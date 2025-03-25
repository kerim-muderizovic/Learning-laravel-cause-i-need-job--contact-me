<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Services\UserActivityService;

class PasswordResetController extends Controller
{
    protected $activityLog;

    public function __construct(UserActivityService $activityLog)
    {
        $this->activityLog = $activityLog;
    }

    /**
     * Send a reset link to the given user
     */
    public function sendResetLink(Request $request)
    {
        // Check if password reset is enabled in admin settings
        $adminSettings = $request->get('admin_settings');
        if ($adminSettings && !$adminSettings->enable_reset_password) {
            return response()->json(['message' => 'Password reset is currently disabled by administrator'], 403);
        }

        $request->validate(['email' => 'required|email']);

        // Check if user exists
        $user = User::where('email', $request->email)->first();
        if ($user) {
            // Log the reset attempt
            $this->activityLog->storeActivity($user->id, 'password_reset_requested', 'Password reset requested');
        }

        $status = Password::sendResetLink(
            $request->only('email')
        );

        return $status === Password::RESET_LINK_SENT
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }

    /**
     * Reset the user's password
     */
    public function resetPassword(Request $request)
    {
        // Check if password reset is enabled in admin settings
        $adminSettings = $request->get('admin_settings');
        if ($adminSettings && !$adminSettings->enable_reset_password) {
            return response()->json(['message' => 'Password reset is currently disabled by administrator'], 403);
        }

        // Determine password validation rules based on admin settings
        $passwordRule = 'required|confirmed';
        if ($adminSettings && $adminSettings->require_strong_password) {
            // Add strong password requirements
            $passwordRule .= '|regex:/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]{8,}$/';
        }

        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => $passwordRule,
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
                
                // Log the successful password reset
                $this->activityLog->storeActivity($user->id, 'password_reset_completed', 'Password reset completed');
            }
        );

        return $status === Password::PASSWORD_RESET
            ? response()->json(['message' => __($status)])
            : response()->json(['message' => __($status)], 400);
    }
}
