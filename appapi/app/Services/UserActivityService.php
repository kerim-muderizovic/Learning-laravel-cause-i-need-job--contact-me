<?php

namespace App\Services;

use App\Models\Activity_log;
use App\Models\UserActivity;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Request;

class UserActivityService
{
    /**
     * Store a new activity log for the user.
     *
     * @param int $userId
     * @param string $activity
     * @param string $ipAddress
     * @return UserActivity
     */
    public function __construct() 
{
}

    public function storeActivity(int $userId, string $activity, string $ipAddress)
    {

        $ipAddress=Request::ip();
        // Logic for storing user activity
        return Activity_log::create([
            'user_id' => $userId,
            'activity' => $activity,
            'ip_address' => $ipAddress
        ]);
    }

    // You can add more methods for different service logic
}
