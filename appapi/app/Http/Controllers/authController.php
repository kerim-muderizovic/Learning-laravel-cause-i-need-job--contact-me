<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Mail\TwoFactorCodeMail;
use App\Models\Activity_log;
use App\Services\UserActivityService;
use Illuminate\Support\Facades\Mail;
use Psr\Http\Message\ResponseInterface;
use function Pest\Laravel\json;

class AuthController extends Controller
{
    /**
     * Handle registration of a new user.
     */
    protected $activity_log;
    public function __construct(UserActivityService $activity_log)
    {
        $this->activity_log = $activity_log;
    }
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
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        // Find the user by email
        $user = User::where('email', $request->email)->first();
        
        // Check if user exists and password is correct
        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Invalid login credentials.'], 401);
        }

        // Check the user's email_verified_at
        if (is_null($user->email_verified_at)) {
            return response()->json(['message' => 'Email not verified. Please check your inbox.'], 403);
        }
        
        // Generate and store 2FA code but don't fully authenticate yet
        // Store user ID in session to remember who is attempting to log in
        $request->session()->put('auth_user_id', $user->id);
        $request->session()->put('auth_requires_2fa', true);
        
        // Log the login attempt
        $this->activity_log->storeActivity($user->id, 'login_attempt', 'User attempting login, awaiting 2FA');
        
        // Generate and send 2FA code
        $key = 1111; // In production, use a random number generator
        $user->update([
            'two_factor_key' => $key,
            'two_factor_expires_at' => now()->addMinutes(10)
        ]);
        
        // Mail::to($user->email)->send(new TwoFactorCodeMail($key));

        // Return response indicating 2FA is required
        return response()->json([
            'message' => 'Please enter 2FA code sent to your email',
            'requires_2fa' => true,
            'user' => [
                'email' => $user->email,
                'requires_2fa' => true,
                'role' => $user->role,
            ],
            'isLoggedIn' => false,
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

    public function send_twofactor_key()
    {
        $user = Auth::user();

        if (!$user) {
            return 'not logged in at all';
        }
        $key=1111;
        $user->update([
            'two_factor_key'=>$key,
            'two_factor_expires_at'=> now()->addMinutes(10)
        ]);
        // Mail::to($user->email)->send(new TwoFactorCodeMail($key));
        return response()->json(['message' => '2FA code sent to your email']);
    }

    public function verify_twofactor(Request $request)
    {
        $request->validate([
            'two_factor_key' => 'required|numeric'
        ]);
        
        // Get the user ID from session
        $userId = $request->session()->get('auth_user_id');
        
        if (!$userId) {
            return response()->json(['message' => 'Authentication session expired. Please log in again.'], 401);
        }
        
        $user = User::find($userId);
        
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }
        
        // Verify the 2FA code
        if ($request->two_factor_key !== $user->two_factor_key) {
            return response()->json(['message' => 'Incorrect verification code'], 401);
        }
        
        // Check if code is expired
        if ($user->two_factor_expires_at < now()) {
            return response()->json(['message' => 'Verification code expired. Please request a new one.'], 401);
        }
        
        // Clear 2FA data
        $user->update([
            'two_factor_key' => null,
            'two_factor_expires_at' => null
        ]);
        
        // Now fully authenticate the user
        Auth::login($user);
        $request->session()->forget(['auth_user_id', 'auth_requires_2fa']);
        
        // Log successful login
        $this->activity_log->storeActivity($user->id, 'login', 'User completed 2FA and logged in');
        
        return response()->json([
            'message' => 'Authentication successful',
            'user' => [
                'id' => $user->id,
                'email' => $user->email,
                'role' => $user->role ?? 'User',
                'name' => $user->name ?? null,
                'profilePicture' => $user->url ?? null,
                'isLoggedIn' => true,
            ],
            'isLoggedIn' => true,
        ]);
    }
}
