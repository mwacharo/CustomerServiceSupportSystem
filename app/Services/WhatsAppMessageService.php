<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\DynamicChannelCredentialService;

class WhatsAppMessageService
{
    protected $credentialService;
    protected $token;
    protected $sender;
    protected $accountId;

    public function __construct(User $user)
    {
        $this->credentialService = new DynamicChannelCredentialService($user, 'whatsapp');
        $this->token = $this->credentialService->getApiToken();
        $this->sender = $this->credentialService->getPhoneNumber();
        $this->accountId = $this->credentialService->getAccountId();
    }

    public function sendMessage($chatId, $messageText, array $options = []): array
    {
        $data = [
            'chatId' => $chatId,
            'message' => $messageText,
            'sender' => $this->sender,
            'mentions' => $options['mentions'] ?? [],
            'replyToMessageId' => $options['replyToMessageId'] ?? null,
            'previewLink' => $options['previewLink'] ?? true,
        ];

        $url = "https://waapi.app/api/v1/instances/{$this->accountId}/client/action/send-message";

        try {
            $response = Http::withToken($this->token)->post($url, $data);

            $status = $response->successful() ? 'sent' : 'failed';
            $responseData = $response->json();
            $externalId = $responseData['responses'][0]['response']['data']['data']['_data']['id']['_serialized'] ?? null;

            Log::info('Message sent via service', compact('chatId', 'status', 'responseData'));

            return [
                'status' => $status,
                'external_id' => $externalId,
                'response' => $responseData,
            ];
        } catch (\Exception $e) {
            Log::error('WhatsAppMessageService error', ['chatId' => $chatId, 'error' => $e->getMessage()]);
            return [
                'status' => 'failed',
                'error' => $e->getMessage(),
            ];
        }
    }
}
