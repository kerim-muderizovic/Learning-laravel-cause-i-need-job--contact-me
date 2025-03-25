<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Admin;
use App\Models\User;
use Illuminate\Support\Facades\Log;

class SetupAdminSettings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:setup-settings {--reset : Reset settings to default values}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set up or reset the default admin settings';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Setting up admin settings...');
        
        try {
            // Find any existing admin settings
            $adminSettings = Admin::first();
            
            // If we're resetting or no settings exist, create new settings
            $shouldReset = $this->option('reset') || !$adminSettings;
            
            if ($shouldReset) {
                // Default values
                $defaultSettings = [
                    'require_strong_password' => true,
                    'allow_creating_accounts' => true,
                    'user_deletion_days' => 30,
                    'enable_audit_logs' => true,
                    'enable_reset_password' => true,
                ];
                
                // Find admin user to associate with settings
                $adminUser = User::where('role', 'admin')->first();
                
                if (!$adminUser) {
                    $this->error('No admin user found. Please create an admin user first.');
                    return 1;
                }
                
                // Create or update settings
                if ($adminSettings) {
                    $adminSettings->update($defaultSettings);
                    $this->info('Admin settings have been reset to defaults.');
                } else {
                    $defaultSettings['id'] = $adminUser->id;
                    Admin::create($defaultSettings);
                    $this->info('New admin settings created with default values.');
                }
                
                // Show the current settings
                $this->displaySettings();
                
                return 0;
            } else {
                $this->info('Admin settings already exist. Use --reset to reset to defaults.');
                $this->displaySettings();
                return 0;
            }
        } catch (\Exception $e) {
            $this->error("Error setting up admin settings: {$e->getMessage()}");
            Log::error("Error in admin:setup-settings command", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
    }
    
    /**
     * Display the current admin settings
     */
    private function displaySettings()
    {
        $settings = Admin::first();
        
        if (!$settings) {
            $this->warn('No admin settings found.');
            return;
        }
        
        $this->info('Current Admin Settings:');
        $this->table(
            ['Setting', 'Value'],
            [
                ['require_strong_password', $settings->require_strong_password ? 'Yes' : 'No'],
                ['allow_creating_accounts', $settings->allow_creating_accounts ? 'Yes' : 'No'],
                ['user_deletion_days', $settings->user_deletion_days],
                ['enable_audit_logs', $settings->enable_audit_logs ? 'Yes' : 'No'],
                ['enable_reset_password', $settings->enable_reset_password ? 'Yes' : 'No'],
            ]
        );
    }
}
