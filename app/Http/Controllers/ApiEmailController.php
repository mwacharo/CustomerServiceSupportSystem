<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Contact;
use App\Models\EmailLog;
use App\Models\User;
use App\Models\Vendor;
use App\Services\DynamicMailerService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class ApiEmailController extends Controller
{


    public function send(Request $request)
{
    $request->validate([
        'subject' => 'required|string',
        'body' => 'required|string',
        'recipients' => 'required',
    ]);


    $recipients = is_string($request->recipients) ? json_decode($request->recipients, true) : $request->recipients;

    $attachments = [];

    // Get the sender (polymorphic credentialable: Company, Vendor, Client, or Contact)
    $sender = 
        // ?? Vendor::find($request->vendor_id)
        // ?? Client::find($request->client_id)
        // ?? Contact::find($request->contact_id)
        User::find($request->user_id);

    if (!$sender) {
        return response()->json(['error' => 'Sender not found'], 404);
    }

    // Handle attachments
    if ($request->hasFile('attachments')) {
        foreach ($request->file('attachments') as $file) {
            $path = $file->store('attachments', 'public');
            $attachments[storage_path("app/public/{$path}")] = $file->getClientOriginalName();
        }
    }

    try {
        // Use the DynamicMailerService based on polymorphic sender
        $mailer = new DynamicMailerService($sender);
        $mailer->sendMail([
            'to' => $recipients,
            'subject' => $request->subject,
            'body' => $request->body,
            'attachments' => $attachments,
        ]);

        Log::info('Sender Info:', [
            'class' => get_class($sender),
            'id' => $sender->id ?? null,
            'name' => $sender->name ?? null,
            'email' => $sender->email ?? null,
        ]);
        

        // Log the email
        EmailLog::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'recipients' => $recipients,
            'has_attachments' => !empty($attachments),
            'attachments' => array_values($attachments),
            'status' => 'sent',
            'emailable_id' => $sender->id,
            'emailable_type' => get_class($sender),
        ]);

        return response()->json(['status' => 'success', 'message' => 'Email sent successfully.']);

    } catch (\Exception $e) {


        Log::info('Sender Info:', [
            'class' => get_class($sender),
            'id' => $sender->id ?? null,
            'name' => $sender->name ?? null,
            'email' => $sender->email ?? null,
        ]);
        
        // Log failed email
        EmailLog::create([
            'subject' => $request->subject,
            'body' => $request->body,
            'recipients' => $recipients,
            'has_attachments' => !empty($attachments),
            'attachments' => array_values($attachments),
            'status' => 'failed',
            'error_message' => $e->getMessage(),
            'emailable_id' => $sender->id,
            'emailable_type' => get_class($sender),
        ]);

        return response()->json([
            'status' => 'error',
            'message' => 'Failed to send email.',
            'error' => $e->getMessage()
        ], 500);
    }

}



}
