<?php

namespace App\Http\Controllers;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Task;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Hash;
class UserController extends Controller
{
    public function updatePhoto(Request $request, $id)
{
    // $request->validate([
    //     'url' => 'required|url',
    // ]);

    $user = User::findOrFail($id);
    $user->url = $request->url;
    $user->save();

    return response()->json(['message' => 'URL updated successfully', 'user' => $user]);
}

public function updateName(Request $request, $id)
{
    $request->validate([
        'name' => 'required|string|max:255',
    ]);

    $user = User::findOrFail($id);
    if(!$user)
    {
        return response()->json(['message' => 'User not found'], 404);
    }
    $user->name = $request->name;
    $user->save();
    return response()->json(['message' => 'Name updated successfully', 'user' => $user], 200);
}

public function updatePassword(Request $request)
{
    $request->validate([
        'password' => 'required|string|min:8|confirmed',
    ]);

    $user = Auth::user() ;// Get the authenticated user

    try {
        $user->password = Hash::make($request->password); // Hash and update the password
        $user->save(); 

        return response()->json([
            'message' => 'Password updated successfully!',
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Failed to update password.',
            'error' => $e->getMessage(),
        ], 500);
    }
}
public function getAllUsers()
{
    // Retrieve all users from the database
    $users = User::all();

    // Return the users as a JSON response
    return response()->json($users, 200);
}
}
