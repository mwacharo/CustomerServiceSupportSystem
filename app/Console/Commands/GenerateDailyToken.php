<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Exception;

class GenerateAfricasTalkingTokens extends Command
{
    protected $signature = 'generate:tokens';

    protected $description = 'Generate Africa\'s Talking WebRTC capability tokens for all users';

    public function handle()
    {
        $apiKey = config('services.africastalking.api_key');
        $username = config('services.africastalking.username');
        $phoneNumber = config('services.africastalking.phone');

        if (!$username || !$apiKey) {
            $this->error('Africa’s Talking credentials are missing.');
            Log::error('Africa’s Talking credentials are missing.', [
                'username' => $username,
                'apiKey'   => $apiKey
            ]);
            return Command::FAILURE;
        }

        $users = User::all();
        $updatedTokens = [];
        $failedUpdates = [];

        foreach ($users as $user) {
            try {
                if (empty($user->client_name)) {
                    $user->client_name = 'client_' . $user->id . '_' . substr(md5(uniqid()), 0, 6);
                    $user->save();
                }

                $clientName = str_replace(' ', '', $user->client_name);

                // Use Spatie permissions
                $incoming = $user->hasPermissionTo('receive calls');
                $outgoing = $user->hasPermissionTo('make calls');

                Log::info('Generating token for user', [
                    'user_id' => $user->id,
                    'clientName' => $clientName,
                    'phoneNumber' => $phoneNumber
                ]);

                $payload = [
                    'username'    => $username,
                    'clientName'  => $clientName,
                    'phoneNumber' => $phoneNumber,
                    'incoming'    => $incoming ? "true" : "false",
                    'outgoing'    => $outgoing ? "true" : "false",
                    'lifeTimeSec' => "86400"
                ];

                $url = 'https://webrtc.africastalking.com/capability-token/request';
                $ch = curl_init($url);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
                curl_setopt($ch, CURLOPT_HTTPHEADER, [
                    'apiKey: ' . $apiKey,
                    'Accept: application/json',
                    'Content-Type: application/json'
                ]);

                $response = curl_exec($ch);
                if (curl_errno($ch)) {
                    throw new Exception('cURL Error: ' . curl_error($ch));
                }

                curl_close($ch);
                $responseData = json_decode($response, true);

                if (!isset($responseData['token'])) {
                    throw new Exception($responseData['message'] ?? 'Unknown API error');
                }

                $user->updateOrFail(['token' => $responseData['token']]);

                Log::info("Token updated successfully for user {$user->id}", [
                    'token' => $responseData['token']
                ]);

                $updatedTokens[] = [
                    'user_id' => $user->id,
                    'token' => $responseData['token'],
                    'clientName' => $responseData['clientName'] ?? $clientName,
                    'incoming' => $responseData['incoming'] ?? null,
                    'outgoing' => $responseData['outgoing'] ?? null,
                    'lifeTimeSec' => $responseData['lifeTimeSec'] ?? null,
                    'message' => $responseData['message'] ?? null,
                    'success' => $responseData['success'] ?? false
                ];
            } catch (Exception $e) {
                Log::error("Token generation failed for user {$user->id}: " . $e->getMessage());

                $failedUpdates[] = [
                    'user_id' => $user->id,
                    'error'   => $e->getMessage()
                ];
            }
        }

        $this->info("Token generation complete.");
        $this->info("Success: " . count($updatedTokens));
        $this->info("Failed: " . count($failedUpdates));

        return Command::SUCCESS;
    }
}
