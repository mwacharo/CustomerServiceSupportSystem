<?php

namespace App\Http\Controllers;

use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

use App\Services\AIResponderService;
use App\Models\User;


use App\Services\WhatsAppMessageService;

use App\Services\WhatsApp\Handlers\GroupHandler;
use App\Services\WhatsApp\Handlers\CallHandler;
use App\Services\WhatsApp\Handlers\MessageHandler;

class WhatsAppWebhookController extends Controller
{





    protected $handlers;

    public function __construct()
    {
        $this->handlers = [
            'message' => app(MessageHandler::class),
            'message_create' => app(MessageHandler::class),
            'message_ack' => app(MessageHandler::class),

            // 'group_join' => app(GroupHandler::class),
            // 'group_leave' => app(GroupHandler::class),
            // 'group_update' => app(GroupHandler::class),
            // 'call' => app(CallHandler::class),
            // Add other event handlers here...
        ];
    }

    public function receive(Request $request)
    {
        $event = $request->input('event');

        Log::info("Incoming WhatsApp Event", [
            'event' => $event,
            'payload' => $request->all(),
            'headers' => $request->headers->all(),
            'ip' => $request->ip(),
        ]);

        if (!isset($this->handlers[$event])) {
            Log::info("No handler for event: $event", [
                'event' => $event,
                'available_handlers' => array_keys($this->handlers),
            ]);
            return response()->json(['status' => "Ignored event type: $event"], 200);
        }

        try {
            $handler = $this->handlers[$event];
            Log::info("Handler found for event: $event", ['handler' => get_class($handler)]);

            $response = $handler->handle($request);

            Log::info("Event handled successfully", [
                'event' => $event,
                'response' => $response,
            ]);

            return response()->json(['status' => 'Handled', 'details' => $response], 200);
        } catch (\Exception $e) {
            Log::error("Error handling $event", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'payload' => $request->all(),
                'event' => $event,
            ]);
            return response()->json(['error' => "Failed to handle event"], 500);
        }
    }



}
