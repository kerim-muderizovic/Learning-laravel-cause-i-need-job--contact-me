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
        // Commenting out default broadcasting routes to use our custom one
        // Broadcast::routes(['middleware' => ['web']]);
        
        require base_path('routes/channels.php');
    }
} 