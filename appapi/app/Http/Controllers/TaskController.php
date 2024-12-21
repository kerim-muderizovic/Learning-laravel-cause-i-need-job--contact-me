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
        $this->middleware('auth')->only('store'); // This ensures only authenticated users can store tasks
    }

    public function store(Request $request)
    {
        // Check if user is authenticated (optional, since middleware already handles this)
        if (!Auth::check()) {
            return response()->json(['error' => 'User not authenticated'], 401);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'progress' => 'integer|between:0,100',
            'due_date' => 'nullable|date',
            'priority' => 'in:low,medium,high',
            'users' => 'array', // Array of user IDs
            'users.*' => 'exists:users,id'
        ]);

        // Proceed with task creation
        $task = Task::create($validated);

        // Attach users if provided
        if (!empty($validated['users'])) {
            $task->users()->sync($validated['users']);
        }

        return response()->json($task, 201);
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
}
