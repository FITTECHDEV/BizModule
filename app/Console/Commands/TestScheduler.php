<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TestScheduler extends Command
{
    protected $signature = 'scheduler:test';
    protected $description = 'Test that the Laravel scheduler is running';

    public function handle()
    {
        Log::info('✅ Scheduler is running - ' . now());
        $this->info('Scheduler is running - ' . now());
    }
}
