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
    $waId = $phone . '@c.us';

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
            // $message = Message::create([
            //     'recipient_phone' => $contact['chatId'],
            //     'content' => $request->message,
            //     'message_status' => 'pending', // Initially set to 'pending'
            //     'reply_to_message_id' => $request->replyToMessageId,
            //     'messageable_id' => $credentialable->id,
            //     'messageable_type' => get_class($credentialable),
            // ]);


            // Log::info('Message stored in database', ['message_id' => $message->id]);

            // Construct the dynamic URL using the account ID
            $url = "https://waapi.app/api/v1/instances/{$accountId}/client/action/send-message";

            // Send request to the WhatsApp API
            try {
                $response = Http::withToken($token)
                    ->post($url, $data);

                // Update the message status based on the response
                $status = $response->successful() ? 'sent' : 'failed';



                $responseData = $response->json();
                $externalMessageId = $responseData['responses'][0]['response']['data']['data']['_data']['id']['_serialized'] ?? null;

                // $message->update([
                //     'message_status' => 'sent',
                //     'external_message_id' => $externalMessageId, // <-- store this for future tracking
                // ]);

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
                // $message->update([
                //     'message_status' => 'failed',
                // ]);

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




    // public function index()
    // {
    //     $messages = Message::latest()->paginate(20);

    //     return response()->json([
    //         'success' => true,
    //         'data' => $messages,
    //     ]);
    // }



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

        // Transform output (format datetime)
        // $messages->getCollection()->transform(function ($message) {
        //     return [
        //         'id' => $message->id,
        //         'content' => $message->content,
        //         'recipient_phone' => $message->recipient_phone,
        //         'status' => $message->status,
        //         'message_status' => $message->message_status,
        //         'sent_at' => optional($message->sent_at)->format('Y-m-d H:i:s'),
        //         'created_at' => optional($message->created_at)->format('Y-m-d H:i:s'),
        //     ];
        // });

        // return response()->json([
        //     'success' => true,
        //     'meta' => [
        //         'current_page' => $messages->currentPage(),
        //         'last_page' => $messages->lastPage(),
        //         'per_page' => $messages->perPage(),
        //         'total' => $messages->total(),
        //     ],
        //     'data' => $messages->items(),
        // ]);



        return MessageResource::collection($query->paginate($perPage));
    }
}
