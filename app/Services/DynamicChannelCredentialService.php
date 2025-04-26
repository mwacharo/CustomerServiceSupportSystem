<?php

namespace App\Services;

use App\Models\ChannelCredential;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Log;

class DynamicChannelCredentialService
{
    protected ?ChannelCredential $credential = null;

    public function __construct(Model $credentialable, string $channel, ?string $provider = null)
    {
        $query = $credentialable->channelCredentials()
            ->forChannel($channel)
            ->where('status', 'active');

        if ($provider) {
            $query->forProvider($provider);
        }

        $this->credential = $query->first();

        if (app()->environment('local') || config('app.debug')) {
            Log::info("Loaded {$channel} credentials", [
                'credential' => $this->credential?->toArray(),
                'for_model' => class_basename($credentialable),
                'model_id' => $credentialable->id ?? null
            ]);
        }

        if (!$this->credential) {
            $type = class_basename($credentialable);
            $id = $credentialable->id ?? 'unknown';
            Log::warning("No active {$channel} credentials found for {$type} ID: {$id}");
            throw new \Exception("No active {$channel} credentials found for {$type} ID: {$id}");
        }
    }

    public function getCredential(): ChannelCredential
    {
        return $this->credential;
    }

    public function getApiToken(): string
    {
        return $this->credential->api_key;
    }

    public function getPhoneNumber(): ?string
    {
        return $this->credential->phone_number;
    }

    public function getProvider(): ?string
    {
        return $this->credential->provider;
    }

    public function getAccountId(): ?string
    {
        return $this->credential->account_id;
    }
}
