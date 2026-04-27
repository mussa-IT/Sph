<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
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
            'email' => $this->email,
            'location' => $this->location,
            'website' => $this->website,
            'bio' => $this->bio,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'subscription' => $this->when($request->include('subscription'), function () {
                $subscription = $this->getCurrentSubscription();
                return $subscription ? [
                    'id' => $subscription->id,
                    'status' => $subscription->status,
                    'plan' => [
                        'id' => $subscription->plan->id,
                        'name' => $subscription->plan->name,
                        'slug' => $subscription->plan->slug,
                    ],
                    'billing_cycle' => $subscription->billing_cycle,
                    'price' => $subscription->price,
                    'currency' => $subscription->currency,
                    'trial_ends_at' => $subscription->trial_ends_at,
                    'ends_at' => $subscription->ends_at,
                    'auto_renew' => $subscription->auto_renew,
                ] : null;
            }),
            'teams' => TeamResource::collection($this->whenLoaded('teams')),
            'projects_count' => $this->when($request->include('projects_count'), $this->projects()->count()),
            'owned_teams_count' => $this->when($request->include('teams_count'), $this->ownedTeams()->count()),
        ];
    }
}
