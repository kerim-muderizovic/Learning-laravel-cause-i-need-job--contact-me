<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Task;
use Illuminate\Http\Request;
use App\Models\TaskUser;

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

        // Validate the input
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|email|unique:users,email,' . $id,
            // Add more fields as needed (e.g., password, role)
        ]);

        // Update the user
        $user->update($validated);

        return response()->json(['message' => 'User updated successfully', 'user' => $user]);
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
}
