<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\Services\AIResponderService;
use App\Services\WhatsAppMessageService;
use App\Models\User;



class WhatsAppWebhookController extends Controller
{


    protected $aiResponder;

    public function __construct(AIResponderService $aiResponder)
    {
        $this->aiResponder = $aiResponder;
    }

    // public function receive(Request $request)
    // {
    //     Log::info('Incoming WhatsApp Message', $request->all());

    //     // Get the message data from the correct path in the payload
    //     $messageData = $request->input('data.message');
    //     $internalData = $request->input('data.message._data');

    //     if (!$messageData) {
    //         Log::error('No message data received', $request->all());
    //         return response()->json(['error' => 'Invalid data'], 400);
    //     }

    //     // Extract data from the correct structure
    //     $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
    //     $fromMe = $messageData['fromMe'] ?? false;
    //     $body = $messageData['body'] ?? '';
    //     $timestamp = $messageData['timestamp'] ?? time();
    //     $type = $messageData['type'] ?? 'text';
    //     $mediaUrl = null;
    //     $mediaMimeType = null;

    //     if ($request->input('data.media')) {
    //         $mediaUrl = $request->input('data.media.url') ?? null;
    //         $mediaMimeType = $request->input('data.media.mimetype') ?? null;
    //     }

    //     $externalMessageId = null;
    //     if (isset($messageData['id']) && isset($messageData['id']['id'])) {
    //         $externalMessageId = $messageData['id']['id'];
    //     }

    //     $replyToMessageId = $internalData['parentMsgId'] ?? null;
    //     $notifyName = $internalData['notifyName'] ?? null;
    //     $direction = $fromMe ? 'outgoing' : 'incoming';

    //     // Prepare the message attributes
    //     $messageAttributes = [
    //         'messageable_type' => null,
    //         'messageable_id' => null,
    //         'channel' => 'whatsapp',
    //         'recipient_name' => $notifyName,
    //         'recipient_phone' => $waId,
    //         'content' => $body,
    //         'status' => 'received',
    //         'sent_at' => date('Y-m-d H:i:s', $timestamp),
    //         'response_payload' => $request->all(),
    //         'from' => $messageData['from'] ?? null,
    //         'to' => $messageData['to'] ?? null,
    //         'body' => $body,
    //         'message_type' => $type,
    //         'media_url' => $mediaUrl,
    //         'media_mime_type' => $mediaMimeType,
    //         'message_status' => 'received',
    //         'external_message_id' => $externalMessageId,
    //         'reply_to_message_id' => $replyToMessageId,
    //         'timestamp' => date('Y-m-d H:i:s', $timestamp),
    //         'direction' => $direction,
    //     ];

    //     // Log the message attributes before trying to save
    //     Log::debug('Prepared message attributes for storage', $messageAttributes);

    //     try {
    //         // Prevent creating duplicate when external_message_id is missing
    //         $externalId = $messageAttributes['external_message_id'] ?? null;

    //         if (!$externalId) {
    //             Log::warning('Missing external_message_id, skipping message storage', ['attributes' => $messageAttributes]);
    //             return response()->json(['error' => 'Missing external_message_id'], 400);
    //         }

    //         $existingMessage = Message::where('external_message_id', $externalId)->first();

    //         if ($existingMessage) {
    //             Log::info('Duplicate message detected, skipping storage', ['external_message_id' => $externalId]);
    //             return response()->json(['status' => 'Message already exists']);
    //         }

    //         $message = Message::create($messageAttributes);

    //         // $aiReply = interpretCustomerQuery($body);

    //         $aiReply = $this->aiResponder->interpretCustomerQuery($body);

    //         if ($aiReply) {
    //             $message->update(['ai_interpretation' => $aiReply]);
    //             Log::info('AI interpretation successful', ['ai_interpretation' => $aiReply]);
    //         } else {
    //             Log::error('AI interpretation failed', ['message' => $body]);
    //         }

    //         Log::info('AI interpretation', ['message' => $body, 'interpretation' => $aiReply]);


    
    //         // You can get the user from auth or define a default one (e.g., admin user)
    //         $user = User::where('phone', $waId)->first(); // Adjust logic based on your system
    //         if (!$user) {
    //             $user = User::first(); // fallback
    //         }

    //         $whatsappService = new WhatsAppMessageService($user);
    //         $sendResult = $whatsappService->sendMessage($waId . '@c.us', $aiReply);

    //         if ($sendResult['status'] === 'sent') {
    //             Log::info('AI response sent', ['chatId' => $waId, 'response' => $aiReply]);
    //         } else {
    //             Log::warning('Failed to send AI response via WhatsApp', ['chatId' => $waId, 'error' => $sendResult['error'] ?? 'unknown']);
    //         }

    //         Log::info('Message stored successfully', ['message_id' => $message->id]);
    //         return response()->json(['status' => 'Message stored successfully']);
    //     } catch (\Exception $e) {
    //         Log::error('Error storing message', [
    //             'error_message' => $e->getMessage(),
    //             'attributes' => $messageAttributes,
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['error' => 'Failed to store message', 'details' => $e->getMessage()], 500);
    //     }
    // }


    public function receive(Request $request)
{
    // Log the incoming request for debugging purposes
    Log::info('Incoming WhatsApp Message', $request->all());

    // Check if the event is a 'message_create' or 'message' event
    $eventType = $request->input('event');
    
    // Handle only 'message' events that contain actual message data
    if ($eventType === 'message' || $eventType === 'message_create') {
        // Get the message data from the correct path in the payload
        $messageData = $request->input('data.message');
        $internalData = $request->input('data.message._data');
        
        // Ensure the message data is not empty and contains a valid body
        if (!$messageData || !isset($messageData['body']) || empty($messageData['body'])) {
            Log::error('Invalid message data received or no valid message body', $request->all());
            return response()->json(['error' => 'Invalid message body'], 400);
        }

        // Extract the message ID to avoid duplication
        $externalMessageId = $messageData['id']['id'] ?? null;

        if (!$externalMessageId) {
            Log::warning('Missing external_message_id, skipping message storage', $request->all());
            return response()->json(['error' => 'Missing external_message_id'], 400);
        }

        // Check if the message already exists in the database based on the external_message_id
        $existingMessage = Message::where('external_message_id', $externalMessageId)->first();

        if ($existingMessage) {
            // Log the duplicate message detection and skip storing it
            Log::info('Duplicate message detected, skipping storage', ['external_message_id' => $externalMessageId]);
            return response()->json(['status' => 'Message already exists'], 200);
        }

        // If it's a new message, proceed to store it
        $newMessage = new Message();
        $newMessage->external_message_id = $externalMessageId;
        $newMessage->sender_id = $messageData['from'] ?? null;
        $newMessage->message_body = $messageData['body'];
        $newMessage->timestamp = $messageData['timestamp'] ?? now();
        $newMessage->status = 'received'; // You can adjust status accordingly

        // Save the new message to the database
        $newMessage->save();

        // Log the successful storage of the new message
        Log::info('New message stored successfully', ['external_message_id' => $externalMessageId]);

        return response()->json(['status' => 'Message received and stored successfully'], 200);
    }

    // If the event type is not recognized, return an error
    Log::warning('Unrecognized event type', ['event_type' => $eventType]);
    return response()->json(['error' => 'Unrecognized event type'], 400);
}



}
