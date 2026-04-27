<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamMemberResource extends JsonResource
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
            'role' => $this->role,
            'permissions' => $this->permissions,
            'joined_at' => $this->joined_at,
            'invited_at' => $this->invited_at,
            'accepted_at' => $this->accepted_at,
            'is_active' => $this->is_active,
            'user' => [
                'id' => $this->user->id,
                'name' => $this->user->name,
                'email' => $this->user->email,
            ],
            'team' => [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ],
        ];
    }
}
