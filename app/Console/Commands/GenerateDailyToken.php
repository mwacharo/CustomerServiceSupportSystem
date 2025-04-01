<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\User;

class GenerateDailyToken extends Command
{
    protected $signature = 'token:generate-daily';
    protected $description = 'Generate AfricaTalking tokens for users daily at midnight';

    public function handle()
    {
        $users = User::all();

        foreach ($users as $user) {
            $response = $this->generateAfricaTalkingToken($user);

            if (isset($response['token'])) {
                $this->info("Token generated for User ID {$user->id}");
            } else {
                $this->error("Failed to generate token for User ID {$user->id}");
            }
        }
    }

    private function generateAfricaTalkingToken(User $user)
    {
        $apiKey = env('AFRICASTALKING_API_KEY'); 
        $username = env('AFRICASTALKING_USERNAME');
        $phoneNumber = config('services.africastalking.phone'); // e.g. +2547XXXXXXX
        


        if (!$apiKey || !$username) {
            return ['error' => 'Missing API credentials'];
        }

        $clientName = $user->client_name ?: 'client-' . uniqid();

        if (!$user->can_call && !$user->can_receive_calls) {
            return ['error' => 'User does not have permission to make or receive calls'];
        }

        $url = 'https://webrtc.africastalking.com/capability-token/request';

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'username'    => $username,
            'clientName'  => $clientName,
            'phoneNumber' => $phoneNumber,
            'incoming'    => $user->can_receive_calls,
            'outgoing'    => $user->can_call,
            'expire'      => 86400
        ]);

        if ($response->successful()) {
            $data = $response->json();
            $user->update(['token' => $data['token'], 'client_name' => $clientName]);
            return $data;
        }

        return ['error' => 'Failed to generate token', 'response' => $response->json()];
    }
}
