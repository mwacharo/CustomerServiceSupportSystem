<?php

namespace App\Http\Controllers;

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
    
        Log::info('Credentials retrieved', ['token' => $token, 'sender' => $sender]);
    
        $responses = [];
    
        // Loop through contacts and send messages
        foreach ($request->contacts as $contact) {
            Log::info('Sending message', ['chatId' => $contact['chatId'], 'message' => $request->message]);
    
            // Prepare request body for each contact
            $data = [
                'chatId' => $contact['chatId'], // Chat ID for this specific contact
                'message' => $request->message, // The message to send
                'sender' => $sender, // Sender’s phone number
                'mentions' => $request->mentions ?? [], // Optional mentions array
                'replyToMessageId' => $request->replyToMessageId ?? null, // Optional reply ID
                'previewLink' => $request->previewLink ?? true, // Whether to show link previews
            ];
    
            // Send request to the WhatsApp API
            


            $response = Http::withHeaders([
                'accept' => 'application/json',
                'authorization' => 'Bearer ' . $token, // Add Bearer token to headers
                'content-type' => 'application/json',
            ])
                ->post('https://waapi.app/api/v1/instances/62238/client/action/send-message', [
                    'chatId' => $contact['chatId'],
                    'message' => $request->message,
                ]);
            // Log and collect the response for each contact
            $responses[] = [
                'chatId' => $contact['chatId'],
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
            ];
    
            Log::info('Message sent', [
                'chatId' => $contact['chatId'],
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
            ]);
        }
    
        Log::info('All messages processed', ['responses' => $responses]);
    
        return response()->json([
            'status' => 'done',
            'results' => $responses,
        ]);
    }
    
}
