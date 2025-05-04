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



    //  public function processRecentFailedCalls()
    // {
    //     Log::info('Processing recent failed calls.');

    //     $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))
    //         ->where(function ($q) {
    //             $q->whereNull('lastBridgeHangupCause')
    //               ->orWhereIn('lastBridgeHangupCause', [
    //                   'NO_ANSWER', 'USER_BUSY', 'CALL_REJECTED',
    //                   'SUBSCRIBER_ABSENT', 'NORMAL_TEMPORARY_FAILURE',
    //                   'UNSPECIFIED', 'RECOVERY_ON_TIMER_EXPIRE',
    //                   'NO_USER_RESPONSE', 'UNALLOCATED_NUMBER',
    //                   'Aborted'
    //               ]);
    //         })
    //         ->get();

    //     foreach ($calls as $call) {
    //         Log::info('Processing call', ['call_id' => $call->id]);

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
    
    //         // Fallback to a placeholder client
    //         $client = Client::where('name', 'LIKE', '%Unknown%')->first();
    
    //         if (!$client) {
    //             if ($call->clientDialedNumber) {
    //                 $client = Client::create([
    //                     'name' => 'Unknown Client',
    //                     'phone_number' => $call->clientDialedNumber,
    //                 ]);
    
    //                 Log::info('Created new client record for unknown client.', ['client_id' => $client->id]);
    //             } else {
    //                 Log::warning('Cannot create client record: phone number is null.', ['call_id' => $call->id]);
    //                 return;
    //             }
    //         }
    //     }
    
        

    //        $orders = $client
    //         ? Order::with('client','vendor','orderItems')
    //         ->where('client_id', $client->id)
    //         ->latest()
    //         ->take(2)
    //         ->get()
    //         : collect();


    //         // log orders
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
    
    //     // Build the message content
    //     $orderDetails = $orders->isEmpty()
    //     // order->client->name as the relationship exist 
    //     // inlcude order details like order id, status, tracking number, seller name, items
    //     // selleer Name and the their online store 
    //     // include name of the courier company in Thi Case Boxleo Courier & Fulfillment
    //         ? "Hi {$client->name}, we tried reaching you regarding your order, but couldn't get through. Please reply to confirm your delivery details."
    //         : "Hi {$client->name}, we attempted to reach you regarding your recent order(s):\n\n" .
    //           $orders->map(function ($o) {
    //               $items = $o->orderItems->map(fn($item) =>
    //                   "- {$item->product_name} x{$item->quantity} @ KES {$item->price}"
    //               )->implode("\n");
    
    //               $vendorName = $o->vendor ? $o->vendor->name : 'Unknown';
    
    //               return "Order #{$o->id} ({$o->status})\nTracking No: {$o->tracking_no}\nSeller: {$vendorName}\nItems:\n{$items}\n";
    //           })->implode("\n") .
    //           "\nPlease confirm your delivery address or let us know how we can assist.";
    
    //     // Determine the user (agent or system) sending this message
    //     $userId = $this->determineUserId($call);
    
    //     Log::info('Dispatching WhatsApp message.', [
    //         'client_phone' => $call->clientDialedNumber,
    //         'order_details' => $orderDetails,
    //         'user_id' => $userId,
    //     ]);
    
    //     // Send the WhatsApp message via queue
    //     SendWhatsAppMessageJob::dispatch($call->clientDialedNumber, $orderDetails, $userId);
    // }
    


    // protected function determineUserId(CallHistory $call)
    // {
    //     if ($call->user_id) {
    //         Log::info('User ID found from call record.', ['user_id' => $call->user_id]);
    //         return $call->user_id;
    //     }

    //     if ($call->CallerNumber) {
    //         $normalizedCaller = $this->normalizePhoneNumber($call->CallerNumber);
    //         $user = User::where('phone_number', $normalizedCaller)->first();

    //         if ($user) {
    //             Log::info('User ID found from caller number.', ['user_id' => $user->id]);
    //             return $user->id;
    //         }
    //     }

    //     $user = User::where('name', 'Default Agent')->first();
    //     $fallbackId = $user?->id ?? 1;

    //     Log::info('Fallback to default agent.', ['user_id' => $fallbackId]);

    //     return $fallbackId;
    // }

    // protected function findClientByPhoneNumber($number)
    // {
    //     $normalized = $this->normalizePhoneNumber($number);

    //     return Client::where('phone_number', $normalized)
    //         ->orWhere('alt_phone_number', $normalized)
    //         ->first();
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
    //         'Aborted'
    //     ]);

    //     Log::info('Checking if call is failed.', ['lastBridgeHangupCause' => $code, 'is_failed' => $isFailed]);

    //     return $isFailed;
    // }

    // private function normalizePhoneNumber($number)
    // {
    //     $number = preg_replace('/\D/', '', $number);

    //     if (str_starts_with($number, '0')) {
    //         return '254' . substr($number, 1);
    //     } elseif (str_starts_with($number, '254')) {
    //         return $number;
    //     } elseif (str_starts_with($number, '+254')) {
    //         return substr($number, 1);
    //     } else {
    //         return '254' . $number;
    //     }
    // }




    /**
 * Process recent failed calls and send WhatsApp notifications to clients.
 * 
 * @return void
 */
public function processRecentFailedCalls()
{
    Log::info('Processing recent failed calls.');

    // Define failed call statuses once
    $failedCallStatuses = [
        'NO_ANSWER', 'USER_BUSY', 'CALL_REJECTED',
        'SUBSCRIBER_ABSENT', 'NORMAL_TEMPORARY_FAILURE',
        'UNSPECIFIED', 'RECOVERY_ON_TIMER_EXPIRE',
        'NO_USER_RESPONSE', 'UNALLOCATED_NUMBER',
        'Aborted'
    ];

    // Get recent failed calls within the last 10 minutes
    $calls = CallHistory::where('created_at', '>=', now()->subMinutes(10))
        ->where(function ($q) use ($failedCallStatuses) {
            $q->whereNull('lastBridgeHangupCause')
              ->orWhereIn('lastBridgeHangupCause', $failedCallStatuses);
        })
        ->get();

    Log::info('Found failed calls.', ['count' => $calls->count()]);

    foreach ($calls as $call) {
        Log::info('Processing call', ['call_id' => $call->id]);
        $this->handleFailedCall($call);
    }
}

/**
 * Handle an individual failed call by sending a WhatsApp message.
 * 
 * @param CallHistory $call The failed call record
 * @return void
 */
protected function handleFailedCall(CallHistory $call)
{
    Log::info('Handling failed call.', [
        'call_id' => $call->id,
        'caller_number' => $call->clientDialedNumber,
    ]);

    // Validate phone number
    if (empty($call->clientDialedNumber)) {
        Log::warning('No caller number found.', ['call_id' => $call->id]);
        return;
    }

    // Find or create client
    $client = $this->getOrCreateClient($call->clientDialedNumber);
    
    if (!$client) {
        Log::error('Failed to find or create client.', ['call_id' => $call->id]);
        return;
    }

    // Get recent orders for this client
    $orders = $this->getRecentClientOrders($client);

    // Build the message based on order information
    $message = $this->buildClientMessage($client, $orders);
    
    // Determine the user (agent) sending this message
    $userId = $this->determineUserId($call);

    Log::info('Dispatching WhatsApp message.', [
        'client_id' => $client->id,
        'client_phone' => $call->clientDialedNumber,
        'user_id' => $userId,
    ]);

    // Send the WhatsApp message via queue
    SendWhatsAppMessageJob::dispatch($call->clientDialedNumber, $message, $userId);
}

/**
 * Find existing client or create a new one if needed.
 * 
 * @param string $phoneNumber Client's phone number
 * @return Client|null
 */
protected function getOrCreateClient($phoneNumber)
{
    // Find client by phone number
    $client = $this->findClientByPhoneNumber($phoneNumber);
    
    if ($client) {
        return $client;
    }
    
    Log::info('Client not found by phone number.', ['phone' => $phoneNumber]);
    
    // Try to find a placeholder client
    $client = Client::where('name', 'LIKE', '%Unknown%')->first();
    
    // Create a new unknown client if needed
    if (!$client) {
        try {
            $client = Client::create([
                'name' => 'Unknown Client',
                'phone_number' => $phoneNumber,
            ]);
            Log::info('Created new client record for unknown client.', ['client_id' => $client->id]);
        } catch (\Exception $e) {
            Log::error('Failed to create client record', [
                'phone' => $phoneNumber,
                'error' => $e->getMessage()
            ]);
        }
    }
    
    return $client;
}

/**
 * Get recent orders for a client.
 * 
 * @param Client $client
 * @return \Illuminate\Database\Eloquent\Collection
 */
protected function getRecentClientOrders(Client $client)
{
    $orders = Order::with(['client', 'vendor', 'orderItems'])
        ->where('client_id', $client->id)
        ->latest()
        ->take(2)
        ->get();
    
    // Log order details
    Log::info('Orders fetched for client.', [
        'client_id' => $client->id,
        'client_name' => $client->name,
        'orders_count' => $orders->count(),
        'orders' => $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'status' => $order->status,
                 'created_at' => $order->created_at,
                'total_amount' => $order->total_price,
                'tracking_number' => $order->tracking_no,
                'seller' => $order->vendor?->name ?? 'Unknown Seller',
                'seller_store' => $order->vendor?->website_url ?? 'Unknown Online Store',
                'items_count' => $order->orderItems->count(),
            ];
        }),
    ]);
    
    return $orders;
}

/**
 * Build a personalized WhatsApp message for the client.
 * 
 * @param Client $client
 * @param \Illuminate\Database\Eloquent\Collection $orders
 * @return string
 */
// protected function buildClientMessage(Client $client, $orders)
// {
//     if ($orders->isEmpty()) {
//         return "Hi {$client->name}, we tried reaching you regarding your order with Boxleo Courier & Fulfillment, but couldn't get through. Please reply to confirm your delivery details.";
//     }

//     $message = "Hi {$client->name}, we are Boxleo Courier & Fulfillment, delivering on behalf of our partner(s):\n\n";

//     foreach ($orders as $order) {
//         $vendorName = $order->vendor ? $order->vendor->name : 'Unknown';
//         $vendorStore = $order->vendor && $order->vendor->website_url ? "({$order->vendor->website_url})" : '';
//         $items = $order->orderItems->map(function ($item) {
//             return "- {$item->product_name} x{$item->quantity} @ KES {$item->price}";
//         })->implode("\n");

//         $message .= "Order #{$order->id} ({$order->status})\n";
//         $message .= "Tracking No: {$order->tracking_no}\n";
//         $message .= "Seller: {$vendorName} {$vendorStore}\n";
//         $message .= "Total Price: KES {$order->total_price}\n";
//         $message .= "Items:\n{$items}\n\n";
//     }

//     $message .= "Please confirm your delivery address or let us know how we can assist.";
//     return $message;
    

// }



protected function buildClientMessage(Client $client, $orders)
{
    if ($orders->isEmpty()) {
        return "Dear {$client->name},\n\nWe are Boxleo Courier & Fulfillment. We tried reaching you regarding your order but were unable to connect. Please reply with your delivery details or contact us at +254 741 821 113.\n\nBest regards,\nBoxleo Courier & Fulfillment";
    }

    $message = "Dear {$client->name},\n\nBoxleo Courier & Fulfillment is delivering the following orders on behalf of our partner(s):\n\n";

    foreach ($orders as $order) {
        $vendorName = $order->vendor ? $order->vendor->name : 'Unknown';
        $vendorStore = $order->vendor && $order->vendor->website_url ? "({$order->vendor->website_url})" : '';
        $items = $order->orderItems->map(function ($item) {
            return "- {$item->quantity} units @ KES {$item->price}";
        })->implode("\n");

        $message .= "Order #{$order->id} ({$order->status})\n";
        $message .= "Tracking: {$order->tracking_no}\n";
        $message .= "Seller: {$vendorName} {$vendorStore}\n";
        $message .= "Total: KES {$order->total_price}\n";
        $message .= "Items:\n{$items}\n\n";
    }

    $message .= "Please confirm your delivery address or share any instructions. Contact us at +254 741 821 113.\n\nThank you,\nBoxleo Courier & Fulfillment";

    return $message;
}

/**
 * Determine which user ID should be used for the WhatsApp message.
 * 
 * @param CallHistory $call
 * @return int
 */
protected function determineUserId(CallHistory $call)
{
    // Check if the call already has a user ID
    if ($call->user_id) {
        Log::info('User ID found from call record.', ['user_id' => $call->user_id]);
        return $call->user_id;
    }

    // Try to find a user by caller number
    if (!empty($call->CallerNumber)) {
        $normalizedCaller = $this->normalizePhoneNumber($call->CallerNumber);
        $user = User::where('phone_number', $normalizedCaller)->first();

        if ($user) {
            Log::info('User ID found from caller number.', ['user_id' => $user->id]);
            return $user->id;
        }
    }

    // Fall back to default agent
    try {
        $user = User::where('name', 'Default Agent')->first();
        $fallbackId = $user?->id ?? 1;
        
        Log::info('Fallback to default agent.', ['user_id' => $fallbackId]);
        return $fallbackId;
    } catch (\Exception $e) {
        Log::error('Error finding default agent', ['error' => $e->getMessage()]);
        return 1; // Ultimate fallback
    }
}

/**
 * Find a client by phone number.
 * 
 * @param string $number
 * @return Client|null
 */
protected function findClientByPhoneNumber($number)
{
    Log::info('Attempting to find client by phone number.', ['phone_number' => $number]);

    $normalized = $this->normalizePhoneNumber($number);

    Log::info('Normalized phone number.', ['normalized' => $normalized]);

    $client = Client::where('phone_number', $normalized)
        ->orWhere('alt_phone_number', $normalized)
        ->first();

    if ($client) {
        Log::info('Client found.', ['client_id' => $client->id, 'client_name' => $client->name]);
    } else {
        Log::info('Client not found.', ['phone_number' => $normalized]);
    }

    return $client;
}

/**
 * Check if a call hangup cause indicates a failed call.
 * 
 * @param string|null $code
 * @return bool
 */
protected function isFailedCall($code)
{
    $failedCodes = [
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
    ];

    $isFailed = in_array($code, $failedCodes);

    Log::info('Checking if call is failed.', [
        'lastBridgeHangupCause' => $code, 
        'is_failed' => $isFailed
    ]);

    return $isFailed;
}

/**
 * Normalize a phone number to standard format.
 * 
 * @param string $number
 * @return string
 */
private function normalizePhoneNumber($number)
{
    Log::info('Normalizing phone number.', ['original_number' => $number]);

    if (empty($number)) {
        Log::warning('Phone number is empty.');
        return '';
    }
    
    // Remove non-digit characters
    $number = preg_replace('/\D/', '', $number);
    Log::info('Removed non-digit characters.', ['cleaned_number' => $number]);

    // Convert to Kenyan format (254...)
    if (str_starts_with($number, '0')) {
        $normalized = '254' . substr($number, 1);
        Log::info('Converted to Kenyan format (starting with 0).', ['normalized_number' => $normalized]);
        return $normalized;
    } elseif (str_starts_with($number, '254')) {
        Log::info('Number already in Kenyan format.', ['normalized_number' => $number]);
        return $number;
    } elseif (str_starts_with($number, '+254')) {
        $normalized = substr($number, 1);
        Log::info('Converted to Kenyan format (starting with +254).', ['normalized_number' => $normalized]);
        return $normalized;
    } else {
        $normalized = '254' . $number;
        Log::info('Converted to Kenyan format (default case).', ['normalized_number' => $normalized]);
        return $normalized;
    }

    // inlcude isntance to check 254799806098@c.us such a formart
}
}
