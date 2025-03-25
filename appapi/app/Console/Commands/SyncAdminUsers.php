<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SyncAdminUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'admin:sync';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Synchronize users with role=admin to the admins table';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting admin user synchronization...');
        
        try {
            // Get all users with role = 'admin'
            $adminUsers = User::where('role', 'admin')->get();
            
            $this->info("Found {$adminUsers->count()} users with admin role.");
            
            $count = 0;
            
            // For each admin user, ensure they have an entry in the admins table
            foreach ($adminUsers as $user) {
                $admin = Admin::find($user->id);
                
                if (!$admin) {
                    Admin::create([
                        'id' => $user->id,
                    ]);
                    $count++;
                    $this->info("Added user ID {$user->id} ({$user->name}) to admins table.");
                }
            }
            
            // Check for orphaned admin records
            $adminIds = Admin::pluck('id')->toArray();
            $validAdminUserIds = $adminUsers->pluck('id')->toArray();
            
            $orphanedIds = array_diff($adminIds, $validAdminUserIds);
            
            if (count($orphanedIds) > 0) {
                Admin::whereIn('id', $orphanedIds)->delete();
                $this->warn("Removed ".count($orphanedIds)." orphaned admin records.");
            }
            
            $this->info("Synchronization complete. Added $count new admin records.");
            
        } catch (\Exception $e) {
            $this->error("Error synchronizing admin users: {$e->getMessage()}");
            Log::error("Error in admin:sync command", [
                'exception' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return 1;
        }
        
        return 0;
    }
}
