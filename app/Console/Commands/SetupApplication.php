<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupApplication extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:setup';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up the application by running migrations and seeders';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up the application...');

        // Run migrations
        $this->info('Running migrations...');
        Artisan::call('migrate', ['--force' => true]);
        $this->info(Artisan::output());

        // Run seeders
        $this->info('Running seeders...');
        Artisan::call('db:seed', ['--force' => true]);
        $this->info(Artisan::output());

        // Clear cache
        $this->info('Clearing cache...');
        Artisan::call('cache:clear');
        Artisan::call('config:clear');
        Artisan::call('route:clear');
        Artisan::call('view:clear');
        $this->info('Cache cleared.');

        // Generate application key if not already set
        if (empty(config('app.key'))) {
            $this->info('Generating application key...');
            Artisan::call('key:generate');
            $this->info('Application key generated.');
        }

        // Optimize the application
        $this->info('Optimizing the application...');
        Artisan::call('optimize');
        $this->info('Application optimized.');

        $this->info('Application setup complete!');
        $this->info('You can now log in with the following credentials:');
        $this->info('Email: admin@example.com');
        $this->info('Password: password');
        
        return Command::SUCCESS;
    }
} 