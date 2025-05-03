<?php

namespace App\Services;

use App\Models\Order;
use App\Helpers\WhatsAppHelper;
use App\Models\CallHistory;
use App\Models\User;
use App\Jobs\SendWhatsAppMessageJob;
use App\Models\Client;



class CallFailureService
{
    public function processRecentFailedCalls()
    {
        $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))->get();

        foreach ($calls as $call) {
            if (!$this->isFailedCall($call->cause_code)) {
                continue;
            }

            $this->handleFailedCall($call);
        }
    }

    protected function handleFailedCall(CallHistory $call)
    {

        $client = Client::where('phone', $call->phone_number)->first();

        $orders = $client
            ? Order::with('client')
            ->where('client_id', $client->id)
            ->latest()
            ->take(2)
            ->get()
            : collect(); // if no client found, return empty collection

        $orderDetails = $orders->isEmpty()
            ? "Hi, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
            : "Hi! We attempted to reach you regarding the following orders:\n\n" .
            $orders->map(fn($o) => "Order #{$o->id} - Status: {$o->status}")->implode("\n") .
            "\n\nPlease reply to confirm your delivery address or if you need help.";


        // Determine which user sent the call
        $userId = null;

        // 1. Prefer user_id from the call record
        if ($call->user_id) {
            $userId = $call->user_id;
        }

        // 2. Fallback to matching caller_number with user phone number
        if (!$userId && $call->caller_number) {
            $user = User::where('phone_number', $call->caller_number)->first();
            if ($user) {
                $userId = $user->id;
            }
        }

        // 3. Final fallback to default agent
        if (!$userId) {
            $user = User::where('name', 'Default Agent')->first();
            $userId = $user?->id ?? 1;
        }



        // Get the user who made the call (assuming calls have user_id or some way to trace the agent)
        $userId = $call->user_id ?? User::where('name', 'Default Agent')->first()->id ?? 1;
        // if user_id is null fallback

        // to callerNumber
        $callerNumber = $call->caller_number;


        $userId = User::where('phone_number', $callerNumber)->first()->id ?? 1; // fallback to default agent

        SendWhatsAppMessageJob::dispatch($call->client_phone, $orderDetails, $userId);
    }

    protected function isFailedCall(string $code): bool
    {
        return in_array($code, [
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
    }
}
