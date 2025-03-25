<?php

namespace App\Observers;

use App\Models\User;
use App\Models\Admin;
use Illuminate\Support\Facades\Log;

class UserObserver
{
    /**
     * Handle the User "created" event.
     */
    public function created(User $user): void
    {
        $this->syncAdminRole($user);
    }

    /**
     * Handle the User "updated" event.
     */
    public function updated(User $user): void
    {
        // Check if the role attribute was changed
        if ($user->isDirty('role') || $user->wasChanged('role')) {
            $this->syncAdminRole($user);
        }
    }

    /**
     * Handle the User "deleted" event.
     */
    public function deleted(User $user): void
    {
        // Remove admin record if user is deleted
        Admin::where('id', $user->id)->delete();
    }

    /**
     * Handle the User "restored" event.
     */
    public function restored(User $user): void
    {
        $this->syncAdminRole($user);
    }

    /**
     * Handle the User "force deleted" event.
     */
    public function forceDeleted(User $user): void
    {
        // Remove admin record if user is force deleted
        Admin::where('id', $user->id)->delete();
    }

    /**
     * Sync the admin role to the admins table
     */
    protected function syncAdminRole(User $user): void
    {
        try {
            if (strtolower($user->role) === 'admin') {
                // User is an admin, create record if it doesn't exist
                Admin::firstOrCreate(['id' => $user->id]);
                Log::info("User {$user->id} ({$user->name}) synced to admins table");
            } else {
                // User is not an admin, remove from admins table if exists
                Admin::where('id', $user->id)->delete();
                Log::info("User {$user->id} ({$user->name}) removed from admins table");
            }
        } catch (\Exception $e) {
            Log::error("Error syncing admin role for user {$user->id}", [
                'exception' => $e->getMessage(),
                'user' => $user->toArray()
            ]);
        }
    }
}
