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

    public function interpretCustomerQuery(string $message, array $recentOrders = []): ?string
    {
        $orderDetails = '';
        if (!empty($recentOrders)) {
            $orderDetails .= "Here are the customer's recent orders:\n";
            foreach ($recentOrders as $order) {
                $orderDetails .= "- Order #{$order['order_number']}: {$order['status']} (Delivery: {$order['delivery_date']})\n";
            }
            $orderDetails .= "\n";
        }

        $messageWithContext = $orderDetails . $message;

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
                'Content-Type' => 'application/json',
            ])->post('https://api.openai.com/v1/chat/completions', [
                'model' => 'gpt-3.5-turbo',
                'messages' => [
                    ['role' => 'system', 'content' => 'You are a customer support assistant for a courier company.'],
                    ['role' => 'user', 'content' => $messageWithContext],
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

    // public function interpretCustomerQuery(string $message): ?string
    // {
    //     try {
    //         $response = Http::withHeaders([
    //             'Authorization' => 'Bearer ' . env('OPENAI_API_KEY'),
    //             'Content-Type' => 'application/json',
    //         ])->post('https://api.openai.com/v1/chat/completions', [
    //             // 'model' => 'gpt-4', // or 'gpt-3.5-turbo'
    //             'model' => 'gpt-3.5-turbo',

    //             'messages' => [
    //                 ['role' => 'system', 'content' => 'You are a customer support assistant for a courier company.'],
    //                 ['role' => 'user', 'content' => $message],
    //             ],
    //             'temperature' => 0.2,
    //         ]);

    //         if ($response->successful()) {
    //             return $response->json()['choices'][0]['message']['content'];
    //         }

    //         Log::error('OpenAI response failed', ['response' => $response->body()]);
    //         return null;
    //     } catch (\Exception $e) {
    //         Log::error('AIResponderService exception', ['message' => $e->getMessage()]);
    //         return null;
    //     }
    // }




/**
     * Compose a message with context (like order summary) and get a helpful response.
     *
     * @param array $context
     * @return string|null
     */
    public function respondToMessageWithContext(array $context): ?string
    {
        $prompt = "A customer wrote: \"{$context['message']}\"\n\n";

        if (!empty($context['order_summary'])) {
            $prompt .= "Here are the customer's order details:\n";
            foreach ($context['order_summary'] as $order) {
                $prompt .= "- Order #{$order['order_number']}: {$order['status']} (Delivery: {$order['delivery_date']})\n";
            }
        } else {
            $prompt .= "No matching order was found for their query.\n";
        }

        $prompt .= "\n\nRespond politely and helpfully to the customer.";

        return $this->interpretCustomerQuery($prompt);
    }


}
