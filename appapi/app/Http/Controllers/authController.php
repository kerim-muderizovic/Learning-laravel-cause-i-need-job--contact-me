<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    /**
     * Handle registration of a new user.
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);
        $user->sendEmailVerificationNotification();
        Auth::login($user);

        return response()->json(['message' => 'Registration successful'], 201);
    }

    /**
     * Handle login of an existing user.
     */public function login(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required|string',
    ]);

    // Attempt to authenticate the user
    if (!Auth::attempt($request->only('email', 'password'))) {
        return response()->json(['message' => 'Invalid login credentials.'], 401);
    }

    // Check the authenticated user's email_verified_at
    $user = Auth::user(); // Get the currently authenticated user

    if (is_null($user->email_verified_at)) {
        Auth::logout();
        return response()->json(['message' => 'Email not verified. Please check your inbox.'], 403);
    }

    // Return success response with user role
    return response()->json([
        'message' => 'Login successful!',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role,  // Assuming `role` is a column in the users table
        ]
    ]);
}


    /**
     * Handle logout.
     */
    public function logout(Request $request)
    {
        Auth::logout();
        return response()->json(['message' => 'Logout successful']);
    }
}
