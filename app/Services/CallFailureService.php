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
        // localhost:3306/CustomerServiceSupport/call_histories/		http://159.89.41.188/phpmyadmin/index.php?route=/table/sql&db=CustomerServiceSupport&table=call_histories
// Your SQL query has been executed successfully.

// DESCRIBE`call_histories`;



// id	bigint unsigned	NO	PRI	
//     NULL
// 	auto_increment	
// isActive	varchar(255)	YES		
//     NULL
		
// direction	varchar(255)	YES		
//     NULL
		
// sessionId	varchar(255)	YES		
//     NULL
		
// callerNumber	varchar(255)	YES		
//     NULL
		
// callerCarrierName	varchar(255)	YES		
//     NULL
		
// clientDialedNumber	varchar(255)	YES		
//     NULL
		
// callerCountryCode	varchar(255)	YES		
//     NULL
		
// destinationNumber	varchar(255)	YES		
//     NULL
		
// durationInSeconds	varchar(255)	YES		
//     NULL
		
// currencyCode	varchar(255)	YES		
//     NULL
		
// recordingUrl	varchar(255)	YES		
//     NULL
		
// download_status	varchar(255)	NO		pending		
// amount	varchar(255)	YES		
//     NULL
		
// hangupCause	varchar(255)	YES		
//     NULL
		
// user_id	bigint unsigned	YES	MUL	
//     NULL
		
// ivr_option_id	bigint unsigned	YES	MUL	
//     NULL
		
// orderNo	varchar(255)	YES		
//     NULL
		
// notes	longtext	YES		
//     NULL
		
// nextCallStep	varchar(255)	YES		
//     NULL
		
// conference	varchar(255)	YES		
//     NULL
		
// status	varchar(255)	YES		
//     NULL
		
// lastBridgeHangupCause	varchar(255)	YES		
//     NULL
		
// callStartTime	timestamp	YES		
//     NULL
		
// callSessionState	varchar(255)	YES		
//     NULL
		
// created_at	timestamp	YES		
//     NULL
		
// updated_at	timestamp	YES		
//     NULL
		
// deleted_at	timestamp	YES		
//     NULL
		


        foreach ($calls as $call) {
            Log::info('Processing call', ['call_id' => $call->id, 'lastBridgeHangupCause' => $call->lastBridgeHangupCause]);

            // if (!$this->isFailedCall($call->lastBridgeHangupCause)) {
                if (!$call->lastBridgeHangupCause || !$this->isFailedCall($call->lastBridgeHangupCause)) {

                Log::info('Call is not a failed call.', ['call_id' => $call->id]);
                continue;
            }

            $this->handleFailedCall($call);
        }
    }

    protected function handleFailedCall(CallHistory $call)
    {
        Log::info('Handling failed call.', ['call_id' => $call->id]);
        // localhost:3306/CustomerServiceSupport/clients/		http://159.89.41.188/phpmyadmin/index.php?route=/table/sql&db=CustomerServiceSupport&table=clients
// Your SQL query has been executed successfully.

// DESCRIBE `clients`;



// id	bigint unsigned	NO	PRI	
//     NULL
// 	auto_increment	
// phone_number	varchar(15)	NO	UNI	
//     NULL
		
// alt_phone_number	varchar(15)	NO	UNI	
//     NULL
		
// name	varchar(255)	NO		
//     NULL
		
// email	varchar(255)	YES		
//     NULL
		
// address	varchar(255)	YES		
//     NULL
		
// city	varchar(255)	YES		
//     NULL
		
// state	varchar(255)	YES		
//     NULL
		
// zip	varchar(255)	YES		
//     NULL
		
// zip_code	varchar(255)	YES		
//     NULL
		
// country_id	bigint unsigned	YES	MUL	
//     NULL
		
// branch_id	bigint unsigned	YES	MUL	
//     NULL
		
// notes	text	YES		
//     NULL
		
// vendor_id	bigint unsigned	YES	MUL	
//     NULL
		
// status	enum('active','inactive')	NO		active		
// user_id	bigint unsigned	YES	MUL	
//     NULL
		
// created_at	timestamp	YES		
//     NULL
		
// updated_at	timestamp	YES		
//     NULL
		
// deleted_at	timestamp	YES		
    NULL
		


        $client = Client::where('phone_number', $call->phone_number)->first();
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
        ]);

        Log::info('Checking if call is failed.', ['lastBridgeHangupCause' => $code, 'is_failed' => $isFailed]);

        return $isFailed;
    }
}
