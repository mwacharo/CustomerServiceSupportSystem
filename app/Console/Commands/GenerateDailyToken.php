<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class GenerateDailyToken extends Command
{
    protected $signature = 'token:generate-daily';
    protected $description = 'Generate AfricaTalking tokens for users daily at midnight';

    public function handle()
    {
        Log::info('🚀 Starting token generation process');

        $users = User::all();

        foreach ($users as $user) {
            Log::info("🔍 Processing User ID {$user->id} ({$user->name})");

            $response = $this->generateAfricaTalkingToken($user);

            if (isset($response['token'])) {
                $this->info("✅ Token generated for User ID {$user->id}");
                Log::info("✅ Token generated", [
                    'user_id' => $user->id,
                    'token' => $response['token']
                ]);
            } else {
                $this->error("❌ Failed to generate token for User ID {$user->id}");
                Log::error("❌ Token generation failed", [
                    'user_id' => $user->id,
                    'error' => $response['error'] ?? 'Unknown error',
                    'response' => $response['response'] ?? null
                ]);
            }
        }

        Log::info('🏁 Token generation process completed');
    }

    private function generateAfricaTalkingToken(User $user)
    {
        $apiKey     = config('services.africastalking.api_key');
        $username   = config('services.africastalking.username');
        $phoneNumber = config('services.africastalking.phone');
        $clientName = $user->client_name ?: 'client-' . uniqid();
        $canCall = $user->hasPermissionTo('can_call');
        $canReceiveCalls = $user->hasPermissionTo('can_receive_calls');

        Log::debug('🔐 Fetched AfricaTalking config', [
            'apiKey' => $apiKey ? '✅ Provided' : '❌ Missing',
            'username' => $username ? '✅ Provided' : '❌ Missing',
            'phoneNumber' => $phoneNumber
        ]);

        if (!$apiKey || !$username) {
            return ['error' => 'Missing API credentials'];
        }

        if (!$canCall && !$canReceiveCalls) {
            Log::warning("🚫 User ID {$user->id} lacks call permissions", [
                'name' => $user->name,
                'roles' => $user->getRoleNames()->implode(', '),
                'permissions' => $user->getAllPermissions()->pluck('name')->implode(', ')
            ]);
            return ['error' => 'User does not have permission to make or receive calls'];
        }

        $payload = [
            'username'    => $username,
            'clientName'  => $clientName,
            'phoneNumber' => $phoneNumber,
            'outgoing'    => $canCall,
            'incoming'    => $canReceiveCalls,
            'expire'      => 86400
        ];

        Log::info("📤 Sending token request", array_merge(['user_id' => $user->id], $payload));

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post('https://webrtc.africastalking.com/capability-token/request', $payload);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("✅ Token received from Africa's Talking", ['user_id' => $user->id]);
            $user->update(['token' => $data['token'], 'client_name' => $clientName]);
            return $data;
        }

        $errorResponse = $response->json();
        Log::error("❌ API call failed", ['user_id' => $user->id, 'response' => $errorResponse]);
        return ['error' => 'Failed to generate token', 'response' => $errorResponse];
    }
}
