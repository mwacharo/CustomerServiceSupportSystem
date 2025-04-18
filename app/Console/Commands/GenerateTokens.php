<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class GenerateTokens extends Command
{
    // Command signature
    protected $signature = 'generate:tokens';

    // Command description
    protected $description = 'Generate Africa\'s Talking WebRTC tokens for all users based on permissions';

    public function handle()
    {
        // Log the start of the process
        Log::info('Starting token generation process for all users.');

        // Fetch all users from the database
        $users = User::all();

        // Loop through each user and generate a token
        foreach ($users as $user) {
            try {
                // Ensure a unique client name for the user
                if (empty($user->client_name)) {
                    $user->client_name = 'client_' . $user->id . '_' . substr(md5(uniqid()), 0, 6);
                    $user->save();
                }

                $clientName = str_replace(' ', '', $user->client_name); // Remove spaces

                // Determine incoming and outgoing permissions
                $incoming = $user->can_receive_calls ? 'true' : 'false';
                $outgoing = $user->can_call ? 'true' : 'false';

                // Log user details before sending token request
                Log::info('Generating token for user', [
                    'user_id' => $user->id,
                    'clientName' => $clientName
                ]);

                // Prepare the API payload
                $payload = [
                    'username'    => config('services.africastalking.username'),
                    'clientName'  => $clientName,
                    'phoneNumber' => config('services.africastalking.phone'),
                    'incoming'    => $incoming,
                    'outgoing'    => $outgoing,
                    'expire'      => "86400"
                ];

                // Africa's Talking API request URL
                $url = 'https://webrtc.africastalking.com/capability-token/request';

                // Send the request using Http facade
                $response = Http::withHeaders([
                    'apiKey' => config('services.africastalking.api_key'),
                    'Content-Type' => 'application/json',
                ])->post($url, $payload);

                // Check if the response was successful
                if ($response->successful()) {
                    $data = $response->json();

                    // Update the user's token in the database
                    $user->update(['token' => $data['token']]);

                    Log::info("Token successfully generated for User ID {$user->id}", [
                        'token' => $data['token']
                    ]);
                } else {
                    throw new Exception('Failed to generate token: ' . $response->body());
                }

            } catch (Exception $e) {
                // Log any exceptions or errors that occur
                Log::error("Token generation failed for User ID {$user->id}: " . $e->getMessage());
            }
        }

        // Log completion of token generation
        Log::info('Token generation process completed.');
    }
}
