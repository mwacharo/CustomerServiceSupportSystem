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

        $request->validate([
            'contacts' => 'required|array',
            'message' => 'required|string',
            'chatId' => 'required|string',  // Validate chatId
        ]);

        Log::info('Request validated successfully');

        $credentialable = User::find($request->user_id);

        if (!$credentialable) {
            Log::error('Sender not found', ['user_id' => $request->user_id]);
            return response()->json(['error' => 'Sender not found'], 404);
        }

        Log::info('Sender found', ['user_id' => $request->user_id]);

        $credentialService = new DynamicChannelCredentialService($credentialable, 'whatsapp');

        $token = $credentialService->getApiToken();
        $sender = $credentialService->getPhoneNumber();

        Log::info('Credentials retrieved', ['token' => $token, 'sender' => $sender]);

        $responses = [];

        foreach ($request->contacts as $contact) {
            Log::info('Sending message', ['phone' => $contact['phone'], 'message' => $request->message]);

            // Prepare request body
            $data = [
                'chatId' => $request->chatId, // The chat ID (phone number or group chat ID)
                'message' => $request->message, // The message to send
                'sender' => $sender, // Sender's phone number
                'mentions' => $request->mentions ?? [], // Optional mentions array
                'replyToMessageId' => $request->replyToMessageId ?? null, // Optional reply ID
                'previewLink' => $request->previewLink ?? true, // Whether to show link previews
            ];

            // Send request to the WhatsApp API
            $response = Http::withToken($token)
                ->post('https://waapi.app/api/v1/messages', $data);

            $responses[] = [
                'phone' => $contact['phone'],
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
            ];

            Log::info('Message sent', [
                'phone' => $contact['phone'],
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
//     public function send(Request $request)
//     {
//         Log::info('Received request to send WhatsApp messages', ['request' => $request->all()]);

//         $request->validate([
//             'contacts' => 'required|array',
//             'message' => 'required|string',
//         ]);

//         Log::info('Request validated successfully');

//         $credentialable = User::find($request->user_id);

//         if (!$credentialable) {
//             Log::error('Sender not found', ['user_id' => $request->user_id]);
//             return response()->json(['error' => 'Sender not found'], 404);
//         }

//         Log::info('Sender found', ['user_id' => $request->user_id]);

//         $credentialService = new DynamicChannelCredentialService($credentialable, 'whatsapp');

//         $token = $credentialService->getApiToken();
//         $sender = $credentialService->getPhoneNumber();

//         Log::info('Credentials retrieved', ['token' => $token, 'sender' => $sender]);

//         $responses = [];

//         foreach ($request->contacts as $contact) {
//             Log::info('Sending message', ['phone' => $contact['phone'], 'message' => $request->message]);

//             $response = Http::withToken($token)
//                 ->post('https://waapi.app/api/v1/messages', [
//                     'phone' => $contact['phone'],
//                     'message' => $request->message,
//                     'sender' => $sender,
//                 ]);

//             $responses[] = [
//                 'phone' => $contact['phone'],
//                 'status' => $response->successful() ? 'sent' : 'failed',
//                 'response' => $response->json(),
//             ];

//             Log::info('Message sent', [
//                 'phone' => $contact['phone'],
//                 'status' => $response->successful() ? 'sent' : 'failed',
//                 'response' => $response->json(),
//             ]);
//         }

//         Log::info('All messages processed', ['responses' => $responses]);

//         return response()->json([
//             'status' => 'done',
//             'results' => $responses,
//         ]);
//     }
}
