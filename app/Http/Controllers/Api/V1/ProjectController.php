<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProjectResource;
use App\Models\Project;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;

class ProjectController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        $projects = $user->projects()
            ->with(['user', 'team', 'tasks', 'budgets'])
            ->latest()
            ->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => ProjectResource::collection($projects->items()),
            'meta' => [
                'current_page' => $projects->currentPage(),
                'per_page' => $projects->perPage(),
                'total' => $projects->total(),
                'last_page' => $projects->lastPage(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'visibility' => ['required', 'in:public,private'],
            'team_id' => ['nullable', 'exists:teams,id'],
        ]);

        $project = Project::create([
            'title' => $request->title,
            'description' => $request->description,
            'visibility' => $request->visibility,
            'team_id' => $request->team_id,
            'user_id' => $request->user()->id,
        ]);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project->load(['user', 'team'])),
            'message' => 'Project created successfully.',
        ], Response::HTTP_CREATED);
    }

    public function show(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanView($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $project->load(['user', 'team', 'tasks', 'budgets', 'comments']);

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project),
        ]);
    }

    public function update(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanEdit($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', 'in:planning,in_progress,completed,archived'],
            'visibility' => ['required', 'in:public,private'],
            'progress' => ['nullable', 'integer', 'min:0', 'max:100'],
        ]);

        $project->update($request->only([
            'title',
            'description',
            'status',
            'visibility',
            'progress',
        ]));

        return response()->json([
            'success' => true,
            'data' => new ProjectResource($project->load(['user', 'team'])),
            'message' => 'Project updated successfully.',
        ]);
    }

    public function destroy(Request $request, Project $project): JsonResponse
    {
        if (!$project->userCanDelete($request->user())) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied.',
            ], Response::HTTP_FORBIDDEN);
        }

        $project->delete();

        return response()->json([
            'success' => true,
            'message' => 'Project deleted successfully.',
        ]);
    }
}
