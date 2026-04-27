<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProjectResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'status' => $this->status,
            'visibility' => $this->visibility,
            'progress' => $this->progress,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'team' => $this->when($this->team_id, [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ]),
            'tasks_count' => $this->when($request->include('tasks_count'), $this->tasks()->count()),
            'comments_count' => $this->when($request->include('comments_count'), $this->comments()->count()),
            'budgets' => BudgetResource::collection($this->whenLoaded('budgets')),
            'tasks' => TaskResource::collection($this->whenLoaded('tasks')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
