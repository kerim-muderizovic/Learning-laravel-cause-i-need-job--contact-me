<?php
namespace App\Http\Controllers;

use App\Models\User;

use Illuminate\Http\Request;
use App\Models\Task;
use App\Services\UserActivityService;
use Illuminate\Support\Facades\Auth;

class TaskController extends Controller
{
    protected $userActivityService;
    public function __construct(UserActivityService $userActivityService)
    {
        $this->userActivityService = $userActivityService;
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
            'due_date' => $validated['due_date'] ?? null,
        ]);
        $this->userActivityService->storeActivity(Auth::id(), 'created_task', request()->ip());

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
        $tasks = Task::with('users')->get();
        return response()->json([
            'success' => true,
            'tasks' => $tasks,
            
        ]);
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
        $this->userActivityService->storeActivity($user->id, 'deleted_task', request()->ip());
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

    // Check if progress is 100 and update the completed status
    if ($task->progress == 100) {
        $task->completed = true;
    } else {
        $task->completed = false; // Optionally, set to false if progress is less than 100
    }
    $this->userActivityService->storeActivity(Auth::id(), 'updated_task_progress', request()->ip());
    $task->save(); // Save the updated task

    return response()->json(['task' => $task], 200);
}

public function getTaskProgresses()
{
    $user = Auth::user(); // Get the logged-in user

    // Count tasks in progress (not completed, progress > 0 but < 100)
    $inProgressTasks = Task::whereHas('users', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('progress', '>', 0)
      ->where('progress', '<', 100)
      ->where('completed', false)
      ->count();

    // Count completed tasks (progress == 100 and completed == true)
    $completedTasks = Task::whereHas('users', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('progress', 100)
      ->where('completed', true)
      ->count();

    // Count not started tasks (progress == 0)
    $notStartedTasks = Task::whereHas('users', function ($query) use ($user) {
        $query->where('user_id', $user->id);
    })->where('progress', 0)
      ->count();

    return response()->json([
        'in_progress_tasks' => $inProgressTasks,
        'completed_tasks' => $completedTasks,
        'not_started_tasks' => $notStartedTasks,
    ]);
}
   

}
