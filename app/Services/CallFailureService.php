<?php

namespace App\Services;

use App\Models\Order;
use App\Helpers\WhatsAppHelper;
use App\Models\CallHistory;
use App\Models\User;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Client;
use Illuminate\Support\Facades\Log;

class CallFailureService
{
    public function processRecentFailedCalls()
    {
        Log::info('Processing recent failed calls.');

        $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))->get();

        foreach ($calls as $call) {
            Log::info('Processing call', ['call_id' => $call->id, 'cause_code' => $call->cause_code]);

            if (!$this->isFailedCall($call->lastBridgeHangupCause)) {
                Log::info('Call is not a failed call.', ['call_id' => $call->id]);
                continue;
            }

            $this->handleFailedCall($call);
        }
    }

    protected function handleFailedCall(CallHistory $call)
    {
        Log::info('Handling failed call.', ['call_id' => $call->id]);

        $client = Client::where('phone', $call->phone_number)->first();
        Log::info('Client lookup result.', ['phone' => $call->phone_number, 'client_id' => $client?->id]);

        $orders = $client
            ? Order::with('client')
                ->where('client_id', $client->id)
                ->latest()
                ->take(2)
                ->get()
            : collect();

        Log::info('Orders fetched for client.', ['client_id' => $client?->id, 'orders' => $orders->pluck('id')]);

        $orderDetails = $orders->isEmpty()
            ? "Hi, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
            : "Hi! We attempted to reach you regarding the following orders:\n\n" .
            $orders->map(fn($o) => "Order #{$o->id} - Status: {$o->status}")->implode("\n") .
            "\n\nPlease reply to confirm your delivery address or if you need help.";

        $userId = null;

        if ($call->user_id) {
            $userId = $call->user_id;
            Log::info('User ID found from call record.', ['user_id' => $userId]);
        }

        if (!$userId && $call->caller_number) {
            $user = User::where('phone_number', $call->caller_number)->first();
            if ($user) {
                $userId = $user->id;
                Log::info('User ID found from caller number.', ['user_id' => $userId]);
            }
        }

        if (!$userId) {
            $user = User::where('name', 'Default Agent')->first();
            $userId = $user?->id ?? 1;
            Log::info('Fallback to default agent.', ['user_id' => $userId]);
        }

        Log::info('Dispatching WhatsApp message.', [
            'client_phone' => $call->client_phone,
            'order_details' => $orderDetails,
            'user_id' => $userId,
        ]);

        SendWhatsAppMessageJob::dispatch($call->client_phone, $orderDetails, $userId);
    }

    protected function isFailedCall( $code)
    {
        $isFailed = in_array($code, [
            'NO_ANSWER',
            'USER_BUSY',
            'CALL_REJECTED',
            'SUBSCRIBER_ABSENT',
            'NORMAL_TEMPORARY_FAILURE',
            'UNSPECIFIED',
            'RECOVERY_ON_TIMER_EXPIRE',
            'NO_USER_RESPONSE',
            'UNALLOCATED_NUMBER',
        ]);

        Log::info('Checking if call is failed.', ['cause_code' => $code, 'is_failed' => $isFailed]);

        return $isFailed;
    }
}
