<?php

namespace App\Providers;

use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\ServiceProvider;

class BroadcastServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use a more flexible authentication approach for broadcasting
        Broadcast::routes(['middleware' => ['web']]);
        
        require base_path('routes/channels.php');
    }
} 