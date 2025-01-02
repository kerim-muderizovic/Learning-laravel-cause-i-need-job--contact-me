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
    if (!Auth::check()) {
        return response()->json(['error' => 'User not authenticated'], 401);
    }

    // Validate incoming request
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'description' => 'nullable|string',
        'progress' => 'integer|between:0,100|nullable',
        'due_date' => 'nullable|date',
        'priority' => 'string|nullable',
        'users' => 'array|nullable', // Array of user IDs
        'users.*' => 'exists:users,id', // Validate user IDs exist in the users table
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
            $task->users()->sync($validated['users']); // This syncs the users in the pivot table
        }

        return response()->json([
            'success' => true,
            'message' => 'Task created successfully',
            'task' => $task->load('users'), // Load users for the response
        ], 201);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error storing task',
            'error' => $e->getMessage(),
        ], 500);
    }
}


public function getUserTasks()
{
    try {
        // Get the authenticated user
        $user = Auth::user();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not authenticated',
            ], 401);
        }

        // Fetch tasks associated with the user
        $tasks = $user->tasks()->with('users')->get();

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'An error occurred',
            'error' => $e->getMessage(),
        ], 500);
    }
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
       
        // Fetch tasks associated with the authenticated user via the many-to-many relationship
        $tasks =Task::all(); // Include users in the tasks

        return response()->json([
            'success' => true,
            'tasks' => $tasks,
        ], 200);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Could not fetch tasks.',
        ], 500);
    }
        }

        public function destroy($id)
{
    try {
        // Check if user is authenticated
        $user = Auth::user();
        if (!$user) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        // Find the task by ID
        $task = Task::findOrFail($id);

        // Optionally, check if the user has permission to delete the task
        // Example: if tasks are associated with users
        // if ($task->user_id !== $user->id) {
        //     return response()->json(['error' => 'Unauthorized'], 403);
        // }

        // Delete the task
        $task->delete();

        return response()->json([
            'success' => true,
            'message' => 'Task deleted successfully',
        ], 200);
    } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Task not found',
        ], 404);
    } catch (\Exception $e) {
        // Log the error
        // \Log::error('Error deleting task: ' . $e->getMessage());

        return response()->json([
            'success' => false,
            'message' => 'An error occurred while trying to delete the task.',
            'error' => $e->getMessage(),
        ], 500);
    }
}

public function updateProgress(Request $request, $taskId)
    {
        // Validate the input (making sure progress is a number between 0 and 100)
        $validated = $request->validate([
            'progress' => 'required|integer|between:0,100',
        ]);

        // Find the task by ID
        $task = Task::find($taskId);

        if (!$task) {
            return response()->json(['message' => 'Task not found'], 404);
        }

        // Update the progress field
        $task->progress = $validated['progress'];
        $task->save(); // Save the updated task

        return response()->json(['task' => $task], 200);
    }
   

}
