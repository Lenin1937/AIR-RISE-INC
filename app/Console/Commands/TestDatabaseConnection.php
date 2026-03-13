<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Document;

class TestDatabaseConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:database';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test database connection and show sample data';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testing database connection...');
        
        try {
            $userCount = User::count();
            $this->info("Total users: {$userCount}");
            
            $adminUsers = User::whereHas('roles', function ($q) {
                $q->where('name', 'super-admin');
            })->get();
            
            $this->info("Admin users found: " . $adminUsers->count());
            foreach ($adminUsers as $admin) {
                $this->info("- {$admin->email}");
            }
            
            $clientUsers = User::whereHas('roles', function ($q) {
                $q->where('name', 'client');
            })->get();
            
            $this->info("Client users found: " . $clientUsers->count());
            foreach ($clientUsers->take(3) as $client) {
                $this->info("- {$client->email}");
            }
            
            $documentCount = Document::count();
            $this->info("Total documents: {$documentCount}");
            
        } catch (\Exception $e) {
            $this->error("Database error: " . $e->getMessage());
        }
        
        return Command::SUCCESS;
    }
}
