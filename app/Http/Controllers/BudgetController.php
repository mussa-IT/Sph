<?php

namespace App\Http\Controllers;

use App\Http\Requests\BudgetStoreRequest;
use App\Models\Budget;
use App\Models\Project;
use App\Services\BudgetService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BudgetController extends Controller
{
    protected BudgetService $budgetService;

    public function __construct(BudgetService $budgetService)
    {
        $this->budgetService = $budgetService;
    }

    public function index(): View
    {
        $budgets = Auth::user()->budgets()->with('project')->latest()->paginate(20);

        return view('pages.budgets', compact('budgets'));
    }

    public function store(BudgetStoreRequest $request, Project $project): RedirectResponse
    {
        $this->authorize('update', $project);

        $validated = $request->validated();

        $this->budgetService->createBudget($project, $validated);

        return back()->with('success', __('Budget item added successfully.'));
    }

    public function update(BudgetStoreRequest $request, Budget $budget): RedirectResponse
    {
        $this->authorize('update', $budget->project);

        $validated = $request->validated();

        $this->budgetService->updateBudget($budget, $validated);

        return back()->with('success', __('Budget item updated.'));
    }

    public function destroy(Budget $budget): RedirectResponse
    {
        $this->authorize('delete', $budget->project);

        $this->budgetService->deleteBudget($budget);

        return back()->with('success', __('Budget item deleted.'));
    }
}