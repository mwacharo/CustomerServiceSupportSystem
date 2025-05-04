<?php

namespace App\Services\WhatsApp\Handlers;

use App\Models\Message;
use App\Models\Order;
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
        $event = $request->input('event');

        if (!method_exists($this, $event)) {
            Log::warning("No method found in MessageHandler for event: $event");
            return "No handler for $event";
        }

        return $this->$event($request);
    }
    // Someone has sent you a message via WhatsApp.
    // incoming message


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

        // call function to get recent orders
        Log::info("Fetching recent orders for phone", ['phone' => $waId]);
        $recentOrders = $this->getRecentOrdersForPhone($waId);

        if ($recentOrders->isNotEmpty()) {
            Log::info("Recent orders found", ['orders' => $recentOrders->toArray()]);
            $orderSummary = $this->formatOrderSummary($recentOrders);
        } else {
            Log::info("No recent orders found for phone", ['phone' => $waId]);
            $orderSummary = "We couldn't find any recent orders for your number.";
        }

        Log::info("Fetching user for phone", ['phone' => $waId]);
        $user = User::where('phone', $waId)->first() ?? User::first();
        Log::info("User fetched", ['user' => $user]);

        $waService = new WhatsAppMessageService($user);

        Log::info("Sending message content to AI for interpretation", ['body' => $body]);
        // $aiReply = $this->aiResponder->interpretCustomerQuery($body);
        $aiReply = $this->aiResponder->interpretCustomerQuery($body, $recentOrders->toArray());


        if ($aiReply) {
            Log::info("AI interpretation received", ['aiReply' => $aiReply]);
            $message->update(['ai_interpretation' => $aiReply]);
            $waService->sendMessage($waId . '@c.us', $aiReply);
            Log::info("AI reply sent to user", ['phone' => $waId, 'aiReply' => $aiReply]);
        } else {
            Log::warning("No AI interpretation received for message", ['body' => $body]);
        }


        return "Message processed";
    }
    // This event is triggered when a message is created (sent).
    // Fired when a new message is created. Applies to both, sent and received messages.


    public function message_create(Request $request): string
    {
        Log::info("message_create event triggered", $request->all());

        $payload = $request->input('data.message');



        if (!$payload) {
            Log::error("Invalid or missing message data in message_create");
            return "Invalid or missing message data.";
        }


        $waMessageId = $payload['id']['id'] ?? null;
        $ack = $payload['ack'] ?? null;
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        Log::debug("Parsed message_create payload", compact('waMessageId', 'ack', 'from', 'body', 'timestamp'));

        if ($ack === null) {
            Log::warning("Missing ACK value in message_create");
            return "Missing ACK value.";
        }

        $status = $this->getMessageStatus($ack);
        Log::info("Determined message status", ['ack' => $ack, 'status' => $status]);

        $message = null;

        // Try matching by external_message_id first
        if ($waMessageId) {
            $message = Message::where('external_message_id', $waMessageId)->first();
            Log::debug("Message lookup by external_message_id", ['waMessageId' => $waMessageId, 'message' => $message]);
        }

        // Fallback: match by phone + body + approximate timestamp
        if (!$message
            //  && $from && $body
        ) {
            $approxTime = now()->subMinutes(5); // only recent messages
            $message = Message::where('recipient_phone', $from)
                // ->where('body', $body)
                ->where('created_at', '>=', $approxTime)
                ->latest()
                ->first();
            Log::debug("Message fallback lookup", ['from' => $from, 'body' => $body, 'approxTime' => $approxTime, 'message' => $message]);
        }

        if ($message) {
            $message->update([
                'ack' => $ack,
                'message_status' => $status,
                'external_message_id' => $waMessageId ?? $message->external_message_id,
            ]);

            Log::info("Message updated successfully", ['message_id' => $message->id, 'status' => $status]);
            return "Message updated with ACK: $ack and status: $status";
        }

        Log::warning("Message not found for update in message_create", compact('waMessageId', 'from', 'body'));



        // creating a new message
        $message = Message::create([
            'channel' => 'whatsapp',
            'recipient_name' => $from,
            'recipient_phone' => $from,
            'content' => $body,
            'status' => 'sent',
            'sent_at' => date('Y-m-d H:i:s', $timestamp),
            'from' => $payload['from'] ?? null,
            'to' => $payload['to'] ?? null,
            'body' => $body,
            'message_type' => $payload['type'] ?? null,
            'external_message_id' => $waMessageId,
            'timestamp' => date('Y-m-d H:i:s', $timestamp),
            'direction' => 'outgoing',
        ]);


         // call function to get recent orders

        Log::info("Fetching recent orders for phone", ['phone' => $from]);
        $recentOrders = $this->getRecentOrdersForPhone($from);

        if ($recentOrders->isNotEmpty()) {
            Log::info("Recent orders found", ['orders' => $recentOrders->toArray()]);
            $orderSummary = $this->formatOrderSummary($recentOrders);
        } else {
            Log::info("No recent orders found for phone", ['phone' => $from]);
            $orderSummary = "We couldn't find any recent orders for your number.";
        }

        Log::info("Fetching user for phone", ['phone' => $from]);
        $user = User::where('phone', $from)->first() ?? User::first();
        Log::info("User fetched", ['user' => $user]);

        $waService = new WhatsAppMessageService($user);

        Log::info("Sending message content to AI for interpretation", ['body' => $body, 'recentOrders' => $recentOrders]);
        $aiReply = $this->aiResponder->interpretCustomerQuery($body);

        if ($aiReply) {
            Log::info("AI interpretation received", ['aiReply' => $aiReply]);
            $message->update(['ai_interpretation' => $aiReply]);
            $waService->sendMessage($from . '@c.us', $aiReply);
            Log::info("AI reply sent to user", ['phone' => $from, 'aiReply' => $aiReply]);
        } else {
            Log::warning("No AI interpretation received for message", ['body' => $body]);
        }
        

        return "creating a new message.";
    }


    public function message_ack(Request $request): string
    {
        Log::info("message_ack event triggered", $request->all());

        $payload = $request->input('data.message');

        if (!$payload) {
            Log::error("Invalid or missing message data in message_ack");
            return "Invalid or missing message data.";
        }

        // $waMessageId = $payload['id']['_serialized'] ?? null;
        $waMessageId = $payload['id']['id'] ?? null;

        $ack = $payload['ack'] ?? null;
        $from = $payload['from'] ?? null;
        $body = $payload['body'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        Log::debug("Parsed message_ack payload", compact('waMessageId', 'ack', 'from', 'body', 'timestamp'));

        if ($ack === null) {
            Log::warning("Missing ACK value in message_ack");
            return "Missing ACK value.";
        }

        $status = $this->getMessageStatus($ack);
        Log::info("Determined message status", ['ack' => $ack, 'status' => $status]);

        $message = null;

        // Try matching by external_message_id first
        if ($waMessageId) {
            $message = Message::where('external_message_id', $waMessageId)->first();
            Log::debug("Message lookup by external_message_id", ['waMessageId' => $waMessageId, 'message' => $message]);
        }

        // Fallback: match by recipient_phone + body + recent timestamp
        if (!$message
            //  && $from && $body
        ) {
            $approxTime = now()->subMinutes(5);
            $message = Message::where('recipient_phone', $from)
                // ->where('body', $body)
                ->where('created_at', '>=', $approxTime)
                ->latest()
                ->first();
            Log::debug("Message fallback lookup", ['from' => $from, 'body' => $body, 'approxTime' => $approxTime, 'message' => $message]);
        }

        if ($message) {
            $message->update([
                'ack' => $ack,
                'message_status' => $status,
                'external_message_id' => $waMessageId ?? $message->external_message_id,
            ]);

            Log::info("Message updated successfully", ['message_id' => $message->id, 'status' => $status]);
            return "Message ACK updated with status: $status";
        }

        Log::warning("Message not found for ACK update in message_ack", compact('waMessageId', 'from', 'body'));
        return "Message not found for ACK update.";
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
            case -1:
                return 'Error'; // An error occurred while sending the message
            case 0:
                return 'Pending'; // Message is still pending and has not yet been sent to the server
            case 1:
                return 'Server'; // Message has been successfully sent to the WhatsApp server
            case 2:
                return 'Device'; // Message has been delivered to the recipient's device
            case 3:
                return 'Read'; // Recipient has read the message
            case 4:
                return 'Played'; // Recipient has played the audio/video message
            default:
                return 'Unknown'; // Default status if ack value is unknown
        }
    }



    private function getRecentOrdersForPhone($phone)
    {
        return Order::whereHas('client', function ($query) use ($phone) {
            $query->where('phone_number', $phone);
        })->orderBy('created_at', 'desc')->take(3)->get();
    }

    private function formatOrderSummary($orders)
    {
        $summary = "Here are your recent orders:\n";
        foreach ($orders as $order) {
            $summary .= "Order #{$order->id} - Status: {$order->status} - Date: {$order->created_at}\n";
        }
        return $summary;
    }
}
