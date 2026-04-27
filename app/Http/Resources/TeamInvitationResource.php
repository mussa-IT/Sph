<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TeamInvitationResource extends JsonResource
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
            'email' => $this->email,
            'role' => $this->role,
            'status' => $this->status,
            'token' => $this->token,
            'expires_at' => $this->expires_at,
            'accepted_at' => $this->accepted_at,
            'declined_at' => $this->declined_at,
            'message' => $this->message,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'team' => [
                'id' => $this->team->id,
                'name' => $this->team->name,
            ],
            'invited_by' => [
                'id' => $this->invitedBy->id,
                'name' => $this->invitedBy->name,
                'email' => $this->invitedBy->email,
            ],
        ];
    }
}
