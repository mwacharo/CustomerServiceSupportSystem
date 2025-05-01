<?php

namespace App\Http\Controllers;

use App\Http\Resources\MessageResource;
use App\Models\Message;
use App\Models\User;
use App\Services\DynamicChannelCredentialService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Jobs\SendWhatsAppMessageJob;


class ApiWhatsAppController extends Controller
{


    public function getChat($phone)
    {
        $waId = $phone ;

        $messages = Message::where(function ($query) use ($waId) {
            $query->where('from', $waId)
                ->orWhere('to', $waId);
        })
            ->orderBy('timestamp', 'asc')
            ->get();

        return response()->json($messages);
    }


    // public function getChat($phone)
    // {
    //     $messages = Message::where('recipient_phone', $phone)
    //         ->orWhere('from', $phone . '@c.us')
    //         ->orderBy('timestamp', 'asc')
    //         ->get();

    //     return response()->json($messages);
    // }

    public function destroy($id)
    {
        $message = Message::findOrFail($id);
        $message->deleted_at = now();  // soft delete
        $message->save();

        return response()->json(['status' => 'success', 'message' => 'Message deleted']);
    }

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
            SendWhatsAppMessageJob::dispatch($contact['chatId'], $request->message, $request->user_id);
        }

        Log::info('All messages processed', ['responses' => $responses]);

        return response()->json([
            'status' => 'done',
            'results' => $responses,
        ]);
    }


    public function index()
    {
        $perPage = request('per_page', 20);

        // Only select necessary columns
        $query = Message::query()
            ->select([
                'id',
                'content',
                'recipient_phone',
                'status',
                'message_status',
                'sent_at',
                'created_at',
            ])
            ->whereNull('deleted_at') // Protect against soft deleted records
            ->orderByDesc('sent_at')   // Prefer sent_at if available
            ->orderByDesc('created_at'); // Fallback to created_at

        $messages = $query->paginate($perPage);



        return MessageResource::collection($query->paginate($perPage));
    }
}
