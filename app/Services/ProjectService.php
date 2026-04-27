<?php

namespace App\Services;

use App\Models\Project;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class ProjectService
{
    public function getUserProjects(Authenticatable $user, int $perPage = 15): LengthAwarePaginator
    {
        return $user->projects()->latest()->paginate($perPage);
    }

    public function createProject(Authenticatable $user, array $data): Project
    {
        return $user->projects()->create($data);
    }

    public function updateProject(Project $project, array $data): bool
    {
        return $project->update($data);
    }

    public function deleteProject(Project $project): ?bool
    {
        return $project->delete();
    }
}
