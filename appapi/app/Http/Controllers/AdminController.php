<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\TaskUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use App\Models\Admin;

class AdminController extends Controller
{
    // Ensure only admin can access these routes
    public function __construct()
    {
       
    }

    // Delete a user
    public function deleteUser($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Delete the user
        $user->delete();

        return response()->json(['message' => 'User deleted successfully']);
    }

    // Delete a task
    public function deleteTask($id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        // Delete the task
        $task->delete();

        return response()->json(['message' => 'Task deleted successfully']);
    }

    // Edit a task
    public function editTask(Request $request, $id)
    {
        $task = Task::find($id);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        // Validate the input
        $validated = $request->validate([
            'title' => 'required|string',
            'description' => 'nullable|string',
            'progress' => 'nullable|integer|min:0|max:100',
            'priority' => 'nullable|string|in:low,medium,high',
            'due_date' => 'nullable|date',
            // Add more fields as needed
        ]);

        // Update the task
        $task->update($validated);

        return response()->json(['message' => 'Task updated successfully', 'task' => $task]);
    }

    // Edit a user
    public function editUser(Request $request, $id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Log the incoming request data
        Log::info('Edit user request data', [
            'request_data' => $request->all(),
            'user_id' => $id,
            'current_role' => $user->role
        ]);

        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            'role' => 'required|string|in:admin,user,manager',
            // Add more fields as needed (e.g., password, role)
        ]);

        // Log the validated data
        Log::info('Validated user data', [
            'validated_data' => $validated,
            'role' => $validated['role']
        ]);

        // Update the user
        $user->update($validated);

        // Verify the update was successful
        $updatedUser = User::find($id);
        Log::info('User after update', [
            'user' => $updatedUser,
            'updated_role' => $updatedUser->role
        ]);

        return response()->json([
            'message' => 'User updated successfully', 
            'user' => $updatedUser,
            'updated_role' => $updatedUser->role
        ]);
    }

    public function createTask(Request $request)
    {
        // Validate the input
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'assignedUsers' => 'required|array', // Array of user IDs
            'assignedUsers.*' => 'exists:users,id', // Ensure users exist in the users table
            'progress' => 'required|integer|between:0,100',
            'priority' => 'required|string|in:low,medium,high',
        ]);

        // Create the task
        $task = Task::create([
            'title' => $validated['title'],
            'description' => $validated['description'],
            'progress' => $validated['progress'],
            'priority' => $validated['priority'],
        ]);

        // Assign users to the task in the task_user table
        foreach ($validated['assignedUsers'] as $userId) {
            TaskUser::create([
                'task_id' => $task->id,
                'user_id' => $userId,
            ]);
        }

        return response()->json([
            'message' => 'Task created and users assigned successfully!',
            'task' => $task
        ], 201);
    }
      public function settings()
      {
        // Get the first admin record from the database (global settings)
        $admin = Admin::first();
        
        if (!$admin) {
            return response()->json(['error' => 'Admin settings not found'], 404);
        }
        
        return response()->json([
            'requireStrongPassword' => $admin->require_strong_password,
            'allow_creating_accounts' => $admin->allow_creating_accounts,
            'user_deletion_days' => $admin->user_deletion_days,
            'enable_audit_logs' => $admin->enable_audit_logs,
            'enable_reset_password' => $admin->enable_reset_password,
        ]);
    }

    public function applySettings(Request $request)
    {
        // Validate the incoming request
        $validatedData = $request->validate([
            'require_strong_password' => 'boolean',
            'allow_creating_accounts' => 'boolean',
            'user_deletion_days' => 'integer|min:0',
            'enable_audit_logs' => 'boolean',
            'enable_reset_password' => 'boolean',
        ]);

        // Find the admin record (assuming only one admin settings record exists)
        $admin = Admin::first(); // Using first() instead of find(1)

        if (!$admin) {
            return response()->json(['error' => 'Admin settings not found'], 404);
        }

        // Update the settings
        $admin->update($validatedData);

        return response()->json(['message' => 'Settings updated successfully', 'data' => $admin]);
    }
 
}
