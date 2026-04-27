<?php

namespace App\Services;

use App\Models\Project;
use App\Models\Task;

class TaskService
{
    public function createTask(Project $project, array $data): Task
    {
        return $project->tasks()->create($data);
    }

    public function updateTaskStatus(Task $task, array $data): bool
    {
        return $task->update($data);
    }

    public function deleteTask(Task $task): ?bool
    {
        return $task->delete();
    }
}
