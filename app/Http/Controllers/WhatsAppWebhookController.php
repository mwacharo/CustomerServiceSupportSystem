<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\Services\AIResponderService;
use App\Services\WhatsAppMessageService;
use App\Models\User;


use App\Services\WhatsApp\Handlers\MessageHandler;
use App\Services\WhatsApp\Handlers\GroupHandler;
use App\Services\WhatsApp\Handlers\CallHandler;



class WhatsAppWebhookController extends Controller
{


    // protected $aiResponder;

    // public function __construct(AIResponderService $aiResponder)
    // {
    //     $this->aiResponder = $aiResponder;
    // }

 

    // public function receive(Request $request)
    // {
    //     Log::info('Incoming WhatsApp Event', $request->all());
    
    //     $eventType = $request->input('event');
    
    //     if ($eventType !== 'message') {
    //         Log::info("Ignoring non-message event: $eventType");
    //         return response()->json(['status' => 'Ignored non-message event']);
    //     }
    
    //     $messageData = $request->input('data.message');
    //     $internalData = $request->input('data.message._data');
    
    //     if (!$messageData) {
    //         Log::error('No message data received', $request->all());
    //         return response()->json(['error' => 'Invalid data'], 400);
    //     }
    
    //     $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
    //     $fromMe = $messageData['fromMe'] ?? false;
    //     $body = $messageData['body'] ?? '';
    //     $timestamp = $messageData['timestamp'] ?? time();
    //     $type = $messageData['type'] ?? 'text';
    //     $mediaUrl = $request->input('data.media.url');
    //     $mediaMimeType = $request->input('data.media.mimetype');
    
    //     $externalMessageId = $messageData['id']['id'] ?? null;
    //     $replyToMessageId = $internalData['parentMsgId'] ?? null;
    //     $notifyName = $internalData['notifyName'] ?? null;
    //     $direction = $fromMe ? 'outgoing' : 'incoming';
    
    //     // Hash fallback for messages without externalMessageId
    //     $messageHash = md5($waId . $timestamp . $body);
    
    //     // Check for duplicate by external ID or hash
    //     if ($externalMessageId) {
    //         $existingMessage = Message::where('external_message_id', $externalMessageId)->first();
    //     } else {
    //         $existingMessage = Message::where('message_hash', $messageHash)->first();
    //     }
    
    //     if ($existingMessage) {
    //         Log::info('Duplicate message detected, skipping storage', [
    //             'external_message_id' => $externalMessageId,
    //             'message_hash' => $messageHash
    //         ]);
    //         return response()->json(['status' => 'Message already exists']);
    //     }
    
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
    //         'message_hash' => $messageHash, // fallback identifier
    //     ];
    
    //     try {
    //         $message = Message::create($messageAttributes);
    
    //         $aiReply = $this->aiResponder->interpretCustomerQuery($body);
    
    //         if ($aiReply) {
    //             $message->update(['ai_interpretation' => $aiReply]);
    //             Log::info('AI interpretation successful', ['ai_interpretation' => $aiReply]);
    //         }
    
    //         $user = User::where('phone', $waId)->first() ?? User::first();
    
    //         $whatsappService = new WhatsAppMessageService($user);
    //         $sendResult = $whatsappService->sendMessage($waId . '@c.us', $aiReply);
    
    //         if ($sendResult['status'] === 'sent') {
    //             Log::info('AI response sent', ['chatId' => $waId, 'response' => $aiReply]);
    //         } else {
    //             Log::warning('Failed to send AI response', ['error' => $sendResult['error'] ?? 'unknown']);
    //         }
    
    //         Log::info('Message stored successfully', ['message_id' => $message->id]);
    //         return response()->json(['status' => 'Message stored successfully']);
    //     } catch (\Exception $e) {
    //         Log::error('Error storing message', [
    //             'error_message' => $e->getMessage(),
    //             'attributes' => $messageAttributes,
    //             'trace' => $e->getTraceAsString()
    //         ]);
    //         return response()->json(['error' => 'Failed to store message'], 500);
    //     }
    // }
    



    protected $handlers;

    public function __construct()
    {
        $this->handlers = [
            'message' => app(MessageHandler::class),
            // 'group_join' => app(GroupHandler::class),
            // 'group_leave' => app(GroupHandler::class),
            // 'group_update' => app(GroupHandler::class),
            // 'call' => app(CallHandler::class),
            // Add other event handlers here...
        ];
    }

    public function receive(Request $request)
    {
        $event = $request->input('event');

        Log::info("Incoming WhatsApp Event", [
            'event' => $event,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
        ]);

        if (!isset($this->handlers[$event])) {
            Log::info("No handler for event: $event", [
                'event' => $event,
                'available_handlers' => array_keys($this->handlers),
            ]);
            return response()->json(['status' => "Ignored event type: $event"], 200);
        }

        try {
            $handler = $this->handlers[$event];
            Log::info("Handler found for event: $event", ['handler' => get_class($handler)]);

            $response = $handler->handle($request->all());

            Log::info("Event handled successfully", [
                'event' => $event,
                'response' => $response,
            ]);

            return response()->json(['status' => 'Handled', 'details' => $response], 200);
        } catch (\Exception $e) {
            Log::error("Error handling $event", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'event' => $event,
            ]);
            return response()->json(['error' => "Failed to handle event"], 500);
        }
    }



}
