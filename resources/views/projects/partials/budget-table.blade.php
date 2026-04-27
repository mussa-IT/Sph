<!-- Budget Breakdown Card -->
<div class="bg-gradient-to-r from-primary/5 to-primary/10 dark:from-primary/10 dark:to-primary/5 rounded-2xl border border-primary/20 p-6 mb-6">
    <div class="flex items-center justify-between">
        <div>
            <p class="text-sm text-muted dark:text-muted-dark">Total Project Budget</p>
            <p class="text-3xl font-bold text-foreground dark:text-foreground-dark mt-1">${{ number_format($project->budgets->sum('cost'), 2) }}</p>
            <p class="text-sm text-muted dark:text-muted-dark mt-1">{{ $project->budgets->count() }} items</p>
        </div>
        <div class="w-16 h-16 rounded-2xl bg-primary/20 flex items-center justify-center">
            <svg class="w-8 h-8 text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
        </div>
    </div>
</div>

<!-- Add Budget Form -->
<div class="bg-white dark:bg-background-dark rounded-2xl border border-muted/10 p-5 mb-6 shadow-sm">
    <form action="{{ route('projects.budgets.store', $project) }}" method="POST">
        @csrf
        <h3 class="font-semibold text-foreground dark:text-foreground-dark mb-4">Add Budget Item</h3>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <div class="md:col-span-2">
                <input
                    type="text"
                    name="item_name"
                    placeholder="Item name..."
                    required
                    class="w-full rounded-xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10"
                />
            </div>
            <input
                type="number"
                name="cost"
                placeholder="Cost"
                step="0.01"
                min="0"
                required
                class="rounded-xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
            <input
                type="text"
                name="alternative"
                placeholder="Alternative"
                class="rounded-xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10"
            />
        </div>
        <div class="mt-4">
            <textarea
                name="notes"
                placeholder="Notes (optional)"
                rows="2"
                class="w-full rounded-xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 text-sm outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10 resize-none"
            ></textarea>
        </div>
        <div class="flex justify-end mt-4">
            <button type="submit" class="px-5 py-2.5 bg-primary text-white rounded-xl font-medium hover:bg-primary/90 transition">
                Add Item
            </button>
        </div>
    </form>
</div>

<!-- Budget Items Table -->
<div class="bg-white dark:bg-background-dark rounded-2xl border border-muted/10 overflow-hidden shadow-sm">
    <x-table-wrapper>
        <table class="w-full">
            <thead>
                <tr class="border-b border-muted/10 bg-muted/5 dark:bg-muted-dark/5">
                    <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Item</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Cost</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Alternative</th>
                    <th class="px-5 py-4 text-left text-xs font-semibold text-muted uppercase tracking-wider">Notes</th>
                    <th class="px-5 py-4 text-right text-xs font-semibold text-muted uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-muted/10">
                @foreach ($project->budgets as $budget)
                    <tr class="hover:bg-muted/5 dark:hover:bg-muted-dark/5 transition-colors">
                        <td class="px-5 py-4">
                            <div class="font-medium text-foreground dark:text-foreground-dark">{{ $budget->item_name }}</div>
                        </td>
                        <td class="px-5 py-4 font-medium text-foreground dark:text-foreground-dark">${{ number_format($budget->cost, 2) }}</td>
                        <td class="px-5 py-4 text-sm text-muted">{{ $budget->alternative ?? '-' }}</td>
                        <td class="px-5 py-4 text-sm text-muted">{{ $budget->notes ?? '-' }}</td>
                        <td class="px-5 py-4 text-right">
                            <form action="{{ route('budgets.destroy', $budget) }}" method="POST" onsubmit="return confirm('Delete this budget item?')" class="inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-1.5 text-muted hover:text-red-500 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                    </svg>
                                </button>
                            </form>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </x-table-wrapper>

    @if ($project->budgets->isEmpty())
        <div class="text-center py-12">
            <svg class="w-12 h-12 mx-auto text-muted/50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            <p class="mt-4 text-muted">No budget items added yet.</p>
        </div>
    @endif
</div>