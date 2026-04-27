<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BudgetResource extends JsonResource
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
            'amount' => $this->amount,
            'currency' => $this->currency,
            'spent' => $this->spent,
            'remaining' => $this->amount - $this->spent,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
            'project' => [
                'id' => $this->project->id,
                'title' => $this->project->title,
            ],
            'percentage_used' => $this->amount > 0 ? ($this->spent / $this->amount) * 100 : 0,
        ];
    }
}
