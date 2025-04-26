<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\DynamicChannelCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class ApiWhatsAppController extends Controller
{
    //

    public function send(Request $request)
    {
        $request->validate([
            'contacts' => 'required|array',
            'message' => 'required|string',
        ]);



        $credentialable =

            User::find($request->user_id);

        if (!$credentialable) {
            return response()->json(['error' => 'Sender not found'], 404);
        }
        $credentialService = new DynamicChannelCredentialService($credentialable, 'whatsapp');

        $token = $credentialService->getApiToken();
        $sender = $credentialService->getPhoneNumber();

        $responses = [];

        foreach ($request->contacts as $contact) {
            $response = Http::withToken($token)
                ->post('https://waapi.app/api/v1/messages', [
                    'phone' => $contact['phone'],
                    'message' => $request->message,
                    'sender' => $sender, // Optional if waapi requires sender
                ]);

            $responses[] = [
                'phone' => $contact['phone'],
                'status' => $response->successful() ? 'sent' : 'failed',
                'response' => $response->json(),
            ];
        }

        return response()->json([
            'status' => 'done',
            'results' => $responses,
        ]);
    }

    // public function send(Request $request)
    // {
    //     $request->validate([
    //         'contacts' => 'required|array',
    //         'message' => 'required|string',
    //     ]);

    //     // Load WhatsApp credentials (adjust this logic as needed)
    //     $credential = ChannelCredential::where('channel', 'whatsapp')
    //         ->where('status', 'active')
    //         ->firstOrFail();

    //     $token = $credential->api_key; // or access_token if using Meta/WAAPI
    //     $sender = $credential->phone_number;

    //     $responses = [];

    //     foreach ($request->contacts as $contact) {
    //         $response = Http::withToken($token)
    //             ->post('https://waapi.app/api/v1/messages', [
    //                 'phone' => $contact['phone'],
    //                 'message' => $request->message,
    //             ]);

    //         $responses[] = [
    //             'phone' => $contact['phone'],
    //             'status' => $response->successful() ? 'sent' : 'failed',
    //             'response' => $response->json(),
    //         ];
    //     }

    //     return response()->json([
    //         'status' => 'done',
    //         'results' => $responses
    //     ]);
    // }

}
