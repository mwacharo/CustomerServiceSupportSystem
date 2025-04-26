<?php

namespace App\Http\Controllers;

use App\Models\Message;
use App\Models\User;
use App\Services\DynamicChannelCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiWhatsAppController extends Controller
{

    public function send(Request $request)
    {
        Log::info('Received request to send WhatsApp messages', ['request' => $request->all()]);
    
        // Validate incoming request
        $request->validate([
            'contacts' => 'required|array',
            'contacts.*.chatId' => 'required|string',  // Validate each contact's chatId
            'message' => 'required|string', // Validate message
        ]);
    
        Log::info('Request validated successfully');
    
        // Get user (sender) credentials
        $credentialable = User::find($request->user_id);
    
        if (!$credentialable) {
            Log::error('Sender not found', ['user_id' => $request->user_id]);
            return response()->json(['error' => 'Sender not found'], 404);
        }
    
        Log::info('Sender found', ['user_id' => $request->user_id]);
    
        // Fetch the credentials for the WhatsApp API
        $credentialService = new DynamicChannelCredentialService($credentialable, 'whatsapp');
    
        // Get the token and the sender's phone number (this acts as the chatId)
        $token = $credentialService->getApiToken();
        $sender = $credentialService->getPhoneNumber(); // This will be used as the sender's chatId
    
        // Retrieve the account_id (instance ID) dynamically
        $accountId = $credentialService->getAccountId(); // Assuming getAccountId() method exists or you can add it
    
        Log::info('Credentials retrieved', ['token' => '************', 'sender' => $sender, 'accountId' => $accountId]); // Hide the token for security
    
        $responses = [];
    
        // Loop through contacts and send messages
        foreach ($request->contacts as $contact) {
            Log::info('Preparing to send message', ['chatId' => $contact['chatId'], 'message' => $request->message]);
    
            // Prepare request body for each contact
            $data = [
                'chatId' => $contact['chatId'], // Chat ID for this specific contact
                'message' => $request->message, // The message to send
                'sender' => $sender, // Sender’s phone number
                'mentions' => $request->mentions ?? [], // Optional mentions array
                'replyToMessageId' => $request->replyToMessageId ?? null, // Optional reply ID
                'previewLink' => $request->previewLink ?? true, // Whether to show link previews
            ];
    
            // Store the message in the database before sending it
            $message = Message::create([
                'user_id' => $request->user_id,
                'chat_id' => $contact['chatId'],
                'content' => $request->message,
                'status' => 'pending', // Initially set to 'pending'
                'reply_to_message_id' => $request->replyToMessageId,
            ]);




            // $table->id();
            // $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            // $table->string('channel')->nullable(); // SMS, Email, WhatsApp etc.
            // $table->string('recipient_name')->nullable();
            // $table->string('recipient_phone');
            // $table->text('content');
            // $table->string('status')->default('sent'); // sent, delivered, failed
            // $table->timestamp('sent_at')->nullable();
            // $table->json('response_payload')->nullable();
            // $table->timestamps();
            // $table->softDeletes();
    
            Log::info('Message stored in database', ['message_id' => $message->id]);
    
            // Construct the dynamic URL using the account ID
            $url = "https://waapi.app/api/v1/instances/{$accountId}/client/action/send-message";
    
            // Send request to the WhatsApp API
            try {
                $response = Http::withToken($token)
                    ->post($url, $data);
    
                // Update the message status based on the response
                $status = $response->successful() ? 'sent' : 'failed';
                $message->update([
                    'message_status' => $status, // Update message status to sent/failed
                ]);
    
                // Log and collect the response for each contact
                $responses[] = [
                    'chatId' => $contact['chatId'],
                    'status' => $status,
                    'response' => $response->json(),
                ];
    
                Log::info('Message sent', [
                    'chatId' => $contact['chatId'],
                    'status' => $status,
                    'response' => $response->json(),
                ]);
            } catch (\Exception $e) {
                Log::error('Error sending message', [
                    'chatId' => $contact['chatId'],
                    'error' => $e->getMessage(),
                ]);
    
                // If an error occurs, update the message status to 'failed'
                $message->update([
                    'message_status' => 'failed',
                ]);
    
                $responses[] = [
                    'chatId' => $contact['chatId'],
                    'status' => 'failed',
                    'response' => [
                        'error' => $e->getMessage(),
                    ],
                ];
            }
        }
    
        Log::info('All messages processed', ['responses' => $responses]);
    
        return response()->json([
            'status' => 'done',
            'results' => $responses,
        ]);
    }
    



//     public function send(Request $request)
// {
//     Log::info('Received request to send WhatsApp messages', ['request' => $request->all()]);

//     // Validate incoming request
//     $request->validate([
//         'contacts' => 'required|array',
//         'contacts.*.chatId' => 'required|string',  // Validate each contact's chatId
//         'message' => 'required|string', // Validate message
//     ]);

//     Log::info('Request validated successfully');

//     // Get user (sender) credentials
//     $credentialable = User::find($request->user_id);

//     if (!$credentialable) {
//         Log::error('Sender not found', ['user_id' => $request->user_id]);
//         return response()->json(['error' => 'Sender not found'], 404);
//     }

//     Log::info('Sender found', ['user_id' => $request->user_id]);

//     // Fetch the credentials for the WhatsApp API
//     $credentialService = new DynamicChannelCredentialService($credentialable, 'whatsapp');

//     // Get the token and the sender's phone number (this acts as the chatId)
//     $token = $credentialService->getApiToken();
//     $sender = $credentialService->getPhoneNumber(); // This will be used as the sender's chatId

//     // Retrieve the account_id (instance ID) dynamically
//     $accountId = $credentialService->getAccountId(); // Assuming getAccountId() method exists or you can add it

//     Log::info('Credentials retrieved', ['token' => '************', 'sender' => $sender, 'accountId' => $accountId]); // Hide the token for security

//     $responses = [];

//     // Loop through contacts and send messages
//     foreach ($request->contacts as $contact) {
//         Log::info('Preparing to send message', ['chatId' => $contact['chatId'], 'message' => $request->message]);

//         // Prepare request body for each contact
//         $data = [
//             'chatId' => $contact['chatId'], // Chat ID for this specific contact
//             'message' => $request->message, // The message to send
//             'sender' => $sender, // Sender’s phone number
//             'mentions' => $request->mentions ?? [], // Optional mentions array
//             'replyToMessageId' => $request->replyToMessageId ?? null, // Optional reply ID
//             'previewLink' => $request->previewLink ?? true, // Whether to show link previews
//         ];

//         // Construct the dynamic URL using the account ID
//         $url = "https://waapi.app/api/v1/instances/{$accountId}/client/action/send-message";

//         // Send request to the WhatsApp API
//         try {
//             $response = Http::withToken($token)
//                 ->post($url, $data);

//             // Log and collect the response for each contact
//             $responses[] = [
//                 'chatId' => $contact['chatId'],
//                 'status' => $response->successful() ? 'sent' : 'failed',
//                 'response' => $response->json(),
//             ];

//             Log::info('Message sent', [
//                 'chatId' => $contact['chatId'],
//                 'status' => $response->successful() ? 'sent' : 'failed',
//                 'response' => $response->json(),
//             ]);
//         } catch (\Exception $e) {
//             Log::error('Error sending message', [
//                 'chatId' => $contact['chatId'],
//                 'error' => $e->getMessage(),
//             ]);

//             $responses[] = [
//                 'chatId' => $contact['chatId'],
//                 'status' => 'failed',
//                 'response' => [
//                     'error' => $e->getMessage(),
//                 ],
//             ];
//         }
//     }

//     Log::info('All messages processed', ['responses' => $responses]);

//     return response()->json([
//         'status' => 'done',
//         'results' => $responses,
//     ]);
// }



    
}
