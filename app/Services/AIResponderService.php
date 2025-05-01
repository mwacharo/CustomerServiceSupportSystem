<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;



class AIResponderService
{
    public function interpretCustomerQuery(string $message): ?string
    {
        try {
        
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('                            '),
                'HTTP-Referer' => 'your-app.com', // required by OpenRouter
            ])->post('https://openrouter.ai/api/v1/chat/completions', [
                'model' => 'mistralai/mistral-7b-instruct', // or 'openai/gpt-3.5-turbo'
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
