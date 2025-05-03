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
    // public function processRecentFailedCalls()
    // {
    //     Log::info('Processing recent failed calls.');

    //     $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))->get();
    //     // inlcude status ="Aborted"

    //     foreach ($calls as $call) {
    //         Log::info('Processing call', ['call_id' => $call->id, 'lastBridgeHangupCause' => $call->lastBridgeHangupCause]);

    //         if (!$call->lastBridgeHangupCause || !$this->isFailedCall($call->lastBridgeHangupCause)) {
    //             Log::info('Call is not a failed call.', ['call_id' => $call->id]);
    //             continue;
    //         }

    //         $this->handleFailedCall($call);
    //     }
    // }

    // protected function handleFailedCall(CallHistory $call)
    // {
    //     Log::info('Handling failed call.', [
    //         'call_id' => $call->id,
    //         'caller_number' => $call->clientDialedNumber,
    //     ]);

    //     if (!$call->clientDialedNumber) {
    //         Log::info('No caller number found.', ['call_id' => $call->id]);
    //         return;
    //     }

    //     $client = $this->findClientByPhoneNumber($call->clientDialedNumber);

    //     if (!$client) {
    //         Log::info('Client not found by phone number.', ['phone' => $call->clientDialedNumber]);

    //         $client = Client::where('name', 'LIKE', '%Unknown%')->first();

    //         if (!$client) {
    //             $client = Client::create([
    //                 'name' => 'Unknown Client',
    //                 'phone_number' => $call->clientDialedNumber,
    //             ]);

    //             Log::info('Created new client record for unknown client.', ['client_id' => $client->id]);
    //         }
    //     }

    //     Log::info('Client lookup result.', ['phone' => $call->clientDialedNumber, 'client_id' => $client?->id]);

    //     $orders = $client
    //         ? Order::with('client')
    //         ->where('client_id', $client->id)
    //         ->latest()
    //         ->take(2)
    //         ->get()
    //         : collect();

    //     Log::info('Orders fetched for client.', [
    //         'client_id' => $client?->id,
    //         'client_name' => $client?->name,
    //         'orders' => $orders->map(function ($order) {
    //             return [
    //                 'id' => $order->id,
    //                 'status' => $order->status,
    //                 'created_at' => $order->created_at,
    //                 'total_amount' => $order->total_price,
    //                 'tracking_number' => $order->tracking_no,
    //                 'seller' => $order->vendor?->name ?? 'Unknown Seller',
    //                 'seller online store' => $order->vendor?->website_url ?? 'Unknown Online Store',
    //                 'items' => $order->orderItems->map(function ($item) {
    //                     return [
    //                         'name' => $item->product_name,
    //                         'quantity' => $item->quantity,
    //                         'price' => $item->price,
    //                     ];
    //                 }),
    //             ];
    //         }),
    //     ]);


      
    //     $orderDetails = $orders->isEmpty()
    //         ? "Hi, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
    //         : "Hi! We attempted to reach you regarding the following orders:\n\n" .
    //         $orders->map(fn($o) => "Order #{$o->id} - Status: {$o->status}")->implode("\n") .
    //         "\n\nPlease reply to confirm your delivery address and delivery date  or if you need help.";

    //     $userId = null;

    //     if ($call->user_id) {
    //         $userId = $call->user_id;
    //         Log::info('User ID found from call record.', ['user_id' => $userId]);
    //     }

    //     if (!$userId && $call->CallerNumber) {
    //         $normalizedCaller = $this->normalizePhoneNumber($call->CallerNumber);
    //         $user = User::where('phone_number', $normalizedCaller)->first();
    //         if ($user) {
    //             $userId = $user->id;
    //             Log::info('User ID found from caller number.', ['user_id' => $userId]);
    //         }
    //     }

    //     if (!$userId) {
    //         $user = User::where('name', 'Default Agent')->first();
    //         $userId = $user?->id ?? 1;
    //         Log::info('Fallback to default agent.', ['user_id' => $userId]);
    //     }

    //     Log::info('Dispatching WhatsApp message.', [
    //         'client_phone' => $call->clientDialedNumber,
    //         'order_details' => $orderDetails,
    //         'user_id' => $userId,
    //         // 'User Name' => $user->name,

    //     ]);

        

    //     SendWhatsAppMessageJob::dispatch($call->clientDialedNumber, $orderDetails, $userId);
    // }

    // protected function isFailedCall($code)
    // {
    //     $isFailed = in_array($code, [
    //         'NO_ANSWER',
    //         'USER_BUSY',
    //         'CALL_REJECTED',
    //         'SUBSCRIBER_ABSENT',
    //         'NORMAL_TEMPORARY_FAILURE',
    //         'UNSPECIFIED',
    //         'RECOVERY_ON_TIMER_EXPIRE',
    //         'NO_USER_RESPONSE',
    //         'UNALLOCATED_NUMBER',
    //     ]);

    //     Log::info('Checking if call is failed.', ['lastBridgeHangupCause' => $code, 'is_failed' => $isFailed]);

    //     return $isFailed;
    // }

    // /**
    //  * Normalize any phone number to standard 254 format.
    //  */
    // private function normalizePhoneNumber($number)
    // {
    //     $number = preg_replace('/\D+/', '', $number);

    //     if (str_starts_with($number, '0')) {
    //         $number = '254' . substr($number, 1);
    //     } elseif (str_starts_with($number, '7') && strlen($number) === 9) {
    //         $number = '254' . $number;
    //     } elseif (str_starts_with($number, '+')) {
    //         $number = ltrim($number, '+');
    //     }

    //     return $number;
    // }

    // /**
    //  * Search for a client by phone number in any common format.
    //  */
    // private function findClientByPhoneNumber($number)
    // {
    //     $normalized = $this->normalizePhoneNumber($number);

    //     $possibleFormats = [
    //         $normalized,                         // e.g., 254741821113
    //         '0' . substr($normalized, 3),        // e.g., 0741821113
    //         '+' . $normalized,                     // e.g., +254741821113
    //     ];
    //     // there is a client with 0741821113
    //     return Client::whereIn('phone_number', $possibleFormats)->first();
    // }



     public function processRecentFailedCalls()
    {
        Log::info('Processing recent failed calls.');

        $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))
            ->where(function ($q) {
                $q->whereNull('lastBridgeHangupCause')
                  ->orWhereIn('lastBridgeHangupCause', [
                      'NO_ANSWER', 'USER_BUSY', 'CALL_REJECTED',
                      'SUBSCRIBER_ABSENT', 'NORMAL_TEMPORARY_FAILURE',
                      'UNSPECIFIED', 'RECOVERY_ON_TIMER_EXPIRE',
                      'NO_USER_RESPONSE', 'UNALLOCATED_NUMBER',
                      'Aborted'
                  ]);
            })
            ->get();

        foreach ($calls as $call) {
            Log::info('Processing call', ['call_id' => $call->id]);

            $this->handleFailedCall($call);
        }
    }

    protected function handleFailedCall(CallHistory $call)
    {
        Log::info('Handling failed call.', [
            'call_id' => $call->id,
            'caller_number' => $call->clientDialedNumber,
        ]);

        if (!$call->clientDialedNumber) {
            Log::info('No caller number found.', ['call_id' => $call->id]);
            return;
        }

        $client = $this->findClientByPhoneNumber($call->clientDialedNumber);

        if (!$client) {
            Log::info('Client not found by phone number.', ['phone' => $call->clientDialedNumber]);

            $client = Client::where('name', 'LIKE', '%Unknown%')->first();

            if (!$client) {
                $client = Client::create([
                    'name' => 'Unknown Client',
                    'phone_number' => $call->clientDialedNumber,
                ]);

                Log::info('Created new client record for unknown client.', ['client_id' => $client->id]);
            }
        }

        $orders = $client
            ? Order::with(['client', 'vendor', 'orderItems'])
                ->where('client_id', $client->id)
                ->latest()
                ->take(2)
                ->get()
            : collect();

        $orderDetails = $orders->isEmpty()
            ? "Hi {$client->name}, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
            : "Hi {$client->name}, we attempted to reach you regarding your recent order(s):\n\n" .
              $orders->map(function ($o) {
                  $items = $o->orderItems->map(fn($item) =>
                      "- {$item->product_name} x{$item->quantity} @ KES {$item->price}"
                  )->implode("\n");



                  $orderDetails = $orders->isEmpty()
    ? "Hi {$client->name}, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
    : "Hi {$client->name}, we attempted to reach you regarding your recent order(s):\n\n" .
      $orders->map(function ($o) {
          $items = $o->orderItems->map(fn($item) =>
              "- {$item->product_name} x{$item->quantity} @ KES {$item->price}"
          )->implode("\n");

          $vendorName = $o->vendor ? $o->vendor->name : 'Unknown';

          return "Order #{$o->id} ({$o->status})\nTracking No: {$o->tracking_no}\nSeller: {$vendorName}\nItems:\n{$items}\n";
      })->implode("\n") .
      "\nPlease confirm your delivery address or let us know how we can assist.";


   
        $userId = $this->determineUserId($call);

        Log::info('Dispatching WhatsApp message.', [
            'client_phone' => $call->clientDialedNumber,
            'order_details' => $orderDetails,
            'user_id' => $userId,
        ]);

        SendWhatsAppMessageJob::dispatch($call->clientDialedNumber, $orderDetails, $userId);
    }

}

    protected function determineUserId(CallHistory $call)
    {
        if ($call->user_id) {
            Log::info('User ID found from call record.', ['user_id' => $call->user_id]);
            return $call->user_id;
        }

        if ($call->CallerNumber) {
            $normalizedCaller = $this->normalizePhoneNumber($call->CallerNumber);
            $user = User::where('phone_number', $normalizedCaller)->first();

            if ($user) {
                Log::info('User ID found from caller number.', ['user_id' => $user->id]);
                return $user->id;
            }
        }

        $user = User::where('name', 'Default Agent')->first();
        $fallbackId = $user?->id ?? 1;

        Log::info('Fallback to default agent.', ['user_id' => $fallbackId]);

        return $fallbackId;
    }

    protected function findClientByPhoneNumber($number)
    {
        $normalized = $this->normalizePhoneNumber($number);

        return Client::where('phone_number', $normalized)
            ->orWhere('alternate_phone', $normalized)
            ->first();
    }

    protected function isFailedCall($code)
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
            'Aborted'
        ]);

        Log::info('Checking if call is failed.', ['lastBridgeHangupCause' => $code, 'is_failed' => $isFailed]);

        return $isFailed;
    }

    private function normalizePhoneNumber($number)
    {
        $number = preg_replace('/\D/', '', $number);

        if (str_starts_with($number, '0')) {
            return '254' . substr($number, 1);
        } elseif (str_starts_with($number, '254')) {
            return $number;
        } elseif (str_starts_with($number, '+254')) {
            return substr($number, 1);
        } else {
            return '254' . $number;
        }
    }
}
