<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AIResponderService;

class TinkerAIResponderCommand extends Command
{
    protected $signature = 'tinker:ai-responder 
                            {--message= : The customer query to test}';

    protected $description = 'Test the AIResponderService with a sample message';

    public function handle()
    {
        $message = $this->option('message');

        if (!$message) {
            $message = $this->ask('What message do you want to test?');
        }

        $this->info('Sending message to AIResponderService...');
        $response = app(AIResponderService::class)->interpretCustomerQuery($message);

        if ($response) {
            $this->line("\nResponse:");
            $this->comment($response);
        } else {
            $this->error('Failed to get a response. Check logs for details.');
        }

        return 0;
    }
}
