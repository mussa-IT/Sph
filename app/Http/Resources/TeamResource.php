<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'avatar_url' => $this->avatar_url,
            'is_active' => $this->is_active,
            'trial_ends_at' => $this->trial_ends_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'owner' => [
                'id' => $this->owner->id,
                'name' => $this->owner->name,
                'email' => $this->owner->email,
            ],
            'members_count' => $this->when($request->include('members_count'), $this->members()->where('is_active', true)->count()),
            'projects_count' => $this->when($request->include('projects_count'), $this->projects()->count()),
            'pending_invitations_count' => $this->when($request->include('invitations_count'), $this->getPendingInvitationsCount()),
            'members' => TeamMemberResource::collection($this->whenLoaded('members')),
            'projects' => ProjectResource::collection($this->whenLoaded('projects')),
            'invitations' => TeamInvitationResource::collection($this->whenLoaded('invitations')),
        ];
    }
}
