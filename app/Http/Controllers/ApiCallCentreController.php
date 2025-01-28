<?php

namespace App\Http\Controllers\Api;

use Exception;
use App\CallHistory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Http\Controllers\Controller;



namespace App\Http\Controllers;

use AfricasTalking\SDK\AfricasTalking;
use Illuminate\Http\Request;

class ApiCallCentreController extends Controller
{

    // public $username = "Sandbox";
    // public $apiKey = "atsk_7ffe8c424b8befc58aaaed4682b51dbca631135eddbcfbc7038a403daba5070fb44fe124";




    public function makeCall(Request $request)
    {
        $phone = $request->input('phone');
        $from = $request->input('from', '+25409924432'); 


        // Validate the phone number
        if (empty($phone)) {
            return response()->json(['error' => 'Phone number is required.'], 400);
        }

        // Set up Africa's Talking API
        $username = config('services.africastalking.username');
        $apiKey = config('services.africastalking.api_key');

        // Initialize Africa's Talking API
        $africastalking = new AfricasTalking($username, $apiKey);

        // Set the call parameters
        $callParams = [
            'from' => $request->input('from', '25409924432'),  
            'to' => $phone,
        ];

        try {
            // Make the call
            $response = $africastalking->voice()->call($callParams);

            // Return a success response
            return response()->json([
                'message' => 'Call initiated successfully',
                'data' => $response,
            ], 200);
        } catch (\Exception $e) {
            // Handle any errors
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
        $agents = Agent::where('status', 'busy')->count();

        return $agents >= 10;  // Assume we have 10 agents
    }


    public function transferCall(Request $request)
    {
        $agentId = $request->input('agentId');
        $callId = $request->input('callId');  // Assuming you pass callId for the active call

        // Find the agent details
        $agent = Agent::find($agentId);

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

    public function handleEventCallback(Request $request){

        Log::info($request);
        $callSessionState = $request->callSessionState;
        if($callSessionState == 'Transferred' || $callSessionState == 'TransferCompleted'){

            $callTransferredToNumber = $request->callTransferredToNumber;
            $sessionId = $request->sessionId;

            $call_history = DB::table('call_histories')
                ->where('isActive', 1)
                ->where('sessionId', $sessionId)
                ->where('deleted_at', null)
                ->first();

            if($call_history){

                // Update current agent
                $update_call_agent = DB::table('call_agents')
                    ->where('admin_id', $call_history->adminId)
                    ->update([
                        'status' => 'busy',
                        'sessionId' => $sessionId,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                $call_agent = DB::table('call_agents')
                    ->where('client_name', substr($callTransferredToNumber, strpos($callTransferredToNumber, ".") + 1))
                    ->where('deleted_at', null)
                    ->first();

                if($call_agent){

                    // Update next agent
                    $update_call_agent = DB::table('call_agents')
                        ->where('id', $call_agent->id)
                        ->update([
                            'status' => 'busy',
                            'sessionId' => $sessionId,
                            'updated_at' => date('Y-m-d H:i:s'),
                        ]);
                }
            }

        }elseif ($callSessionState == 'Active'){

            if($request->has('callTransferState')){

                $callTransferState = $request->callTransferState;
                if($callTransferState == 'CallerHangup'){

                    $sessionId = $request->sessionId;
                    $call_history = DB::table('call_histories')
                        ->where('isActive', 1)
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->first();

                    if($call_history){

                        // Update current agent
                        $update_call_agent = DB::table('call_agents')
                            ->where('admin_id', $call_history->adminId)
                            ->update([
                                'status' => 'available',
                                'sessionId' => $sessionId,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        if($update_call_agent){

                            $call_agent = DB::table('call_agents')
                                ->where('sessionId', $sessionId)
                                ->where('deleted_at', null)
                                ->first();

                            if($call_agent){

                                $update_call_history = DB::table('call_histories')
                                    ->where('id', $call_history->id)
                                    ->update([
                                        'adminId' => $call_agent->admin_id,
                                        'agentId' => $call_agent->client_name,
                                        'nextCallStep' => 'in_progress',
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);

                            }
                        }

                    }

                }elseif ($callTransferState == 'CalleeHangup'){

                    $sessionId = $request->sessionId;
                    $call_history = DB::table('call_histories')
                        ->where('isActive', 1)
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->first();

                    if($call_history){

                        $call_agent = DB::table('call_agents')
                            ->where('sessionId', $sessionId)
                            ->where('deleted_at', null)
                            ->first();

                        if($call_agent){

                            if($call_history->agentId != $call_agent->client_name){

                                $update_call_agent = DB::table('call_agents')
                                    ->where('id', $call_agent->id)
                                    ->update([
                                        'status' => 'available',
                                        'sessionId' => '',
                                        'updated_at' => date('Y-m-d H:i:s'),
                                    ]);
                            }

                        }

                    }

                }
            }
        }

    }

    public function handleVoiceCallback(Request $request)
    {

        $isActive = $request->isActive;
        $sessionId = $request->sessionId;
        $direction = $request->direction;
        $callerNumber = $request->callerNumber;
        $destinationNumber = $request->destinationNumber;

        if ($isActive == 1) {

            if ($direction == 'Inbound') {

                $check_call_agent_count = DB::table('call_agents')
                    ->where('client_name', substr($callerNumber, strpos($callerNumber, ".") + 1))
                    ->where('deleted_at', null)
                    ->count();

                if ($check_call_agent_count > 0) {

                    $call_agent = DB::table('call_agents')
                        ->where('client_name', substr($callerNumber, strpos($callerNumber, ".") + 1))
                        ->where('deleted_at', null)
                        ->first();

                    $clientDialedNumber = $request->clientDialedNumber;
                    if ($clientDialedNumber == '+254730731433' || $clientDialedNumber == '+254730731433' ) {

                        $call_history_count = DB::table('call_histories')
                            ->where('isActive', 1)
                            ->where('nextCallStep', 'enqueue')
                            ->where('conference', '!=', null)
                            ->where('deleted_at', null)
                            ->count();

                        if ($call_history_count > 0) {

                            $call_history = DB::table('call_histories')
                                ->where('isActive', 1)
                                ->where('nextCallStep', 'enqueue')
                                ->where('conference', '!=', null)
                                ->where('deleted_at', null)
                                ->orderBy('created_at', 'ASC')
                                ->first();

                            if ($call_history) {

                                    DB::table('call_agents')
                                    ->where('id', $call_agent->id)
                                    ->update([
                                        'status' => 'busy',
                                        'sessionId' => $sessionId,
                                        'updated_at' => date('Y-m-d H:i:s'),
                                    ]);

                                $update_call_history = DB::table('call_histories')
                                    ->where('id', $call_history->id)
                                    ->update([
                                        'adminId' => $call_agent->admin_id,
                                        'agentId' => $call_agent->client_name,
                                        'nextCallStep' => 'in_progress',
                                        'updated_at' => date('Y-m-d H:i:s')
                                    ]);
                            }

                            $conference_name = $call_history->conference;
                            $response = '<?xml version="1.0" encoding="UTF-8"?>';
                            $response .= '<Response>';
                            $response .= '<Conference maxParticipants="2" record="true" startOnEnter="true" endOnExit="true">' . $conference_name . '</Conference>';
                            $response .= '</Response>';
                            header('Content-type: text/plain');
                            echo $response;
                            exit();

                        } else {

                            $text = "Sorry, there are no calls in the waiting queue";
                            $response = '<?xml version="1.0" encoding="UTF-8"?>';
                            $response .= '<Response>';
                            $response .= '<Say voice="en-US-Wavenet-F">' . $text . '</Say>';
                            $response .= '<Reject/>';
                            $response .= '</Response>';
                            header('Content-type: text/plain');
                            echo $response;
                            exit();
                        }

                    } else {

                        $call_history = new CallHistory();
                        $call_history->create([
                            'isActive' => $isActive,
                            'callerNumber' => $callerNumber,
                            'destinationNumber' => $clientDialedNumber,
                            'direction' => 'outbound',
                            'sessionId' => $sessionId,
                            'adminId' => $call_agent->admin_id,
                            'agentId' => $call_agent->client_name,
                        ]);

                            DB::table('call_agents')
                            ->where('id', $call_agent->id)
                            ->update([
                                'status' => 'busy',
                                'sessionId' => $sessionId,
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        $response = '<?xml version="1.0" encoding="UTF-8"?>';
                        $response .= '<Response>';
                        $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $clientDialedNumber . '" ringbackTone="https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav" />';
                        $response .= '<Record trimSilence="true"></Record>';
                        $response .= '</Response>';

                        // Print the response onto the page so that our gateway can read it
                        header('Content-type: application/xml');
                        echo $response;
                        exit();
                    }

                } else {

                    // Get call history
                    $call_history_count = DB::table('call_histories')
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->count();

                    if ($call_history_count > 0) {

                        $update_call_history = DB::table('call_histories')
                            ->where('sessionId', $sessionId)
                            ->update([
                                'isActive' => $isActive,
                                'callerNumber' => $callerNumber,
                                'destinationNumber' => $destinationNumber,
                            ]);

                    } else {

                        $call_history = new CallHistory();
                        $call_history->create([
                            'isActive' => $isActive,
                            'callerNumber' => $callerNumber,
                            'destinationNumber' => $destinationNumber,
                            'direction' => $direction,
                            'sessionId' => $sessionId,
                            'nextCallStep' => 'welcome',
                        ]);

                    }

                    $call_history = DB::table('call_histories')
                        ->where('sessionId', $sessionId)
                        ->where('deleted_at', null)
                        ->first();

                    // Get steps
                    if ($call_history->nextCallStep === 'welcome') {

                        $update_call_history = DB::table('call_histories')
                            ->where('sessionId', $sessionId)
                            ->update([
                                'nextCallStep' => 'agent_speak',
                                'updated_at' => date('Y-m-d H:i:s'),
                            ]);

                        if ($update_call_history) {

                            $milliseconds = round(microtime(true) * 1000);
                            $generated_conference_name = 'ca'.$milliseconds;

                            $call_agents_check = DB::table('call_agents')
                                ->where('status', 'available')
                                ->where('deleted_at', null)
                                ->count();

                            if ($call_agents_check > 0) {

                                $call_agent = DB::table('call_agents')
                                    ->where('status', 'available')
                                    ->where('deleted_at', null)
                                    ->orderBy('updated_at', 'ASC')
                                    ->first();

                                if ($call_agent) {

                                        DB::table('call_agents')
                                        ->where('id', $call_agent->id)
                                        ->update([
                                            'status' => 'busy',
                                            'sessionId' => $sessionId,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);

                                    $update_call_history = DB::table('call_histories')
                                        ->where('sessionId', $sessionId)
                                        ->update([
                                            'adminId' => $call_agent->admin_id,
                                            'agentId' => $call_agent->client_name,
                                            'nextCallStep' => 'in_progress',
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);

                                    $say_welcome_text = "Welcome to Boxleo Courier and Fulfillment Services Limited.";
                                    $say_quality_text = "Please wait while we transfer your call to the next available agent.This call may be recorded for internal training and quality purposes.";
                                    $call_agent_phone = "BoxleoKenya." . $call_agent->client_name;
                                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                                    $response .= '<Response>';
                                    $response .= '<Say voice="en-US-Wavenet-F">' . $say_welcome_text . '</Say>';
                                    $response .= '<Say voice="en-US-Wavenet-F">' . $say_quality_text . '</Say>';
                                    $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $call_agent_phone . '" ringbackTone="https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav" />';
                                    $response .= '<Record trimSilence="true"></Record>';
                                    $response .= '</Response>';

                                    // Print the response onto the page so that our gateway can read it
                                    header('Content-type: application/xml');
                                    echo $response;
                                    exit();

                                } else {

                                    $update_call_history = DB::table('call_histories')
                                        ->where('sessionId', $sessionId)
                                        ->update([
                                            'nextCallStep' => 'enqueue',
                                            'conference' => $generated_conference_name,
                                            'updated_at' => date('Y-m-d H:i:s'),
                                        ]);

                                    // Pick up agent queue
                                    $say_busy_text = "All our customer service representatives are currently busy, please hold and we will attend to you shortly";
                                    $response = '<?xml version="1.0" encoding="UTF-8"?>';
                                    $response .= '<Response>';
                                    $response .= '<Say voice="en-US-Wavenet-F">' . $say_busy_text . '</Say>';
                                    $response .= '<Conference maxParticipants="2" record="true" startOnEnter="true" endOnExit="true"  waitUrl="https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav">'.$generated_conference_name.'</Conference>';
                                    $response .= '</Response>';
                                    header('Content-type: text/plain');
                                    echo $response;
                                    exit();

                                }

                            } else {

                                $update_call_history = DB::table('call_histories')
                                    ->where('sessionId', $sessionId)
                                    ->update([
                                        'nextCallStep' => 'enqueue',
                                        'conference' => $generated_conference_name,
                                        'updated_at' => date('Y-m-d H:i:s'),
                                    ]);

                                // Pick up agent queue
                                $say_welcome_text = "Welcome to Boxleo Courier and Fulfillment Services Limited.";
                                $say_busy_text = "All our customer service representatives are currently not available, please hold and we will attend to you shortly";
                                $response = '<?xml version="1.0" encoding="UTF-8"?>';
                                $response .= '<Response>';
                                $response .= '<Say voice="en-US-Wavenet-F">' . $say_welcome_text . '</Say>';
                                $response .= '<Say voice="en-US-Wavenet-F">' . $say_busy_text . '</Say>';
                                $response .= '<Conference maxParticipants="2" record="true" startOnEnter="true" endOnExit="true"  waitUrl="https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav">'.$generated_conference_name.'</Conference>';
                                $response .= '</Response>';
                                header('Content-type: text/plain');
                                echo $response;
                                exit();
                            }

                        }

                    }

                }

            } else {

                $response = '<?xml version="1.0" encoding="UTF-8"?>';
                $response .= '<Response>';
                $response .= '<Dial record="true" sequential="true" phoneNumbers="' . $callerNumber . '" ringbackTone="https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav" />';
                $response .= '</Response>';

                // Print the response onto the page so that our gateway can read it
                header('Content-type: text/plain');
                echo $response;
                exit();

            }

        } else {

            $recordingUrl = $request->recordingUrl;
            $durationInSeconds = $request->durationInSeconds;
            $currencyCode = $request->currencyCode;
            $amount = $request->amount;
            $hangupCause = $request->hangupCause;

            $call_history = DB::table('call_histories')
                ->where('sessionId', $sessionId)
                ->first();

            if($call_history){

                $update_call_history = DB::table('call_histories')
                    ->where('sessionId', $sessionId)
                    ->update([
                        'isActive' => $isActive,
                        'recordingUrl' => $recordingUrl,
                        'durationInSeconds' => $durationInSeconds,
                        'currencyCode' => $currencyCode,
                        'amount' => $amount,
                        'hangupCause' => $hangupCause,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);


                    DB::table('call_agents')
                    ->where('sessionId', $sessionId)
                    ->update([
                        'status' => 'available',
                        'sessionId' => null,
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

            }


        }

    }

    public function uploadMediaFile()
    {
        $AT = new AfricasTalking($this->username, $this->apiKey);
        $voice = $AT->voice();
        $phoneNumber = "+254730731433";
        $fileUrl = "https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav";

        try {
            // Upload the file
            $result = $voice->uploadMediaFile([
                "phoneNumber" => $phoneNumber,
                "url" => $fileUrl
            ]);

            print_r($result);

        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    public function getToken(Request $request)
    {

        $clientName = $request->clientName;
        $curl = curl_init();
        curl_setopt_array($curl, array(
            CURLOPT_URL => "https://webrtc.africastalking.com/capability-token/request",
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => "",
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => "POST",
            CURLOPT_POSTFIELDS => "{\n    \"username\": \"BoxleoKenya\",\n    \"phoneNumber\": \"+254730731433\",\n    \"clientName\": \"$clientName\",\n    \"incoming\": \"true\",\n    \"lifeTimeSec\": \"864000\",\n    \"outgoing\": \"true\",\n    \"token\": \"ATCAPtkn_206675b68efaff83d1ac2d027dd5bff18fd7cb64fgjhd5d0bdcsac44a883678afe7\"\n}",
            CURLOPT_HTTPHEADER => array(
                "apikey: 0fbf70babd408769207a740119b305da1c5505f72c9e91dce8031f7515a94255",
                "Content-Type: application/json"
            ),
        ));

        $response = curl_exec($curl);
        curl_close($curl);

        $content = json_decode($response, true);
        $call_agent = DB::table('call_agents')
            ->where('client_name', $clientName)
            ->first();

        if ($call_agent) {

            $update = DB::table('call_agents')
                ->where('id', $call_agent->id)
                ->update([
                    'token' => $content['token'],
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

        }

        return $content['token'];

    }

    // public function transferCall(Request $request)
    // {
    //     $client_name = $request->client_name;
    //     $transfer_client_name = $request->transfer_client_name;

    //     $call_agent_available = DB::table('call_agents')
    //         ->where('client_name', $transfer_client_name)
    //         ->where('status', 'available')
    //         ->count();

    //     if ($call_agent_available > 0) {
    //         $call_history = DB::table('call_histories')
    //             ->where('isActive', 1)
    //             ->where('agentId', $client_name)
    //             ->latest()
    //             ->first();

    //         if ($call_history) {
    //             $transfer_client_name = "BoxleoKenya." . $transfer_client_name;
    //             $curl = curl_init();
    //             curl_setopt_array($curl, [
    //                 CURLOPT_URL => "https://voice.africastalking.com/callTransfer",
    //                 CURLOPT_RETURNTRANSFER => true,
    //                 CURLOPT_ENCODING => "",
    //                 CURLOPT_MAXREDIRS => 10,
    //                 CURLOPT_TIMEOUT => 0,
    //                 CURLOPT_FOLLOWLOCATION => true,
    //                 CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
    //                 CURLOPT_CUSTOMREQUEST => "POST",
    //                 CURLOPT_POSTFIELDS => [
    //                     "sessionId" => $call_history->sessionId,
    //                     "phoneNumber" => $transfer_client_name,
    //                     "holdMusicUrl" => "https://boxleocourier.com/dashboard/api/v1/get-audio/playMusic.wav",
    //                     "username" => "BoxleoKenya",
    //                     "callLeg" => "caller"
    //                 ],
    //                 CURLOPT_HTTPHEADER => [
    //                     "apikey: 0fbf70babd408769207a740119b305da1c5505f72c9e91dce8031f7515a94255"
    //                 ],
    //             ]);

    //             $response = curl_exec($curl);
    //             curl_close($curl);

    //             $xml = simplexml_load_string($response);
    //             $json = json_encode($xml);
    //             $array = json_decode($json, true);

    //             if ($array['status'] == 'Success') {
    //                 // Hang up the transferring agent's call
    //                 $hangup_response = $this->hangupCall($call_history->sessionId);

    //                 if ($hangup_response['success']) {
    //                     $json_array = array(
    //                         'success' => 1,
    //                         'message' => 'Call successfully transferred',
    //                     );
    //                 } else {
    //                     $json_array = array(
    //                         'success' => 0,
    //                         'message' => 'Error hanging up the transferring agent\'s call',
    //                     );
    //                 }

    //                 $response = $json_array;
    //                 return json_encode($response);
    //             } else {
    //                 $json_array = array(
    //                     'success' => 0,
    //                     'message' => $array['errorMessage'],
    //                 );

    //                 $response = $json_array;
    //                 return json_encode($response);
    //             }
    //         }
    //     } else {
    //         $json_array = array(
    //             'success' => 0,
    //             'message' => 'Agent not available',
    //         );

    //         $response = $json_array;
    //         return json_encode($response);
    //     }
    // }

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


    // public function dequeueCall(Request $request){

    // }





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
        foreach ($call_histories as $call_history){

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if($admin){
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push($json_results,
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
                ));


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
        foreach ($call_agents as $call_agent){

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_agent->admin_id)
                ->where('deleted_at', null)
                ->first();

            if($admin){
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
            if($summary_delivery_order + $summary_scheduled_order > 0){
                $summary_delivery_rate = $summary_delivery_order / ($summary_delivery_order + $summary_scheduled_order);
                $summary_delivery_rate = $summary_delivery_rate * 100;
            }

            array_push($json_results,
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
                ));


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
        foreach ($call_agents as $call_agent){

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_agent->admin_id)
                ->where('deleted_at', null)
                ->first();

            if($admin){
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
            if($summary_delivery_order + $summary_scheduled_order > 0){
                $summary_delivery_rate = $summary_delivery_order / ($summary_delivery_order + $summary_scheduled_order);
                $summary_delivery_rate = $summary_delivery_rate * 100;
            }

            array_push($json_results,
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
                ));

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
        foreach ($call_histories as $call_history){

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if($admin){
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push($json_results,
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
                ));


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
            $call_histories->where('destinationNumber', 'LIKE', "%{$destination_number}%") ;
        }

        if ($caller_number != '') {
            $call_histories->where('callerNumber', 'LIKE', "%{$caller_number}%") ;
        }


        $call_histories = $call_histories->get();
        $json_results = array();

        foreach ($call_histories as $call_history){

            $admin_name = "";
            $admin = DB::table('admins')
                ->where('id', $call_history->adminId)
                ->where('deleted_at', null)
                ->first();

            if($admin){
                $admin_name = $admin->first_name . ' ' . $admin->last_name;
            }

            array_push($json_results,
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
                ));



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

    public function callOrderHistory(Request $request){

        $phone_number = $request->phone_number;
        if(substr($phone_number,0, 1) == "+"){
            $phone_number = substr_replace($phone_number,"",0, 1);
        }

        $orders = DB::table('orders')
            ->where('receiver_phone', $phone_number)
            ->where('deleted_at', null)
            ->latest()
            ->get();

        return json_encode($orders);

    }

}


