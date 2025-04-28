<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Controllers\WebhookController; // Adjust if your controller is named differently
use App\Http\Controllers\WhatsAppWebhookController;

class TestWhatsAppWebhook extends Command
{
    protected $signature = 'test:whatsapp-webhook';

    protected $description = 'Simulate a WhatsApp webhook POST request to test receiving logic';

    public function handle()
    {
        $this->info('Simulating WhatsApp webhook...');

        // Build fake payload
        $fakePayload = [
            'data' => [
                'message' => [
                    'from' => '254712345678@c.us',
                    'to' => '254798765432@c.us',
                    'body' => 'Hello from Artisan Command!',
                    'timestamp' => time(),
                    'type' => 'chat',
                    'fromMe' => false,
                    'id' => [
                        'id' => uniqid()
                    ],
                    '_data' => [
                        'notifyName' => 'Test User',
                        'parentMsgId' => null,
                    ],
                ],
                'media' => null,
            ],
        ];

        $this->info('Sending fake payload...');
        logger()->info('Fake WhatsApp payload:', $fakePayload);

        // Create a fake request
        $request = Request::create('/webhook/whatsapp', 'POST', [], [], [], [], json_encode($fakePayload));
        $request->headers->set('Content-Type', 'application/json');

        // Call your controller method manually
        $controller = new WhatsAppWebhookController();
        $response = $controller->receive($request);

        $this->info('Response: ' . json_encode($response->getData()));

        return 0;
    }
}
