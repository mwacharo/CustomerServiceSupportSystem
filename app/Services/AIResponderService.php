<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class AIResponderService
{

    /**
     * Interpret a customer query using OpenAI's API.
     *
     * @param string $message The customer query to interpret.
     * @return string|null The interpreted response or null on failure.
     */
    public function interpretCustomerQuery(string $message): ?string
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                // 'model' => 'gpt-4', // or 'gpt-3.5-turbo'
                'model' => 'gpt-3.5-turbo',

                'messages' => [
                    ['role' => 'system', 'content' => 'You are a customer support assistant for a courier company.'],
                    ['role' => 'user', 'content' => $message],
                ],
                'temperature' => 0.2,
            ]);

            if ($response->successful()) {
                return $response->json()['choices'][0]['message']['content'];
            }

            Log::error('OpenAI response failed', ['response' => $response->body()]);
            return null;
        } catch (\Exception $e) {
            Log::error('AIResponderService exception', ['message' => $e->getMessage()]);
            return null;
        }
    }


}
