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

    public function __construct(AIResponderService $aiResponder)
    {
        $this->aiResponder = $aiResponder;
    }

    public function handle(Request $request): string
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
}
