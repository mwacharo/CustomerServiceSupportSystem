<?php

namespace App\Jobs;

use App\Models\CallHistory;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DownloadCallRecordingsJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        Log::info('📥 Starting Call Recordings Download Job');

        // Fetch all call histories with a valid recording URL
        // $callHistories = CallHistory::whereNotNull('recordingUrl')->where('download_status', '!=', 'downloaded')->get();



        $threeDaysAgo = Carbon::now()->subDays(3);

$callHistories = CallHistory::whereNotNull('recordingUrl')
    ->where('download_status', '!=', 'downloaded')
    ->where('created_at', '>=', $threeDaysAgo)
    ->get();

        foreach ($callHistories as $call) {
            try {
                $recordingUrl = $call->recordingUrl;
                $fileName = "call_{$call->id}.mp3";
                $filePath = "call_recordings/{$fileName}";

                // Download the recording
                $response = Http::get($recordingUrl);

                if ($response->successful()) {
                    Storage::disk('public')->put($filePath, $response->body());

                    // Update call history record
                    $call->update([
                        'download_status' => 'downloaded',
                        'recordingUrl' => asset("storage/{$filePath}"),
                    ]);

                    Log::info("✅ Successfully downloaded call recording: {$fileName}");
                } else {
                    Log::error("❌ Failed to download recording for call ID: {$call->id}");
                }
            } catch (\Exception $e) {
                Log::error("🚨 Error downloading recording for call ID: {$call->id} - " . $e->getMessage());
            }
        }

        Log::info('📥 Call Recordings Download Job Completed');
    }
}
