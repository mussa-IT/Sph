<?php

namespace App\Http\Controllers;

use App\Http\Requests\TaskStoreRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class TaskController extends Controller
{
    protected TaskService $taskService;

    public function __construct(TaskService $taskService)
    {
        $this->taskService = $taskService;
    }

    public function index(): View
    {
        $tasks = Auth::user()->tasks()->with('project')->latest()->paginate(20);

        return view('pages.tasks', compact('tasks'));
    }

    public function store(TaskStoreRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $this->taskService->createTask($project, [
            ...$validated,
            'status' => Task::STATUS_PENDING,
        ]);

        return back()->with('success', __('Task added successfully.'));
    }

    public function updateStatus(Request $request, Task $task): RedirectResponse
    {
        $this->authorize('update', $task->project);

        $validated = $request->validate([
            'status' => ['required', 'string', 'in:pending,in_progress,done'],
        ]);

        $this->taskService->updateTaskStatus($task, $validated);

        return back()->with('success', __('Task status updated.'));
    }

    public function destroy(Task $task): RedirectResponse
    {
        $this->authorize('delete', $task->project);

        $this->taskService->deleteTask($task);

        return back()->with('success', __('Task deleted successfully.'));
    }
}