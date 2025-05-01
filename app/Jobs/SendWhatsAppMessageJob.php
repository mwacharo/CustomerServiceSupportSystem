<?php

namespace App\Jobs;

use App\Models\Message;
use App\Models\User;
use App\Services\DynamicChannelCredentialService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SendWhatsAppMessageJob implements ShouldQueue
{
    use Dispatchable, Queueable;

    protected $chatId;
    protected $messageContent;
    protected $userId;

    public function __construct($chatId, $messageContent, $userId)
    {
        $this->chatId = $chatId;
        $this->messageContent = $messageContent;
        $this->userId = $userId;
    }

    public function handle()
    {
        $user = User::find($this->userId);
        if (!$user) {
            Log::error('User not found', ['user_id' => $this->userId]);
            return;
        }

        $credentialService = new DynamicChannelCredentialService($user, 'whatsapp');
        $token = $credentialService->getApiToken();
        $sender = $credentialService->getPhoneNumber();
        $accountId = $credentialService->getAccountId();

        $data = [
            'chatId' => $this->chatId,
            'message' => $this->messageContent,
            'sender' => $sender,
        ];

        $url = "https://waapi.app/api/v1/instances/{$accountId}/client/action/send-message";

        try {
            $response = Http::withToken($token)->post($url, $data);
            $status = $response->successful() ? 'sent' : 'failed';

            $responseData = $response->json();
            $externalMessageId = data_get($responseData, 'responses.0.response.data.data._data.id._serialized');


            // Log missing _serialized if null
            if (!$externalMessageId) {
                Log::warning('Missing _serialized WhatsApp ID', [
                    'chatId' => $this->chatId,
                    'response' => $responseData,
                ]);
            }

            $message = Message::create([
                'recipient_phone' => $this->chatId,
                'content' => $this->messageContent,
                'message_status' => $status,
                'external_message_id' => $externalMessageId,
                'messageable_id' => $user->id,
                'messageable_type' => get_class($user),
                'response_payload' => $responseData,
            ]);

            Log::info('Message record created', ['message_id' => $message->id, 'chatId' => $this->chatId]);

            // Log::info('Message processed', ['chatId' => $this->chatId, 'status' => $status]);
        } catch (\Exception $e) {
            Log::error('Error sending message', ['chatId' => $this->chatId, 'error' => $e->getMessage()]);
        }
    }
}
