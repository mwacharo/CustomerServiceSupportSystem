<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    //



// public function receive(Request $request)
// {

//     Log::info('Incoming WhatsApp Message', $request->all());

//     $messageData = $request->input('data.message');

//     $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
//     $fromMe = $messageData['fromMe'] ?? false;
//     $body = $messageData['body'] ?? '';
//     $timestamp = $messageData['timestamp'] ?? time();
//     $type = $messageData['type'] ?? 'text'; // default type
//     $mediaUrl = $messageData['mediaUrl'] ?? null;
//     $mediaMimeType = $messageData['mediaMimeType'] ?? null;
//     $externalMessageId = $messageData['id'] ?? null;
//     $replyToMessageId = $messageData['quotedMsgId'] ?? null;

//     // Determine direction
//     $direction = $fromMe ? 'outgoing' : 'incoming';

//     // Create the message
//     Message::create([
//         'messageable_type' => null, // if you link to customer later
//         'messageable_id' => null,    // if you link to customer later
//         'channel' => 'whatsapp',
//         'recipient_name' => null,    // You can fill this if you have name
//         'recipient_phone' => $waId,
//         'content' => $body,
//         'status' => 'sent',
//         'sent_at' => date('Y-m-d H:i:s', $timestamp),
//         'response_payload' => json_encode($messageData), // Store full payload if needed
//         'from' => $messageData['from'] ?? null,
//         'to' => $messageData['to'] ?? null,
//         'body' => $body,
//         'message_type' => $type,
//         'media_url' => $mediaUrl,
//         'media_mime_type' => $mediaMimeType,
//         'message_status' => 'pending', // pending until delivery confirmed
//         'external_message_id' => $externalMessageId,
//         'reply_to_message_id' => $replyToMessageId,
//         'timestamp' => date('Y-m-d H:i:s', $timestamp),
//         'direction' => $direction,
//     ]);

//     return response()->json(['status' => 'Message stored successfully']);
// }



public function receive(Request $request)
{
    Log::info('Incoming WhatsApp Message', $request->all());

    $messageData = $request->input('data.message._data'); // <- notice _data

    if (!$messageData) {
        Log::error('No message data received', $request->all());
        return response()->json(['error' => 'Invalid data'], 400);
    }

    $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
    $fromMe = $messageData['id']['fromMe'] ?? false;
    $body = $messageData['body'] ?? '';
    $timestamp = $messageData['t'] ?? time(); // it is `t` inside _data, not timestamp
    $type = $messageData['type'] ?? 'text'; 
    $mediaUrl = $messageData['mediaUrl'] ?? null;
    $mediaMimeType = $messageData['mediaMimeType'] ?? null;
    $externalMessageId = $messageData['id']['id'] ?? null; // id is nested
    $replyToMessageId = $messageData['parentMsgId'] ?? null;

    $direction = $fromMe ? 'outgoing' : 'incoming';

    Message::create([
        'messageable_type' => null,
        'messageable_id' => null,
        'channel' => 'whatsapp',
        'recipient_name' => $messageData['notifyName'] ?? null,
        'recipient_phone' => $waId,
        'content' => $body,
        'status' => 'sent',
        'sent_at' => date('Y-m-d H:i:s', $timestamp),
        'response_payload' => json_encode($messageData),
        'from' => $messageData['from'] ?? null,
        'to' => $messageData['to'] ?? null,
        'body' => $body,
        'message_type' => $type,
        'media_url' => $mediaUrl,
        'media_mime_type' => $mediaMimeType,
        'message_status' => 'pending',
        'external_message_id' => $externalMessageId,
        'reply_to_message_id' => $replyToMessageId,
        'timestamp' => date('Y-m-d H:i:s', $timestamp),
        'direction' => $direction,
    ]);

    return response()->json(['status' => 'Message stored successfully']);
}


 }
