<?php

namespace App\Http\Controllers;

use App\Http\Requests\ProjectStoreRequest;
use App\Models\Project;
use App\Models\Task;
use App\Services\ProjectService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;

class ProjectController extends Controller
{
    protected ProjectService $projectService;

    public function __construct(ProjectService $projectService)
    {
        $this->projectService = $projectService;
    }

    public function index(): View
    {
        $projects = $this->projectService->getUserProjects(Auth::user());

        return view('pages.projects', compact('projects'));
    }

    public function create(): View
    {
        return view('projects.create');
    }

    public function store(ProjectStoreRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $this->projectService->createProject(Auth::user(), $validated);

        return redirect()
            ->route('projects.index')
            ->with('success', __('Project created successfully.'));
    }

    public function show(Project $project): View
    {
        $this->authorize('view', $project);

        $project->load([
            'tasks' => function ($query) {
                $query
                    ->orderByRaw("CASE WHEN status = ? THEN 1 ELSE 0 END", [Task::STATUS_DONE])
                    ->orderByRaw('due_date IS NULL')
                    ->orderBy('due_date')
                    ->orderBy('id');
            },
            'budgets' => fn ($query) => $query->latest('id'),
        ])->loadCount([
            'tasks',
            'tasks as completed_tasks_count' => fn ($query) => $query->where('status', Task::STATUS_DONE),
        ]);

        return view('projects.show', compact('project'));
    }

    public function edit(Project $project): View
    {
        $this->authorize('update', $project);

        return view('projects.edit', compact('project'));
    }

    public function update(ProjectStoreRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $this->projectService->updateProject($project, $validated);

        return redirect()
            ->route('projects.show', $project)
            ->with('success', __('Project updated successfully.'));
    }

    public function destroy(Project $project): RedirectResponse
    {
        $this->authorize('delete', $project);

        $this->projectService->deleteProject($project);

        return redirect()
            ->route('projects.index')
            ->with('success', __('Project deleted successfully.'));
    }
}
