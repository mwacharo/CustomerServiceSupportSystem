<?php

namespace App\Console\Commands;

use App\Services\CallFailureService;
use Illuminate\Console\Command;

class ProcessRecentFailedCallsCommand extends Command
{
    

    protected $signature = 'calls:process-failed';

    protected $description = 'Process failed calls and send WhatsApp messages';

    protected CallFailureService $callFailureService;

    public function __construct(CallFailureService $callFailureService)
    {
        parent::__construct();
        $this->callFailureService = $callFailureService;
    }

    public function handle(): int
    {
        $this->info('Processing failed calls...');
        
        $this->callFailureService->processRecentFailedCalls();

        $this->info('Done.');
        return Command::SUCCESS;
    }
}
