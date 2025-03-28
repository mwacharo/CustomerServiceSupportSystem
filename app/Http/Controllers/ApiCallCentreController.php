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
use App\Events\CallStatusUpdated;
use App\Models\CallHistory;
use App\Models\CallQueue;
use App\Models\IvrOption;
use App\Models\Officer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Http;

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



    // Function to check if all agents are busy
    private function checkIfAgentsAreBusy()
    {
        // For simplicity, let's assume we check the agent's status in the database.
        // This logic can be customized depending on how you track agent activity.
        $agents = User::where('status', 'busy')->count();

        return $agents >= 10;  // Assume we have 10 agents
    }


    public function transferCall(Request $request)
    {
        $agentId = $request->input('agentId');
        $callId = $request->input('callId');  // Assuming you pass callId for the active call

        // Find the agent details
        $agent = User::find($agentId);

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
        try {
            // Log the full request data for debugging
            Log::info('📞 Received voice callback', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            // Validate session ID
            $sessionId = $request->input('sessionId');
            if (!$sessionId) {
                Log::warning("⚠️ Missing sessionId in voice callback request.");
                return response()->json(['error' => 'Session ID is required'], 400);
            }

            // Extract relevant parameters
            $isActive = $request->boolean('isActive', false);
            $callerNumber = $request->input('callerNumber');
            $destinationNumber = $request->input('destinationNumber', '');
            $clientDialedNumber = $request->input('clientDialedNumber', '');
            $callSessionState = $request->input('callSessionState', '');

            Log::info("📞 Caller Number received: $callerNumber");

            // $isOutgoing = str_contains($callerNumber, 'Mwacharo.browser-client') ||
            //     str_contains($callerNumber, 'BoxleoKenya.browser-client');
            // // Log call session state for debugging
            // Log::info("📞 Call session state: $callSessionState for session: $sessionId");


            $isOutgoing = str_contains($callerNumber, ' BoxleoKenya') ||
            str_contains($callerNumber, 'BoxleoKenya');
        // Log call session state for debugging
        Log::info("📞 Call session state: $callSessionState for session: $sessionId");


            // Handle outgoing calls
            if ($isOutgoing) {
                Log::info("📞 Outgoing call check", [
                    'callerNumber' => $callerNumber,
                    'isOutgoing' => $isOutgoing
                ]);

                switch ($callSessionState) {
                    case 'Ringing':
                        // Ensure no whitespace before output
                        ob_clean();
                        header('Content-Type: application/xml; charset=utf-8');

                        // Construct the XML response
                        $response = '<?xml version="1.0" encoding="UTF-8"?>';
                        $response .= '<Response>';
                        $response .= '<Dial record="true" sequential="true" phoneNumbers="' . preg_replace('/^\+/', '', trim($clientDialedNumber)) . '"/>';
                        $response .= '</Response>';

                        // Debugging logs
                        Log::info("📲 Outgoing call from $callerNumber to $clientDialedNumber");
                        Log::info("Generated XML Response: " . json_encode($response, JSON_UNESCAPED_SLASHES));

                        // Print & exit to ensure no extra output
                        echo trim($response);
                        exit;
                    case 'CallInitiated':
                        Log::info("🔄 Call initiated: $callerNumber -> $clientDialedNumber");
                        $this->updateCallHistory($sessionId, ['status' => 'initiated']);
                        break;

                    case 'CallConnected':
                        Log::info("✅ Call connected between $callerNumber and $clientDialedNumber");
                        $this->updateCallHistory($sessionId, ['status' => 'connected']);
                        break;

                    case 'CallTerminated':
                        Log::info("⏹️ Call terminated for session: $sessionId");
                        if ($isActive) {
                            Log::info("✅ Call is active. Caller: $callerNumber, Destination: $clientDialedNumber");
                            $this->updateCallHistory($sessionId, [
                                'callerNumber' => $callerNumber,
                                'destinationNumber' => $clientDialedNumber,
                                'direction' => 'outgoing',
                                'isActive' => 1,
                            ]);
                        }
                        break;

                    case 'Completed':
                        Log::info("⏹️ Call ended. Updating call history for session: $sessionId");
                        $this->updateCallHistory($sessionId, [
                            'isActive' => 0,
                            'recordingUrl' => $request->input('recordingUrl'),
                            'durationInSeconds' => $request->input('durationInSeconds'),
                            'currencyCode' => $request->input('currencyCode'),
                            'amount' => $request->input('amount'),
                            'hangupCause' => $request->input('hangupCause'),
                            'status' => $request->input('status'),
                            'dialStartTime' => $request->input('dialStartTime'),
                            'dialDurationInSeconds' => $request->input('dialDurationInSeconds'),
                        ]);

                        // Reset agent status
                        Log::info("🔄 Resetting agent status for session: $sessionId");
                        User::where('sessionId', $sessionId)->update(['status' => 'available', 'sessionId' => null]);
                        break;

                    default:
                        Log::warning("⚠️ Unhandled call state: $callSessionState");
                        break;
                }
            } else {
                // Handle incoming calls
                // Compose the response for incoming calls
                // Handle incoming calls
                // Clear any previous output
                if (ob_get_length()) {
                    ob_clean();
                }



        // ✅ PRIORITY: Process user input FIRST
        if ($request->has('dtmfDigits')) {
            return response($this->handleSelection($request->dtmfDigits))
                ->header('Content-Type', 'application/xml');
        }



             // ✅ If no input was received, continue IVR flow
             return response($this->generateDynamicMenu($request->input('step', 1)))
             ->header('Content-Type', 'application/xml');
     }
 
           

            Log::info("📞 Call session state: $callSessionState for session: $sessionId");
            
        } catch (\Exception $e) {
            Log::error("❌ Error in handleVoiceCallback: " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }



    private function dialNumber($phoneNumber)
    {
        return "<?xml version=\"1.0\" encoding=\"UTF-8\"?>
        <Response>
            <Dial phoneNumbers=\"{$phoneNumber}\" />
        </Response>";
    }


    private function getAvailableAgent()
    {
        Log::info('Checking for available agents...');

        $agent = User::where('status', 'available')
            // ->where('is_active', true)
            // ->where('can_receive_calls', true)
            ->first();

        Log::info($agent ? "Found available agent: {$agent->phone_number}" : 'No available agents found.');

        return $agent ? $agent->phone_number : null;
    }

    /**
     * Update call history.
     */
    private function updateCallHistory(string $sessionId, array $data): void
    {
        $call = CallHistory::updateOrCreate(['sessionId' => $sessionId], $data);
        broadcast(new CallStatusUpdated($call));
    }



    public function handleEventCallback(Request $request)
    {
        try {

            // Dialing,Bridged,Completed ,hangup , 
            // Log full request data for debugging
            Log::info('📡 Received event callback', [
                'headers' => $request->headers->all(),
                'body' => $request->all()
            ]);

            // Extract the payload from the request
            $payload = $request->all();

            // Store the request body in the CallHistory table
            CallHistory::updateOrCreate(
                ['sessionId' => $payload['sessionId'] ?? null],
                [
                    'callerNumber' => $payload['callerNumber'] ?? null,
                    'destinationNumber' => $payload['destinationNumber'] ?? null,
                    'direction' => $payload['direction'] ?? null,
                    'status' => $payload['status'] ?? null,
                    'isActive' => $payload['isActive'] ?? null,
                    'callStartTime' => $payload['callStartTime'] ?? null,
                    'durationInSeconds' => $payload['durationInSeconds'] ?? null,
                    'amount' => $payload['amount'] ?? null,
                    'currencyCode' => $payload['currencyCode'] ?? null,
                    'callerCountryCode' => $payload['callerCountryCode'] ?? null,
                    'callerCarrierName' => $payload['callerCarrierName'] ?? null,
                    'dialStartTime' => $payload['dialStartTime'] ?? null,
                    'dialDurationInSeconds' => $payload['dialDurationInSeconds'] ?? null,
                    // 'clientDialedNumber' => $payload['clientDialedNumber'] ?? null,
                    'callerNumber' => $payload['callerNumber'] ?? null,
                    'recordingUrl' => $payload['recordingUrl'] ?? null,
                ]
            );

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error("❌ Error : " . $e->getMessage(), [
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Internal Server Error'], 500);
        }
    }

    


    public function uploadMediaFile()
{
    $username = env('AFRICASTALKING_USERNAME');
    $apiKey = env('AFRICASTALKING_API_KEY');
    $fileUrl = "https://support.solssa.com/ringtones/office_phone.mp3"; 
    $phoneNumber = env('AFRICASTALKING_PHONE'); 
    if (!$phoneNumber) {
        Log::error('Africa’s Talking phone number is missing.');
        return response()->json(['error' => 'Internal Server Error.'], 500);
    }
    if (!$username || !$apiKey) {
        Log::error('Africa’s Talking credentials are missing.', [
            'username' => $username,
            'apiKey'   => $apiKey
        ]);
        return response()->json(['error' => 'Africa’s Talking credentials are missing.'], 500);
    }
    
    // Initialize the SDK
$AT = new AfricasTalking($username, $apiKey);

// Get the voice service
$voice = $AT->voice();


    try {
        // Upload the file
        $result = $voice->uploadMediaFile([
            "phoneNumber" => $phoneNumber,
            "url"         => $fileUrl
        ]);
    
        print_r($result);
    } catch (Exception $e) {
        echo "Error: ".$e->getMessage();
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




    public function generateDynamicMenu($step)
    {
        $options = IVROption::orderBy('option_number')->get();
    
        $response = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response>\n";
        // / Welcome message before GetDigits
        $response .= "<Say voice=\"woman\">Welcome to Boxleo Courier and Fulfillment. Your trusted logistics partner.</Say>\n";
        $response .= "<GetDigits timeout=\"3\" finishOnKey=\"#\" callbackUrl=\"https://support.solssa.com/api/v1/africastalking-handle-callback\">\n";
    
        // Combine all options into a single prompt
        $prompt = "";
        foreach ($options as $option) {
            $prompt .= "Press {$option->option_number} for {$option->description}. ";
        }
    
        $response .= "<Say voice=\"woman\" barge-in=\"true\">{$prompt}</Say>\n";
        $response .= "</GetDigits>\n";
        $response .= "<Say voice=\"woman\">We did not receive any input. Goodbye.</Say>\n";
        $response .= "</Response>";
    
        return $response;
    }
    


  
    public function handleSelection($dtmfDigits)
    {
        $options = IVROption::orderBy('option_number')->get();
        Log::info("📲 IVR Input: {$dtmfDigits} | Available Options: " . json_encode($options));

        $option = $options->where('option_number', $dtmfDigits)->first();

        if (!$option) {
            Log::warning("❌ Invalid IVR selection: {$dtmfDigits} | Redirecting to fallback");
            return $this->createVoiceResponse(
                "Invalid option selected. Please try again.",
                '+254707709370'
            );
        }

        Log::info("✅ User selected: {$option->option_number} - {$option->description}");

        if ($option->option_number == 6) {
            $agentNumber = $this->getAvailableAgent();
            return $agentNumber 
                ? $this->createVoiceResponse("Connecting you to an agent.", $agentNumber)
                : $this->createVoiceResponse("All agents are currently busy. Please try again later.");
        }

        return $this->createVoiceResponse(
            "You selected {$option->description}. Connecting your call.",
            $option->forward_number ?? '+254741821113'
        );
    }

    private function createVoiceResponse($message, $phoneNumber = null)
    {
        Log::info("Generating voice response", ['message' => $message, 'phoneNumber' => $phoneNumber]);

        $response = "<?xml version=\"1.0\" encoding=\"UTF-8\"?>\n<Response>\n";
        $response .= "<Say voice=\"woman\">{$message}</Say>\n";
        if ($phoneNumber) {
            $response .= "<Dial record=\"true\" sequential=\"true\" ringbackTone=\"https://support.solssa.com/storage/ringtones/office_phone.mp3\" phoneNumbers=\"{$phoneNumber}\"/>\n";
          


        }
        $response .= "</Response>";

        Log::debug("Voice response generated", ['response' => $response]);

        return $response;
    }
}
