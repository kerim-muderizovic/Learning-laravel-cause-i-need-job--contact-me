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
 $this->activity_log->storeActivity(Auth::user()->id,'login','User logged in');
    $this->send_twofactor_key();

    // Return success response with user role
    return response()->json([
        'message' => 'Login successful!',
        'user' => [
            'id' => $user->id,
            'email' => $user->email,
            'role' => $user->role ?? 'User',
            'requires_2fa' => $user->requires_2fa ?? false,
            'name' => $user->name ?? null, // Add name field
            'profilePicture' => $user->url ?? null, // Add profilePicture field
        'isLoggedIn' => true,
        ],
        'isLoggedIn' => true,
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
        $user=auth::user();
             $request->validate([
                'two_factor_key'=>'required|numeric'
             ]);
             if($request->two_factor_key!==$user->two_factor_key)
                          { 
                            Auth::logout();
                            return response()->json(['message'=>'pogrean kod']);
}

            $user->update([
                'two_factor_key'=>null,
                'two_factor_expires_at'=>null
            ]);
            return response()->json(['message'=>'Yes','user'=>$user]);
          

    }

}
