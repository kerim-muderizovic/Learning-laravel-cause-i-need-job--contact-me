<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Services\UserActivityService;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    protected $userActivityService;

    public function __construct(UserActivityService $userActivityService) 
    {
        $this->userActivityService = $userActivityService;
    }

    public function updatePhoto(Request $request, $id)
    {
        $user = User::findOrFail($id);
        $user->url = $request->url;
        $user->save();

        // Log the activity
        $this->userActivityService->storeActivity($user->id, 'update_photo', request()->ip());

        return response()->json(['message' => 'URL updated successfully', 'user' => $user]);
    }

    public function updateName(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $user = User::findOrFail($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $oldName = $user->name;
        $user->name = $request->name;
        $user->save();

        // Log the activity
        $this->userActivityService->storeActivity($user->id, 'update_name', request()->ip());

        return response()->json([
            'message' => "Name updated from $oldName to {$user->name}",
            'user' => $user
        ], 200);
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user(); // Get the authenticated user

        try {
            $user->password = Hash::make($request->password); // Hash and update the password
            $user->save();

            // Log the activity
            $this->userActivityService->storeActivity($user->id, 'update_password', request()->ip());

            return response()->json(['message' => 'Password updated successfully!'], 200);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update password.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getAllUsers()
    {
        $users = User::all();

        // Log the activity (assuming only admins can fetch all users)
        $authUser = Auth::user();
        if ($authUser) {
            $this->userActivityService->storeActivity($authUser->id, 'view_all_users', request()->ip());
        }

        return response()->json($users, 200);
    }

    /**
     * Get user information for a specific user
     */
    public function getUserInfo($id)
    {
        try {
            $user = User::find($id);
            if (!$user) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            return response()->json([
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'profile_image' => $user->url ?? null,
                'is_online' => true // You can implement online status tracking later
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Error retrieving user information: ' . $e->getMessage()], 500);
        }
    }
}
