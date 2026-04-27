<?php

namespace App\Services;

use App\Models\Budget;
use App\Models\Project;

class BudgetService
{
    public function createBudget(Project $project, array $data): Budget
    {
        $budget = $project->budgets()->create($data);
        $this->recalculateProjectBudget($project);

        return $budget;
    }

    public function updateBudget(Budget $budget, array $data): bool
    {
        $updated = $budget->update($data);
        $this->recalculateProjectBudget($budget->project);

        return $updated;
    }

    public function deleteBudget(Budget $budget): ?bool
    {
        $project = $budget->project;
        $deleted = $budget->delete();

        $this->recalculateProjectBudget($project);

        return $deleted;
    }

    public function recalculateProjectBudget(Project $project): Project
    {
        $project->update(['estimated_budget' => $project->budgets()->sum('cost')]);

        return $project;
    }
}
