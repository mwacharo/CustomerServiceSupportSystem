<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WhatsAppWebhookController extends Controller
{
    //


    public function receive(Request $request)
    {
        Log::info('Incoming WhatsApp Message', $request->all());

        // Optionally: Save to `messages` or `inbox` table
        return response()->json(['status' => 'received']);
    }
}
