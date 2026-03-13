<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Http\Controllers\NotificationController;

class SendDocumentExpirationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'documents:check-expiration';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send notifications for expiring and expired documents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for expiring documents...');
        
        NotificationController::sendExpirationReminders();
        
        $this->info('Document expiration check completed.');
        
        return Command::SUCCESS;
    }
}