<?php

namespace App\Http\Controllers;

use App\Models\Activity_log;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Task;
use function Pest\Laravel\json;

class ActivityLogs extends Controller
{
    //
    public function GetAllActivityLogs(Request $request)
    {
        if (!Auth::check()) {
            return response()->json(["error" => "Unauthorized"], 401);
        }
    
        $activityLogs = Activity_log::with('user')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'activityLogs' => $activityLogs
        ]);
    }
    
    
    
}
