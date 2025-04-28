<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
 
    public function receive(Request $request)
    {
        Log::info('Incoming WhatsApp Message', $request->all());
    
        // Get the message data from the correct path in the payload
        $messageData = $request->input('data.message');
        $internalData = $request->input('data.message._data');
        
        if (!$messageData) {
            Log::error('No message data received', $request->all());
            return response()->json(['error' => 'Invalid data'], 400);
        }
    
        // Extract data from the correct structure
        $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
        $fromMe = $messageData['fromMe'] ?? false;
        $body = $messageData['body'] ?? '';
        $timestamp = $messageData['timestamp'] ?? time();
        $type = $messageData['type'] ?? 'text';
        $mediaUrl = null;
        $mediaMimeType = null;
        
        if ($request->input('data.media')) {
            $mediaUrl = $request->input('data.media.url') ?? null;
            $mediaMimeType = $request->input('data.media.mimetype') ?? null;
        }
        
        $externalMessageId = null;
        if (isset($messageData['id']) && isset($messageData['id']['id'])) {
            $externalMessageId = $messageData['id']['id'];
        }
        
        $replyToMessageId = $internalData['parentMsgId'] ?? null;
        $notifyName = $internalData['notifyName'] ?? null;
        
        $direction = $fromMe ? 'outgoing' : 'incoming';
    
        try {
            $message = Message::create([
                'messageable_type' => null,
                'messageable_id' => null,
                'channel' => 'whatsapp',
                'recipient_name' => $notifyName,
                'recipient_phone' => $waId,
                'content' => $body,
                'status' => 'received',
                'sent_at' => date('Y-m-d H:i:s', $timestamp),
                'response_payload' => $request->all(),
                'from' => $messageData['from'] ?? null,
                'to' => $messageData['to'] ?? null,
                'body' => $body,
                'message_type' => $type,
                'media_url' => $mediaUrl,
                'media_mime_type' => $mediaMimeType,
                'message_status' => 'received',
                'external_message_id' => $externalMessageId,
                'reply_to_message_id' => $replyToMessageId,
                'timestamp' => date('Y-m-d H:i:s', $timestamp),
                'direction' => $direction,
            ]);
            
            Log::info('Message stored successfully', ['message_id' => $message->id]);
            return response()->json(['status' => 'Message stored successfully']);
        } catch (\Exception $e) {
            Log::error('Error storing message', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return response()->json(['error' => 'Failed to store message'], 500);
        }
    }


// public function receive(Request $request)
// {
//     Log::info('Incoming WhatsApp Message', $request->all());

//     $messageData = $request->input('data.message._data'); // <- notice _data

//     if (!$messageData) {
//         Log::error('No message data received', $request->all());
//         return response()->json(['error' => 'Invalid data'], 400);
//     }

//     $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
//     $fromMe = $messageData['id']['fromMe'] ?? false;
//     $body = $messageData['body'] ?? '';
//     $timestamp = $messageData['t'] ?? time(); // it is `t` inside _data, not timestamp
//     $type = $messageData['type'] ?? 'text'; 
//     $mediaUrl = $messageData['mediaUrl'] ?? null;
//     $mediaMimeType = $messageData['mediaMimeType'] ?? null;
//     $externalMessageId = $messageData['id']['id'] ?? null; // id is nested
//     $replyToMessageId = $messageData['parentMsgId'] ?? null;

//     $direction = $fromMe ? 'outgoing' : 'incoming';

//     Message::create([
//         'messageable_type' => null,
//         'messageable_id' => null,
//         'channel' => 'whatsapp',
//         'recipient_name' => $messageData['notifyName'] ?? null,
//         'recipient_phone' => $waId,
//         'content' => $body,
//         'status' => 'sent',
//         'sent_at' => date('Y-m-d H:i:s', $timestamp),
//         'response_payload' => json_encode($messageData),
//         'from' => $messageData['from'] ?? null,
//         'to' => $messageData['to'] ?? null,
//         'body' => $body,
//         'message_type' => $type,
//         'media_url' => $mediaUrl,
//         'media_mime_type' => $mediaMimeType,
//         'message_status' => 'pending',
//         'external_message_id' => $externalMessageId,
//         'reply_to_message_id' => $replyToMessageId,
//         'timestamp' => date('Y-m-d H:i:s', $timestamp),
//         'direction' => $direction,
//     ]);

//     return response()->json(['status' => 'Message stored successfully']);
// }


 }
