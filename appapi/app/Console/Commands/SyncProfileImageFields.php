<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;

class SyncProfileImageFields extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:sync-profile-image-fields';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync profile_image field with existing url field for all users';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting to sync profile_image fields with url fields...');
        
        $users = User::whereNotNull('url')->get();
        $count = 0;
        
        foreach ($users as $user) {
            $user->profile_image = $user->url;
            $user->save();
            $count++;
        }
        
        $this->info("Completed! Updated {$count} users.");
    }
}
