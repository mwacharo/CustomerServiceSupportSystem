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
        Log::info('Starting token generation process.');

        $users = User::all();

        foreach ($users as $user) {
            Log::info("Processing User ID {$user->id}");

            $response = $this->generateAfricaTalkingToken($user);

            if (isset($response['token'])) {
                $this->info("Token generated for User ID {$user->id}");
                Log::info("Token successfully generated for User ID {$user->id}", ['token' => $response['token']]);
            } else {
                $this->error("Failed to generate token for User ID {$user->id}");
                Log::error("Failed to generate token for User ID {$user->id}", ['error' => $response['error'] ?? 'Unknown error']);
            }
        }

        Log::info('Token generation process completed.');
    }

    private function generateAfricaTalkingToken(User $user)
    {
        // $apiKey = env('AFRICASTALKING_API_KEY'); 
        // $username = env('AFRICASTALKING_USERNAME');
        $apiKey    = config('services.africastalking.api_key');
        $username  = config('services.africastalking.username');
        $phoneNumber = config('services.africastalking.phone'); // e.g. +2547XXXXXXX

        Log::debug('API credentials and phone number fetched.', [
            'apiKey' => $apiKey ? 'Provided' : 'Missing',
            'username' => $username ? 'Provided' : 'Missing',
            'phoneNumber' => $phoneNumber
        ]);

        if (!$apiKey || !$username) {
            return ['error' => 'Missing API credentials'];
        }

        $clientName = $user->client_name ?: 'client-' . uniqid();

        // log user permissions 
        //  if (!$user->can_call && !$user->can_receive_calls) {


        if (!$user->can_call && !$user->can_receive_calls) {
            $roles = $user->getRoleNames()->implode(', ');
            $permissions = $user->getAllPermissions()->pluck('name')->implode(', ');
            Log::warning("User ID {$user->id} does not have permission to make or receive calls.");
            Log::warning("🚫 Call Access Denied", [
                'user_id' => $user->id,
                'name' => $user->name,
                'can_call' => $user->can_call,
                'can_receive_calls' => $user->can_receive_calls,
                'roles' => $roles,
                'permissions' => $permissions,
                'reason' => 'User does not have permission to make or receive calls',
            ]);
        
            return ['error' => 'User does not have permission to make or receive calls'];
        }

        $url = 'https://webrtc.africastalking.com/capability-token/request';

        Log::info("Sending token generation request for User ID {$user->id}", [
            'url' => $url,
            'username' => $username,
            'clientName' => $clientName,
            'phoneNumber' => $phoneNumber,
            // 'incoming' => $user->can_receive_calls,
            // 'outgoing' => $user->can_call,
            'can_call' => $user->hasPermissionTo('can_call'),
            'can_receive_calls' => $user->hasPermissionTo('can_receive_calls'),
            
            'expire' => 86400
        ]);

        $response = Http::withHeaders([
            'apiKey' => $apiKey,
            'Content-Type' => 'application/json'
        ])->post($url, [
            'username'    => $username,
            'clientName'  => $clientName,
            'phoneNumber' => $phoneNumber,
            // 'incoming'    => $user->can_receive_calls,
            // 'outgoing'    => $user->can_call,
            'can_call' => $user->hasPermissionTo('can_call'),
            'can_receive_calls' => $user->hasPermissionTo('can_receive_calls'),
            'expire'      => 86400
        ]);

        if ($response->successful()) {
            $data = $response->json();
            Log::info("Token generation successful for User ID {$user->id}", ['response' => $data]);
            $user->update(['token' => $data['token'], 'client_name' => $clientName]);
            return $data;
        }

        $errorResponse = $response->json();
        Log::error("Token generation failed for User ID {$user->id}", ['response' => $errorResponse]);
        return ['error' => 'Failed to generate token', 'response' => $errorResponse];
    }
}
