<?php

namespace App\Services\WhatsApp\Handlers;

use App\Models\Message;
use App\Models\User;
use App\Services\AIResponderService;
use App\Services\WhatsAppMessageService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MessageHandler
{
    protected $aiResponder;

    // public function __construct(AIResponderService $aiResponder)
    // {
    //     $this->aiResponder = $aiResponder;
    // }

    // public function handle(Request $request): string
    // {
    //     $data = $request->all();
    //     $messageData = $data['data']['message'] ?? null;
    //     $internalData = $messageData['_data'] ?? [];

    //     if (!$messageData) {
    //         throw new \Exception('No message data found');
    //     }

    //     $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
    //     $fromMe = $messageData['fromMe'] ?? false;
    //     $body = $messageData['body'] ?? '';
    //     $timestamp = $messageData['timestamp'] ?? time();
    //     $type = $messageData['type'] ?? 'text';

    //     $externalMessageId = $messageData['id']['id'] ?? null;
    //     $replyToMessageId = $internalData['parentMsgId'] ?? null;
    //     $notifyName = $internalData['notifyName'] ?? null;
    //     $direction = $fromMe ? 'outgoing' : 'incoming';
    //     $messageHash = md5($waId . $timestamp . $body);

    //     $existingMessage = $externalMessageId
    //         ? Message::where('external_message_id', $externalMessageId)->first()
    //         : Message::where('message_hash', $messageHash)->first();

    //     if ($existingMessage) {
    //         Log::info('Duplicate message detected', compact('externalMessageId', 'messageHash'));
    //         return 'Duplicate message skipped';
    //     }

    //     $message = Message::create([
    //         'channel' => 'whatsapp',
    //         'recipient_name' => $notifyName,
    //         'recipient_phone' => $waId,
    //         'content' => $body,
    //         'status' => 'received',
    //         'sent_at' => date('Y-m-d H:i:s', $timestamp),
    //         'from' => $messageData['from'] ?? null,
    //         'to' => $messageData['to'] ?? null,
    //         'body' => $body,
    //         'message_type' => $type,
    //         'external_message_id' => $externalMessageId,
    //         'reply_to_message_id' => $replyToMessageId,
    //         'timestamp' => date('Y-m-d H:i:s', $timestamp),
    //         'direction' => $direction,
    //         'message_hash' => $messageHash,
    //         'response_payload' => $data,
    //     ]);

    //     $aiReply = $this->aiResponder->interpretCustomerQuery($body);

    //     if ($aiReply) {
    //         $message->update(['ai_interpretation' => $aiReply]);

    //         $user = User::where('phone', $waId)->first() ?? User::first();
    //         $waService = new WhatsAppMessageService($user);
    //         $waService->sendMessage($waId . '@c.us', $aiReply);
    //     }

    //     return "Message processed";
    // }




    public function __construct(AIResponderService $aiResponder)
    {
        $this->aiResponder = $aiResponder;
    }

    public function handle(Request $request): string
    {
        $event = $request->input('event');

        if (!method_exists($this, $event)) {
            Log::warning("No method found in MessageHandler for event: $event");
            return "No handler for $event";
        }

        return $this->$event($request);
    }

    public function message(Request $request): string
    {
        $data = $request->all();
        $messageData = $data['data']['message'] ?? null;
        $internalData = $messageData['_data'] ?? [];

        if (!$messageData) {
            throw new \Exception('No message data found');
        }

        $waId = str_replace('@c.us', '', $messageData['from'] ?? '');
        $fromMe = $messageData['fromMe'] ?? false;
        $body = $messageData['body'] ?? '';
        $timestamp = $messageData['timestamp'] ?? time();
        $type = $messageData['type'] ?? 'text';

        $externalMessageId = $messageData['id']['id'] ?? null;
        $replyToMessageId = $internalData['parentMsgId'] ?? null;
        $notifyName = $internalData['notifyName'] ?? null;
        $direction = $fromMe ? 'outgoing' : 'incoming';
        $messageHash = md5($waId . $timestamp . $body);

        $existingMessage = $externalMessageId
            ? Message::where('external_message_id', $externalMessageId)->first()
            : Message::where('message_hash', $messageHash)->first();

        if ($existingMessage) {
            Log::info('Duplicate message detected', compact('externalMessageId', 'messageHash'));
            return 'Duplicate message skipped';
        }

        $message = Message::create([
            'channel' => 'whatsapp',
            'recipient_name' => $notifyName,
            'recipient_phone' => $waId,
            'content' => $body,
            'status' => 'received',
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'from' => $messageData['from'] ?? null,
            'to' => $messageData['to'] ?? null,
            'body' => $body,
            'message_type' => $type,
            'external_message_id' => $externalMessageId,
            'reply_to_message_id' => $replyToMessageId,
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'direction' => $direction,
            'message_hash' => $messageHash,
            'response_payload' => $data,
        ]);

        $aiReply = $this->aiResponder->interpretCustomerQuery($body);

        if ($aiReply) {
            $message->update(['ai_interpretation' => $aiReply]);

            $user = User::where('phone', $waId)->first() ?? User::first();
            $waService = new WhatsAppMessageService($user);
            $waService->sendMessage($waId . '@c.us', $aiReply);
        }

        return "Message processed";
    }

    public function message_create(Request $request): string
    {
        $payload = $request->input('data.message');

        if (!$payload) {
            return "Invalid or missing message data.";
        }

        $waMessageId = $payload['id']['_serialized'] ?? null;
        $ack = $payload['ack'] ?? null;
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        if ($ack === null) {
            return "Missing ACK value.";
        }

        $status = $this->getMessageStatus($ack);

        $message = null;

        // Try matching by external_message_id first
        if ($waMessageId) {
            $message = Message::where('external_message_id', $waMessageId)->first();
        }

        // Fallback: match by phone + body + approximate timestamp
        if (!$message && $from && $body) {
            $approxTime = now()->subMinutes(5); // only recent messages
            $message = Message::where('recipient_phone', $from)
                ->where('body', $body)
                ->where('created_at', '>=', $approxTime)
                ->latest()
                ->first();
        }

        if ($message) {
            $message->update([
                'ack' => $ack,
                'message_status' => $status,
                'external_message_id' => $waMessageId ?? $message->external_message_id,
            ]);

            return "Message updated with ACK: $ack and status: $status";
        }

        return "Message not found for update.";
    }


    public function message_ack(Request $request): string
    {
        $payload = $request->input('data.message');
    
        if (!$payload) {
            return "Invalid or missing message data.";
        }
    
        $waMessageId = $payload['id']['_serialized'] ?? null;
        $ack = $payload['ack'] ?? null;
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;
    
        if ($ack === null) {
            return "Missing ACK value.";
        }
    
        $status = $this->getMessageStatus($ack);
    
        $message = null;
    
        // Try matching by external_message_id first
        if ($waMessageId) {
            $message = Message::where('external_message_id', $waMessageId)->first();
        }
    
        // Fallback: match by recipient_phone + body + recent timestamp
        if (!$message && $from && $body) {
            $approxTime = now()->subMinutes(5);
            $message = Message::where('recipient_phone', $from)
                ->where('body', $body)
                ->where('created_at', '>=', $approxTime)
                ->latest()
                ->first();
        }
    
        if ($message) {
            $message->update([
                'ack' => $ack,
                'message_status' => $status,
                'external_message_id' => $waMessageId ?? $message->external_message_id,
            ]);
    
            return "Message ACK updated with status: $status";
        }
    
        return "Message not found for ACK update";
    }
    

    public function loading_screen(Request $request): string
    {
        Log::info("Loading screen event triggered", $request->all());
        return "Loading screen event logged";
    }

    public function qr(Request $request): string
    {
        Log::info("QR code generated", $request->all());
        return "QR event handled";
    }

    public function authenticated(Request $request): string
    {
        Log::info("Authenticated event received", $request->all());
        return "Authenticated successfully";
    }

    public function auth_failure(Request $request): string
    {
        Log::error("Authentication failed", $request->all());
        return "Authentication failed";
    }

    public function ready(Request $request): string
    {
        Log::info("WhatsApp instance is ready", $request->all());
        return "Instance ready";
    }

    public function disconnected(Request $request): string
    {
        Log::warning("Instance disconnected", $request->all());
        return "Instance disconnected";
    }

    // Add more methods for:
    // message_edit, message_revoke_everyone, message_ack, etc.




    // Helper function to convert ack value to message status
    protected function getMessageStatus(int $ack): string
    {
        switch ($ack) {
            case 0:
                return 'Pending'; // Message is pending to be sent
            case 1:
                return 'Sent'; // Message has been sent
            case 2:
                return 'Delivered'; // Message has been delivered to recipient's device
            case 3:
                return 'Read'; // Message has been read by the recipient
            default:
                return 'Unknown'; // Default status if ack value is unknown
        }
    }
}
