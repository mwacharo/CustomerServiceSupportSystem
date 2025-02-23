<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\CallHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;

use Illuminate\Support\Facades\Validator;


namespace App\Http\Controllers;

use AfricasTalking\SDK\AfricasTalking;
use App\Models\CallHistory;
use App\Models\CallQueue;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;
use SimpleXMLElement;

class ApiCallCentreController extends Controller
{


    public function makeCall(Request $request)
    {
        Log::info('Received request to initiate call.', ['request_data' => $request->all()]);

        // Validate request input
        $validator = Validator::make($request->all(), [
            'phone' => 'required|string',
        ]);

        if ($validator->fails()) {
            Log::warning('Validation failed for makeCall request.', ['errors' => $validator->errors()]);
            return response()->json(['error' => $validator->errors()], 400);
        }

        $phone = $request->input('phone');

        $username = trim(config('services.africastalking.username'));
        $apiKey = trim(config('services.africastalking.api_key'));
        $from = trim(config('services.africastalking.phone'));

        // Log::info('AFRICASTALKING Config', [
        //     'username' => config('services.africastalking.username'),
        //     'api_key'  => config('services.africastalking.api_key'),
        //     'phone'    => config('services.africastalking.phone'),
        // ]);


        if (!$username || !$apiKey || !$from) {
            Log::error('Africa’s Talking API credentials or phone number are missing.');
            return response()->json(['error' => 'Internal Server Error.'], 500);
        }

        Log::info('Initializing Africa’s Talking API.', ['from' => $from, 'to' => $phone]);
        $africastalking = new AfricasTalking($username, $apiKey);

        // Set the call parameters
        $callParams = [
            'from' => $from,
            'to'   => $phone,
        ];

        try {
            Log::info('Attempting to make a call.', ['call_params' => $callParams]);

            // Make the call
            $response = $africastalking->voice()->call($callParams);

            Log::info('Call initiated successfully.', ['response' => $response]);

            return response()->json([
                'message' => 'Call initiated successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            Log::error('Failed to initiate the call.', [
                'error_message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'error' => 'Failed to initiate the call',
                'message' => $e->getMessage(),
            ], 500);
        }
    }


    // Get all queued calls
    public function getQueuedCalls()
    {
        try {
            // Fetch all queued calls (you can filter by status, if needed)
            $queuedCalls = CallQueue::where('status', 'queued')->get();

            return response()->json($queuedCalls, 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to fetch queued calls', 'message' => $e->getMessage()], 500);
        }
    }


    // Dequeue a call when an agent is available
    public function dequeueCall(Request $request)
    {
        $callId = $request->input('callId');

        // Fetch the next queued call (FIFO)
        $nextQueuedCall = CallQueue::where('status', 'queued')->oldest()->first();

        if ($nextQueuedCall) {
            // Mark the call as answered
            $nextQueuedCall->status = 'answered';
            $nextQueuedCall->save();

            // Handle dequeuing the call (picking up by an agent)
            $username = config('services.africastalking.username');
            $apiKey = config('services.africastalking.api_key');
            $africastalking = new AfricasTalking($username, $apiKey);
            $voice = $africastalking->voice();

            // Dequeue the call based on the callId
            $response = $voice->dequeue([
                'callId' => $callId,  // Call ID fetched from the queued call
                'phoneNumber' => $nextQueuedCall->phone_number,
            ]);

            return response()->json(['message' => 'Call dequeued successfully', 'data' => $response], 200);
        }

        return response()->json(['message' => 'No calls in the queue'], 200);
    }


    // Enqueue incoming calls when agents are busy
    public function enqueueCall(Request $request)
    {
        $phone = $request->input('phone');
        $virtualNumber = '+254741821113'; // Virtual number for incoming calls

        // Check if there are available agents or all are busy
        $agentsBusy = $this->checkIfAgentsAreBusy();

        if ($agentsBusy) {
            // If agents are busy, enqueue the call
            $queuedCall = CallQueue::create(['phone_number' => $phone]);

            // Add the call to the queue (using Africa's Talking Voice API)
            $username = config('services.africastalking.username');
            $apiKey = config('services.africastalking.api_key');
            $africastalking = new AfricasTalking($username, $apiKey);
            $voice = $africastalking->voice();

            // Enqueue the call with a hold music or message
            $response = $voice->enqueue([
                'from' => $virtualNumber,
                'to' => $phone,
                'url' => 'http://www.mymediaserver.com/audio/callWaiting.wav',  // Optional: Music or message URL
            ]);

            return response()->json(['message' => 'Call added to queue', 'data' => $response], 200);
        }

        return response()->json(['message' => 'Agents are available, call can be answered immediately'], 200);
    }

    // Function to check if all agents are busy
    private function checkIfAgentsAreBusy()
    {
        // For simplicity, let's assume we check the agent's status in the database.
        // This logic can be customized depending on how you track agent activity.
        $agents = Officer::where('status', 'busy')->count();

        return $agents >= 10;  // Assume we have 10 agents
    }


    public function transferCall(Request $request)
    {
        $agentId = $request->input('agentId');
        $callId = $request->input('callId');  // Assuming you pass callId for the active call

        // Find the agent details
        $agent = Officer::find($agentId);

        if ($agent && !$agent->isInCall) {
            // Transfer the call to the agent (this can be customized based on your API integration)
            $username = config('services.africastalking.username');
            $apiKey = config('services.africastalking.api_key');
            $africastalking = new AfricasTalking($username, $apiKey);
            $voice = $africastalking->voice();

            // Transfer the call
            try {
                $response = $voice->transfer([
                    'callId' => $callId,
                    'destination' => $agent->phone_number,  // Agent's phone number
                ]);

                return response()->json(['message' => 'Call transferred successfully', 'data' => $response], 200);
            } catch (\Exception $e) {
                return response()->json(['error' => 'Error transferring call', 'message' => $e->getMessage()], 500);
            }
        }

        return response()->json(['error' => 'Agent not found or is busy'], 400);
    }


  

    public function handleVoiceCallback(Request $request)
    {
        Log::info('📞 Received voice callback', $request->all());
    
        $isActive = filter_var($request->input('isActive'), FILTER_VALIDATE_BOOLEAN);
        $sessionId = $request->input('sessionId');
        $direction = $request->input('direction'); // 'Inbound' or 'Outbound'
        $callerNumber = $request->input('callerNumber');
        $destinationNumber = $request->input('destinationNumber', '');
        $clientDialedNumber = $request->input('clientDialedNumber', '');
    
        if ($isActive) {
            Log::info("✅ Call is active. Direction: $direction, Caller: $callerNumber, Destination: $destinationNumber");
    
            // **Handle Inbound Calls (User calls your WebRTC Client)**
            if ($direction === 'Inbound') {
                Log::info("📞 Inbound call from: $callerNumber");
    
                return $this->xmlResponse([
                    'Response' => [
                        'Say' => 'Hello! This is an automated response. Please wait.',
                        'Dial' => [
                            '_attributes' => [
                                'record' => 'true',
                                // 'phoneNumbers' => env('SUPPORT_AGENT_NUMBER', '+254711082159'),
                                 'phoneNumbers' => $clientDialedNumber,

                                'ringbackTone' => 'https://support.solssa.com/api/v1/get-audio/playMusic.wav'
                            ]
                        ]
                    ]
                ]);
            }
    
            // **Handle Outbound Calls (WebRTC client dials a user)**
            if ($direction === 'Outbound') {
                Log::info("📤 Outbound call initiated to: $clientDialedNumber");
    
                CallHistory::create([
                    'isActive' => 1,
                    'callerNumber' => $callerNumber ?? 'Unknown',
                    'destinationNumber' => $clientDialedNumber ?? 'Unknown',
                    'direction' => 'outbound',
                    'sessionId' => $sessionId ?? 'Unknown'
                ]);
    
                return $this->xmlResponse([
                    'Response' => [
                        'Dial' => [
                            '_attributes' => [
                                'record' => 'true',
                                'phoneNumbers' => $clientDialedNumber, 
                                'ringbackTone' => 'https://support.solssa.com/api/v1/get-audio/playMusic.wav'
                            ]
                        ],
                        'Record' => []
                    ]
                ]);
            }
        }
    
        // **Handle Call End**
        Log::info("⏹️ Call ended. Updating call history for session: $sessionId");
    
        if (CallHistory::where('sessionId', $sessionId)->exists()) {
            CallHistory::where('sessionId', $sessionId)->update([
                'isActive' => 0,
                'recordingUrl' => $request->input('recordingUrl'),
                'durationInSeconds' => $request->input('durationInSeconds'),
                'currencyCode' => $request->input('currencyCode'),
                'amount' => $request->input('amount'),
                'hangupCause' => $request->input('hangupCause'),
            ]);
        } else {
            Log::warning("⚠️ No call history found for session: $sessionId");
        }
    
        Log::info("🔄 Resetting agent status for session: $sessionId");
        if (Officer::where('sessionId', $sessionId)->exists()) {
            Officer::where('sessionId', $sessionId)->update(['status' => 'available', 'sessionId' => null]);
        } else {
            Log::warning("⚠️ No officer session found for: $sessionId");
        }
    
        return response()->json(['message' => 'Call handled successfully'], 200);
    }
    private function xmlResponse(array $data)
{
    $xml = new SimpleXMLElement('<Response/>');
    $this->arrayToXml($data, $xml);
    return response($xml->asXML(), 200)->header('Content-Type', 'application/xml');
}



private function arrayToXml(array $data, SimpleXMLElement &$xml)
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            if (isset($value['_attributes'])) {
                $subnode = $xml->addChild($key);
                foreach ($value['_attributes'] as $attrKey => $attrValue) {
                    $subnode->addAttribute($attrKey, $attrValue);
                }
            } else {
                $subnode = $xml->addChild($key);
                $this->arrayToXml($value, $subnode);
            }
        } else {
            $xml->addChild($key, htmlspecialchars($value));
        }
    }
}



    public function handleEventCallback(Request $request)
    {
        // Log the entire event callback payload for debugging.
        Log::info('Received event callback', $request->all());

        // Extract common fields; adjust these keys based on the actual payload.
        $eventType = $request->input('eventType', 'undefined');
        $sessionId = $request->input('sessionId', null);

        // Process the event based on its type.
        switch ($eventType) {
            case 'session_created':
                Log::info("Session created event received.", ['sessionId' => $sessionId]);
                // TODO: Add logic to handle a newly created session.
                break;

            case 'session_established':
                Log::info("Session established event received.", ['sessionId' => $sessionId]);
                // TODO: Update session status or perform any other logic.
                break;

            case 'session_terminated':
                Log::info("Session terminated event received.", ['sessionId' => $sessionId]);
                // TODO: Clean up session data, update database, etc.
                break;

            case 'ice_candidate':
                Log::info("ICE candidate event received.", ['sessionId' => $sessionId, 'data' => $request->all()]);
                // TODO: Process ICE candidate details if needed.
                break;

            case 'session_error':
                Log::error("Session error event received.", ['sessionId' => $sessionId, 'data' => $request->all()]);
                // TODO: Handle the error accordingly.
                break;

            default:
                Log::warning("Unhandled event type: $eventType", $request->all());
                break;
        }

        // Return a JSON response indicating successful processing of the event callback.
        return response()->json(['status' => 'success']);
    }




    public function uploadMediaFile()
    {
        $AT = new AfricasTalking($this->username, $this->apiKey);
        $voice = $AT->voice();
        $phoneNumber = "+254730731433";
        $fileUrl = "https://support.solssa.com/api/v1/get-audio/playMusic.wav";

        try {
            // Upload the file
            $result = $voice->uploadMediaFile([
                "phoneNumber" => $phoneNumber,
                "url" => $fileUrl
            ]);

            // print_r($result);

            Log::info("Media File Upload Response", $result);
            return response()->json($result);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }


    public function generateToken(Request $request)
    {
        // Fetch Africa's Talking API credentials from config.
        $apiKey    = config('services.africastalking.api_key');
        $username  = config('services.africastalking.username');
        $phoneNumber = config('services.africastalking.phone'); // e.g. +2547XXXXXXX

        if (!$username || !$apiKey) {
            Log::error('Africa’s Talking credentials are missing.', [
                'username' => $username,
                'apiKey'   => $apiKey
            ]);
            return response()->json(['error' => 'Africa’s Talking credentials are missing.'], 500);
        }

        // Retrieve all users (or filter to specific users if needed).
        $users = User::all();
        $updatedTokens = [];
        $failedUpdates = [];

        foreach ($users as $user) {
            // Either use the user’s existing client_name or generate a unique one
            $clientName = $user->client_name ?: 'browser-client-' . uniqid();

            // Decide if the user can receive or make calls
            $incoming = isset($user->can_receive_calls) ? $user->can_receive_calls : true;
            $outgoing = isset($user->can_call) ? $user->can_call : true;

            Log::info('Generating token for user', [
                'user_id'    => $user->id,
                'clientName' => $clientName,
                'phoneNumber' => $phoneNumber
            ]);

            // Prepare the JSON payload.
            // Do NOT include the apiKey in the payload; pass it in headers instead.
            $payload = [
                'username'    => $username,
                'clientName'  => $clientName,
                'phoneNumber' => $phoneNumber,        // Required if you want PSTN calls
                'incoming'    => $incoming ? "true" : "false",
                'outgoing'    => $outgoing ? "true" : "false",
                'lifeTimeSec' => "86400"              // e.g., 24 hours in seconds
            ];

            // Africa's Talking WebRTC token endpoint
            $url = 'https://webrtc.africastalking.com/capability-token/request';

            // cURL to Africa's Talking
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
            curl_setopt($ch, CURLOPT_HTTPHEADER, [
                'apiKey: ' . $apiKey,
                'Accept: application/json',
                'Content-Type: application/json'
            ]);

            // Execute the cURL request.
            $response = curl_exec($ch);
            if (curl_errno($ch)) {
                Log::error("cURL Error for user {$user->id}: " . curl_error($ch));
                curl_close($ch);
                continue; // Move to next user
            }
            curl_close($ch);

            // Decode the response from Africa’s Talking
            $responseData = json_decode($response, true);

            // Example of a successful response:
            // {
            //   "success": true,
            //   "token": "ATCAPtkn_...",
            //   "message": "Capability token successfully created.",
            //   "incoming": true,
            //   "outgoing": true,
            //   "lifeTimeSec": 86400,
            //   "clientName": "browser-client-123abc"
            // }

            if (isset($responseData['token'])) {
                $newToken = $responseData['token'];

                Log::info("Updating token in database for user {$user->id}", [
                    'old_token'  => $user->token,
                    'new_token'  => $newToken
                ]);

                // Update only the token in your database (or store more fields if desired)
                $updateSuccess = $user->update(['token' => $newToken]);

                if ($updateSuccess) {
                    Log::info("Database update successful for user {$user->id}");
                    // Include all relevant response data
                    $updatedTokens[] = [
                        'user_id'     => $user->id,
                        'token'       => $responseData['token'],
                        'clientName'  => $responseData['clientName'] ?? $clientName,
                        'incoming'    => $responseData['incoming'] ?? null,
                        'outgoing'    => $responseData['outgoing'] ?? null,
                        'lifeTimeSec' => $responseData['lifeTimeSec'] ?? null,
                        'message'     => $responseData['message'] ?? null,
                        'success'     => $responseData['success'] ?? false
                    ];
                } else {
                    Log::warning("Database update failed for user {$user->id}");
                    $failedUpdates[] = $user->id;
                }
            } else {
                Log::error('Failed to generate token for user', [
                    'user_id'  => $user->id,
                    'response' => $responseData
                ]);
                $failedUpdates[] = $user->id;
            }
        }

        // Return a summary of updated tokens
        return response()->json([
            'updatedTokens' => $updatedTokens,
            'failedUpdates' => $failedUpdates,
            'totalUpdated'  => count($updatedTokens),
            'totalFailed'   => count($failedUpdates),
        ]);
    }




    public function hangupCall($sessionId)
    {
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => "https://voice.africastalking.com/hangupCall",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => [
                "sessionId" => $sessionId
            ],
            CURLOPT_HTTPHEADER => [
                "apikey: 0fbf70babd408769207a740119b305da1c5505f72c9e91dce8031f7515a94255"
            ],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        $xml = simplexml_load_string($response);
        $json = json_encode($xml);
        $array = json_decode($json, true);

        return $array;
    }




    public function getCallWaitingHistory()
    {

        $call_histories = DB::table('call_histories')
            ->where('isActive', 1)
            ->where('nextCallStep', 'waCallCenterApiControlleriting')
            ->where('deleted_at', null)
            ->orderBy('created_at', 'ASC')
            ->get();

        return json_encode($call_histories);
    }

    public function getAgentCallHistory(Request $request)
    {

        $admin_id = $request->id;
        $call_histories = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('adminId', $admin_id)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get();

        return json_encode($call_histories);
    }

    public function getCallOngoingHistory()
    {

        $call_histories = DB::table('call_histories')
            ->where('isActive', 1)
            ->where('nextCallStep', 'in_progress')
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get();

        $json_results = array();
        foreach ($call_histories as $call_history) {

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if ($admin) {
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push(
                $json_results,
                array(
                    'id' => $call_history->id,
                    'isActive' => $call_history->isActive,
                    'direction' => $call_history->direction,
                    'sessionId' => $call_history->sessionId,
                    'callerNumber' => $call_history->callerNumber,
                    'destinationNumber' => $call_history->destinationNumber,
                    'durationInSeconds' => $call_history->durationInSeconds,
                    'currencyCode' => $call_history->currencyCode,
                    'recordingUrl' => $call_history->recordingUrl,
                    'amount' => $call_history->amount,
                    'hangupCause' => $call_history->hangupCause,
                    'adminId' => $call_history->adminId,
                    'agentId' => $call_history->agentId,
                    'adminName' => $admin_name,
                    'notes' => $call_history->notes,
                    'nextCallStep' => $call_history->nextCallStep,
                    'conference' => $call_history->conference,
                    'created_at' => $call_history->created_at,
                    'updated_at' => $call_history->updated_at,
                )
            );
        }

        return json_encode($json_results);
    }

    public function getAgentListSummary()
    {

        $call_agents = DB::table('call_agents')
            ->where('deleted_at', null)
            ->orderBy('client_name', 'ASC')
            ->get();

        $json_results = array();
        foreach ($call_agents as $call_agent) {

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_agent->admin_id)
                ->where('deleted_at', null)
                ->first();

            if ($admin) {
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            $summary_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('deleted_at', null)
                ->whereDate('created_at', Carbon::today())
                ->count();

            $summary_inbound_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('direction', 'inbound')
                ->where('deleted_at', null)
                ->whereDate('created_at', Carbon::today())
                ->count();

            $summary_outbound_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('direction', 'outbound')
                ->where('deleted_at', null)
                ->whereDate('created_at', Carbon::today())
                ->count();

            $summary_call_duration = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('deleted_at', null)
                ->whereDate('created_at', Carbon::today())
                ->sum('durationInSeconds');

            $summary_call_missed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
                ->whereDate('created_at', Carbon::today())
                ->where('deleted_at', null)
                ->count();

            $summary_pending_order = DB::table('orders')
                ->where('order_status', 'order_pending')
                ->where('agent', $call_agent->client_name)
                ->whereDate('created_at', Carbon::today())
                ->where('deleted_at', null)
                ->count();

            $summary_scheduled_order = DB::table('orders')
                ->where('order_status', 'scheduled')
                ->where('agent', $call_agent->client_name)
                ->whereDate('created_at', Carbon::today())
                ->where('deleted_at', null)
                ->count();

            $summary_cancelled_order = DB::table('orders')
                ->where('order_status', 'cancelled')
                ->where('agent', $call_agent->client_name)
                ->whereDate('created_at', Carbon::today())
                ->where('deleted_at', null)
                ->count();

            $summary_delivery_order = DB::table('orders')
                ->where('order_status', 'delivered')
                ->where('agent', $call_agent->client_name)
                ->whereDate('created_at', Carbon::today())
                ->where('deleted_at', null)
                ->count();

            $summary_delivery_rate = "-";
            if ($summary_delivery_order + $summary_scheduled_order > 0) {
                $summary_delivery_rate = $summary_delivery_order / ($summary_delivery_order + $summary_scheduled_order);
                $summary_delivery_rate = $summary_delivery_rate * 100;
            }

            array_push(
                $json_results,
                array(
                    'id' => $call_agent->id,
                    'phone_number' => $call_agent->phone_number,
                    'client_name' => $call_agent->client_name,
                    'admin_id' => $call_agent->admin_id,
                    'admin_name' => $admin_name,
                    'status' => $call_agent->status,
                    'sessionId' => $call_agent->sessionId,
                    'token' => $call_agent->token,
                    'summary_call_completed' => $summary_call_completed,
                    'summary_inbound_call_completed' => $summary_inbound_call_completed,
                    'summary_outbound_call_completed' => $summary_outbound_call_completed,
                    'summary_call_duration' => $summary_call_duration,
                    'summary_call_missed' => $summary_call_missed,
                    'summary_pending_order' => $summary_pending_order,
                    'summary_scheduled_order' => $summary_scheduled_order,
                    'summary_cancelled_order' => $summary_cancelled_order,
                    'summary_delivery_rate' => $summary_delivery_rate,
                    'summary_delivery_order' => $summary_delivery_order,
                    'updated_at' => $call_agent->updated_at,
                )
            );
        }

        return json_encode($json_results);
    }

    public function getAgentListSummaryFilter(Request $request)
    {
        $call_date = $request->call_date;
        $custom_date = $request->custom_date;
        $custom_start_date = $request->custom_start_date;
        $custom_end_date = $request->custom_end_date;

        $call_agents = DB::table('call_agents')
            ->where('deleted_at', null)
            ->orderBy('client_name', 'ASC')
            ->get();

        $json_results = array();
        foreach ($call_agents as $call_agent) {

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_agent->admin_id)
                ->where('deleted_at', null)
                ->first();

            if ($admin) {
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            $summary_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('deleted_at', null);

            $summary_inbound_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('direction', 'inbound')
                ->where('deleted_at', null);

            $summary_outbound_call_completed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('direction', 'outbound')
                ->where('deleted_at', null);

            $summary_call_duration = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->where('deleted_at', null);

            $summary_call_missed = DB::table('call_histories')
                ->where('agentId', $call_agent->client_name)
                ->where('isActive', 0)
                ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
                ->where('deleted_at', null);

            $summary_pending_order = DB::table('orders')
                ->where('order_status', 'order_pending')
                ->where('agent', $call_agent->client_name)
                ->where('deleted_at', null);

            $summary_scheduled_order = DB::table('orders')
                ->where('order_status', 'scheduled')
                ->where('agent', $call_agent->client_name)
                ->where('deleted_at', null);

            $summary_cancelled_order = DB::table('orders')
                ->where('order_status', 'cancelled')
                ->where('agent', $call_agent->client_name)
                ->where('deleted_at', null);

            $summary_delivery_order = DB::table('orders')
                ->where('order_status', 'delivered')
                ->where('agent', $call_agent->client_name)
                ->where('deleted_at', null);

            if ($call_date != 'all') {

                if ($call_date == 'today') {

                    $summary_call_completed->whereDate('created_at', Carbon::today());
                    $summary_inbound_call_completed->whereDate('created_at', Carbon::today());
                    $summary_outbound_call_completed->whereDate('created_at', Carbon::today());
                    $summary_call_duration->whereDate('created_at', Carbon::today());
                    $summary_call_missed->whereDate('created_at', Carbon::today());
                    $summary_pending_order->whereDate('created_at', Carbon::today());
                    $summary_scheduled_order->whereDate('created_at', Carbon::today());
                    $summary_cancelled_order->whereDate('created_at', Carbon::today());
                    $summary_delivery_order->whereDate('created_at', Carbon::today());
                } elseif ($call_date == 'current_week') {

                    $summary_call_completed->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_inbound_call_completed->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_outbound_call_completed->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_call_duration->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_call_missed->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_pending_order->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_scheduled_order->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_cancelled_order->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                    $summary_delivery_order->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
                } elseif ($call_date == 'last_week') {

                    $previous_week = strtotime("-1 week +1 day");
                    $start_week = strtotime("last sunday midnight", $previous_week);
                    $end_week = strtotime("next saturday", $start_week);
                    $start_week = date("Y-m-d", $start_week);
                    $end_week = date("Y-m-d", $end_week);

                    $summary_call_completed->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_inbound_call_completed->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_outbound_call_completed->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_call_duration->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_call_missed->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_pending_order->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_scheduled_order->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_cancelled_order->whereBetween('created_at', [$start_week, $end_week]);
                    $summary_delivery_order->whereBetween('created_at', [$start_week, $end_week]);
                } elseif ($call_date == 'current_month') {

                    $summary_call_completed->whereMonth('created_at', Carbon::now()->month);
                    $summary_inbound_call_completed->whereMonth('created_at', Carbon::now()->month);
                    $summary_outbound_call_completed->whereMonth('created_at', Carbon::now()->month);
                    $summary_call_duration->whereMonth('created_at', Carbon::now()->month);
                    $summary_call_missed->whereMonth('created_at', Carbon::now()->month);
                    $summary_pending_order->whereMonth('created_at', Carbon::now()->month);
                    $summary_scheduled_order->whereMonth('created_at', Carbon::now()->month);
                    $summary_cancelled_order->whereMonth('created_at', Carbon::now()->month);
                    $summary_delivery_order->whereMonth('created_at', Carbon::now()->month);
                } elseif ($call_date == 'current_year') {

                    $summary_call_completed->whereYear('created_at', Carbon::now()->year);
                    $summary_inbound_call_completed->whereYear('created_at', Carbon::now()->year);
                    $summary_outbound_call_completed->whereYear('created_at', Carbon::now()->year);
                    $summary_call_duration->whereYear('created_at', Carbon::now()->year);
                    $summary_call_missed->whereYear('created_at', Carbon::now()->year);
                    $summary_pending_order->whereYear('created_at', Carbon::now()->year);
                    $summary_scheduled_order->whereYear('created_at', Carbon::now()->year);
                    $summary_cancelled_order->whereYear('created_at', Carbon::now()->year);
                    $summary_delivery_order->whereYear('created_at', Carbon::now()->year);
                } elseif ($call_date == 'custom_date') {

                    $custom_date = date("Y-m-d", strtotime($custom_date));
                    $summary_call_completed->whereDate('created_at', '=', $custom_date);
                    $summary_inbound_call_completed->whereDate('created_at', '=', $custom_date);
                    $summary_outbound_call_completed->whereDate('created_at', '=', $custom_date);
                    $summary_call_duration->whereDate('created_at', '=', $custom_date);
                    $summary_call_missed->whereDate('created_at', '=', $custom_date);
                    $summary_pending_order->whereDate('created_at', '=', $custom_date);
                    $summary_scheduled_order->whereDate('created_at', '=', $custom_date);
                    $summary_cancelled_order->whereDate('created_at', '=', $custom_date);
                    $summary_delivery_order->whereDate('created_at', '=', $custom_date);
                } elseif ($call_date == 'custom_range') {

                    $start_date = date("Y-m-d", strtotime($custom_start_date));
                    $end_date = date("Y-m-d", strtotime($custom_end_date));

                    $summary_call_completed->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_inbound_call_completed->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_outbound_call_completed->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_call_duration->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_call_missed->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_pending_order->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_scheduled_order->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_cancelled_order->whereBetween('created_at', [$start_date, $end_date]);
                    $summary_delivery_order->whereBetween('created_at', [$start_date, $end_date]);
                }
            }


            $summary_call_completed = $summary_call_completed->count();
            $summary_inbound_call_completed = $summary_inbound_call_completed->count();
            $summary_outbound_call_completed = $summary_outbound_call_completed->count();
            $summary_call_duration = $summary_call_duration->sum('durationInSeconds');
            $summary_call_missed = $summary_call_missed->count();
            $summary_pending_order = $summary_pending_order->count();
            $summary_scheduled_order = $summary_scheduled_order->count();
            $summary_cancelled_order = $summary_cancelled_order->count();
            $summary_delivery_order = $summary_delivery_order->count();


            $summary_delivery_rate = "-";
            if ($summary_delivery_order + $summary_scheduled_order > 0) {
                $summary_delivery_rate = $summary_delivery_order / ($summary_delivery_order + $summary_scheduled_order);
                $summary_delivery_rate = $summary_delivery_rate * 100;
            }

            array_push(
                $json_results,
                array(
                    'id' => $call_agent->id,
                    'phone_number' => $call_agent->phone_number,
                    'client_name' => $call_agent->client_name,
                    'admin_id' => $call_agent->admin_id,
                    'admin_name' => $admin_name,
                    'status' => $call_agent->status,
                    'sessionId' => $call_agent->sessionId,
                    'token' => $call_agent->token,
                    'summary_call_completed' => $summary_call_completed,
                    'summary_inbound_call_completed' => $summary_inbound_call_completed,
                    'summary_outbound_call_completed' => $summary_outbound_call_completed,
                    'summary_call_duration' => $summary_call_duration,
                    'summary_call_missed' => $summary_call_missed,
                    'summary_pending_order' => $summary_pending_order,
                    'summary_scheduled_order' => $summary_scheduled_order,
                    'summary_cancelled_order' => $summary_cancelled_order,
                    'summary_delivery_rate' => $summary_delivery_rate,
                    'summary_delivery_order' => $summary_delivery_order,
                    'updated_at' => $call_agent->updated_at,
                )
            );
        }

        return json_encode($json_results);
    }

    public function getCallHistory()
    {
        $call_histories = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->latest()
            ->get();

        $json_results = array();
        foreach ($call_histories as $call_history) {

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if ($admin) {
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push(
                $json_results,
                array(
                    'id' => $call_history->id,
                    'isActive' => $call_history->isActive,
                    'direction' => $call_history->direction,
                    'sessionId' => $call_history->sessionId,
                    'callerNumber' => $call_history->callerNumber,
                    'destinationNumber' => $call_history->destinationNumber,
                    'durationInSeconds' => $call_history->durationInSeconds,
                    'currencyCode' => $call_history->currencyCode,
                    'recordingUrl' => $call_history->recordingUrl,
                    'amount' => $call_history->amount,
                    'hangupCause' => $call_history->hangupCause,
                    'adminId' => $call_history->adminId,
                    'agentId' => $call_history->agentId,
                    'adminName' => $admin_name,
                    'notes' => $call_history->notes,
                    'nextCallStep' => $call_history->nextCallStep,
                    'conference' => $call_history->conference,
                    'created_at' => $call_history->created_at,
                    'updated_at' => $call_history->updated_at,
                )
            );
        }

        return json_encode($json_results);
    }

    public function getCallHistoryFilter(Request $request)
    {

        $call_date = $request->call_date;
        $custom_date = $request->custom_date;
        $custom_start_date = $request->custom_start_date;
        $custom_end_date = $request->custom_end_date;
        $agentId = $request->agent_id;
        $destination_number = $request->destination_number;
        $caller_number = $request->caller_number;

        $call_histories = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('deleted_at', null);

        if ($call_date != 'all') {

            if ($call_date == 'today') {

                $call_histories->whereDate('created_at', Carbon::today());
            } elseif ($call_date == 'current_week') {

                $call_histories->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
            } elseif ($call_date == 'last_week') {

                $previous_week = strtotime("-1 week +1 day");
                $start_week = strtotime("last sunday midnight", $previous_week);
                $end_week = strtotime("next saturday", $start_week);
                $start_week = date("Y-m-d", $start_week);
                $end_week = date("Y-m-d", $end_week);
                $call_histories->whereBetween('created_at', [$start_week, $end_week]);
            } elseif ($call_date == 'current_month') {

                $call_histories->whereMonth('created_at', Carbon::now()->month);
            } elseif ($call_date == 'current_year') {

                $call_histories->whereYear('created_at', Carbon::now()->year);
            } elseif ($call_date == 'custom_date') {

                $custom_date = date("Y-m-d", strtotime($custom_date));
                $call_histories->whereDate('created_at', '=', $custom_date);
            } elseif ($call_date == 'custom_range') {

                $start_date = date("Y-m-d", strtotime($custom_start_date));
                $end_date = date("Y-m-d", strtotime($custom_end_date));

                $call_histories->whereBetween('created_at', [$start_date, $end_date]);
            }
        }

        if ($agentId != 'all') {
            $call_histories->where('agentId', $agentId);
        }

        if ($destination_number != '') {
            $call_histories->where('destinationNumber', 'LIKE', "%{$destination_number}%");
        }

        if ($caller_number != '') {
            $call_histories->where('callerNumber', 'LIKE', "%{$caller_number}%");
        }


        $call_histories = $call_histories->get();
        $json_results = array();

        foreach ($call_histories as $call_history) {

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if ($admin) {
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push(
                $json_results,
                array(
                    'id' => $call_history->id,
                    'isActive' => $call_history->isActive,
                    'direction' => $call_history->direction,
                    'sessionId' => $call_history->sessionId,
                    'callerNumber' => $call_history->callerNumber,
                    'destinationNumber' => $call_history->destinationNumber,
                    'durationInSeconds' => $call_history->durationInSeconds,
                    'currencyCode' => $call_history->currencyCode,
                    'recordingUrl' => $call_history->recordingUrl,
                    'amount' => $call_history->amount,
                    'hangupCause' => $call_history->hangupCause,
                    'adminId' => $call_history->adminId,
                    'agentId' => $call_history->agentId,
                    'adminName' => $admin_name,
                    'notes' => $call_history->notes,
                    'nextCallStep' => $call_history->nextCallStep,
                    'conference' => $call_history->conference,
                    'created_at' => $call_history->created_at,
                    'updated_at' => $call_history->updated_at,
                )
            );
        }

        return json_encode($json_results);
    }

    public function getSummaryReport()
    {

        $summary_total_agents = DB::table('call_agents')
            ->where('deleted_at', null)
            ->count();

        $summary_available_agents = DB::table('call_agents')
            ->where('status', 'available')
            ->where('deleted_at', null)
            ->count();

        $summary_busy_agents = DB::table('call_agents')
            ->where('status', 'busy')
            ->where('deleted_at', null)
            ->count();

        $summary_call_duration = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->sum('durationInSeconds');

        $summary_call_completed = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $summary_inbound_call_completed = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('direction', 'inbound')
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $summary_outbound_call_completed = DB::table('call_histories')
            ->where('isActive', 0)
            ->where('direction', 'outbound')
            ->where('deleted_at', null)
            ->whereDate('created_at', Carbon::today())
            ->count();

        $summary_call_waiting = DB::table('call_histories')
            ->where('isActive', 1)
            ->where('nextCallStep', 'enqueue')
            ->where('conference', '!=', null)
            ->where('deleted_at', null)
            ->orderBy('created_at', 'ASC')
            ->count();

        $summary_call_missed = DB::table('call_histories')
            ->where('isActive', 0)
            ->whereIn('hangupCause', ['NO_ANSWER', 'SERVICE_UNAVAILABLE'])
            ->whereDate('created_at', Carbon::today())
            ->where('deleted_at', null)
            ->count();


        $json_array = array(
            'summary_total_agents' => $summary_total_agents,
            'summary_available_agents' => $summary_available_agents,
            'summary_busy_agents' => $summary_busy_agents,
            'summary_inbound_call_completed' => $summary_inbound_call_completed,
            'summary_outbound_call_completed' => $summary_outbound_call_completed,
            'summary_call_completed' => $summary_call_completed,
            'summary_call_duration' => $summary_call_duration,
            'summary_call_missed' => $summary_call_missed,
            'summary_call_waiting' => $summary_call_waiting,
        );

        $response = $json_array;
        return json_encode($response);
    }

    public function callOrderHistory(Request $request)
    {

        $phone_number = $request->phone_number;
        if (substr($phone_number, 0, 1) == "+") {
            $phone_number = substr_replace($phone_number, "", 0, 1);
        }

        $orders = DB::table('orders')
            ->where('receiver_phone', $phone_number)
            ->where('deleted_at', null)
            ->latest()
            ->get();

        return json_encode($orders);
    }
}
