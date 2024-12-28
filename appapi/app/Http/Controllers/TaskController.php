<?php
namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    // Applying the auth middleware to the controller or just the store method
    public function __construct()
    {
        // $this->middleware('auth')->only('store'); // This ensures only authenticated users can store tasks
    }

    public function store(Request $request)
    {
        // Check if user is authenticated (optional since middleware might already handle this)
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }
    
        // Validate the incoming request
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'progress' => 'integer|between:0,100|nullable',
            'due_date' => 'nullable|date',
            'priority' => 'string|nullable',
            'users' => 'array|nullable', // Array of user IDs
            'users.*' => 'exists:users,id', // Each user ID must exist in the users table
        ]);
    
        try {
            // Create the task
            $task = Task::create([
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'progress' => $validated['progress'] ?? 0,
                'priority' => $validated['priority'] ?? null,
            ]);
            // Attach users to the task if provided
            if (!empty($validated['users'])) {
                $task->users()->sync($validated['users']); // Sync ensures correct many-to-many relationships
            }
    
            return response()->json([
                'success' => true,
                'message' => 'Task created successfully',
                'task' => $task->load('users'), // Include related users for the response
            ], 201);
        } catch (\Exception $e) {
            \Log::error('Error storing task: ' . $e->getMessage());
    
            return response()->json([
                'success' => false,
                'message' => 'Error storing task',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function getUserTasks($userId)
    {
        $user = User::findOrFail($userId);

        // Retrieve the user's tasks
        $tasks = $user->tasks()->with('users')->get();

        return response()->json([
            'user_id' => $userId,
            'tasks' => $tasks,
        ]);
    }
    public function getAssignableUsers()
    {
        $users = User::where('role', '!=', 'admin')->get();
        return response()->json($users);
    }
    public function getAllTasks()
{
    try {
        // Get the authenticated user
        $user = auth()->user();

        // Fetch tasks associated with the authenticated user via the many-to-many relationship
        $tasks = $user->tasks()->with('users')->get(); // Include users in the tasks

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ], 200);
    } catch (\Exception $e) {
        // Log the error for debugging
        \Log::error('Error fetching tasks for user: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'Could not fetch tasks.',
        ], 500);
    }
}
}
