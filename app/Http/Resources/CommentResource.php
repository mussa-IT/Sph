<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
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
            'content' => $this->content,
            'is_edited' => $this->is_edited,
            'edited_at' => $this->edited_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'project' => [
                'id' => $this->project->id,
                'title' => $this->project->title,
            ],
            'parent_id' => $this->parent_id,
            'mentions' => $this->when($this->mentions, $this->mentions),
            'replies_count' => $this->when($request->include('replies_count'), $this->replies()->count()),
            'replies' => CommentResource::collection($this->whenLoaded('replies')),
        ];
    }
}
