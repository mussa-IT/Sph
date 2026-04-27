<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Cast;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable([
    'project_id',
    'item_name',
    'cost',
    'alternative',
    'notes',
])]
#[Cast('cost', 'decimal:2')]
class Budget extends Model
{
    public function project(): BelongsTo
    {
        return $this->belongsTo(Project::class);
    }
}
