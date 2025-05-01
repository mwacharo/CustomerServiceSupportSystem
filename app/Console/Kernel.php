<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

use App\Jobs\DownloadCallRecordingsJob;
use Illuminate\Support\Facades\Log;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        // Log the start of the token generation scheduling
        // Log::info('🔑 Scheduling daily token generation command at midnight.');

        // Schedule the token generation command at midnight daily
        $schedule->command('generate:tokens')->dailyAt('23:00');
    

        // Log::info('📥 Starting Call Recordings Download Job');

        $schedule->job(new DownloadCallRecordingsJob)->everytenMinutes();


    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
