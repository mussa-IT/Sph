@extends('layouts.app')

@section('content')
<div class="container mx-auto px-4 py-8 max-w-[1440px] min-h-[calc(100vh-10rem)]">
    <div class="mb-8">
        <h1 class="text-3xl md:text-4xl font-bold text-foreground dark:text-foreground-dark">Smart Builder</h1>
        <p class="text-muted mt-2 max-w-2xl">Share your idea and get a polished project plan, budget estimate, tools recommendation and step-by-step roadmap.</p>
    </div>

    <div class="grid gap-6 grid-cols-1 xl:grid-cols-[1.08fr_0.92fr] min-h-[calc(100vh-12rem)]">
        <div class="bg-white dark:bg-background-dark rounded-[2rem] border border-muted/10 flex flex-col overflow-hidden shadow-xl">
            <div class="px-6 py-5 border-b border-muted/10 flex items-center gap-3">
                <div class="w-11 h-11 rounded-2xl bg-gradient-to-br from-primary to-purple-500 flex items-center justify-center shadow-lg text-white">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <div>
                    <div class="font-semibold text-foreground dark:text-foreground-dark">AI Project Assistant</div>
                    <div class="text-xs text-muted">Ready to help you build</div>
                </div>
            </div>

            <div id="chat-messages" class="flex-1 overflow-y-auto p-4 space-y-4 min-h-[200px]">
                {{-- AI Welcome Message --}}
                <div class="flex gap-3">
                    <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary to-purple-500 flex-shrink-0 flex items-center justify-center text-white text-sm font-bold">AI</div>
                    <div class="bg-slate-100 dark:bg-slate-800 rounded-2xl rounded-tl-none p-3 max-w-[90%] border border-slate-200 dark:border-slate-700">
                        <p class="text-sm text-slate-900 dark:text-slate-100">Hi! I'm your AI Project Assistant. Describe your project idea and I'll help you create a complete plan with budget estimates, timeline, and implementation steps.</p>
                    </div>
                </div>
            </div>

            <div class="px-6 py-5 border-t border-muted/10 bg-background dark:bg-background-dark">
                <form id="builder-form" class="relative">
                    <textarea
                        id="idea-input"
                        name="idea"
                        rows="3"
                        placeholder="Describe your project idea and goals..."
                        class="w-full rounded-2xl border border-muted/20 bg-background dark:bg-background-dark px-4 py-3 pr-20 text-sm text-foreground dark:text-foreground-dark placeholder:text-muted dark:placeholder:text-muted-dark outline-none transition duration-200 focus:border-primary focus:ring-4 focus:ring-primary/10 resize-none"
                    ></textarea>
                    <button type="submit" class="absolute right-4 bottom-4 inline-flex h-11 w-11 items-center justify-center rounded-xl bg-primary text-white shadow-lg shadow-primary/20 hover:bg-primary/90 transition" aria-label="Analyze idea">
                        <span class="sr-only">Analyze</span>
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                    </button>
                </form>
                <p class="mt-3 text-xs text-muted dark:text-muted-dark">Use the AI assistant to generate roadmap, budget and implementation priorities.</p>
            </div>
        </div>

        <div class="bg-white dark:bg-background-dark rounded-[2rem] border border-muted/10 flex flex-col overflow-hidden shadow-xl">
            <div class="px-6 py-5 border-b border-muted/10">
                <h2 class="font-semibold text-foreground dark:text-foreground-dark">Project Analysis</h2>
                <p class="text-sm text-muted mt-1">Structured output from your project idea.</p>
            </div>

            <div class="p-6 space-y-5 analysis-panel">
                <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-6 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-xs uppercase tracking-[.28em] text-muted dark:text-muted-dark">Project Type</p>
                            <h3 class="mt-2 text-2xl font-semibold text-foreground dark:text-foreground-dark" id="overview-project-type">Web Application</h3>
                        </div>
                        <span id="overview-difficulty" class="inline-flex rounded-full bg-primary/10 px-3 py-1 text-xs font-semibold text-primary dark:bg-primary/20">Intermediate</span>
                    </div>
                    <p class="mt-4 text-sm text-muted dark:text-muted-dark" id="overview-summary">Analysis cards update instantly after each idea submission.</p>
                </div>

                <div class="grid gap-5 sm:grid-cols-2 xl:grid-cols-4">
                    <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Feasibility</p>
                        <p class="mt-4 text-4xl font-semibold text-foreground dark:text-foreground-dark" id="feasibility-score">84%</p>
                        <p class="mt-2 text-sm text-muted dark:text-muted-dark">Likelihood of practical delivery</p>
                    </div>

                    <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Budget</p>
                        <p class="mt-4 text-2xl font-semibold text-foreground dark:text-foreground-dark" id="budget-range">$5,000 - $25,000</p>
                        <p class="mt-2 text-sm text-muted dark:text-muted-dark" id="budget-hours">Est. 240 hrs - USD</p>
                    </div>

                    <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Tools</p>
                        <div class="mt-4 space-y-3 text-xs">
                            <div class="rounded-xl bg-muted/10 p-3 dark:bg-muted-dark/20">
                                <p class="font-semibold text-foreground dark:text-foreground-dark">Primary Tools</p>
                                <p class="mt-1 text-muted dark:text-muted-dark" id="tools-primary">Laravel, React, PostgreSQL</p>
                            </div>
                            <div class="rounded-xl bg-muted/10 p-3 dark:bg-muted-dark/20">
                                <p class="font-semibold text-foreground dark:text-foreground-dark">Cheap Alternatives</p>
                                <p class="mt-1 text-muted dark:text-muted-dark" id="tools-cheap">Render starter, Namecheap hosting</p>
                            </div>
                            <div class="rounded-xl bg-muted/10 p-3 dark:bg-muted-dark/20">
                                <p class="font-semibold text-foreground dark:text-foreground-dark">Free Software</p>
                                <p class="mt-1 text-muted dark:text-muted-dark" id="tools-free">Laravel, Docker, VS Code</p>
                            </div>
                            <div class="rounded-xl bg-muted/10 p-3 dark:bg-muted-dark/20">
                                <p class="font-semibold text-foreground dark:text-foreground-dark">DIY Options</p>
                                <p class="mt-1 text-muted dark:text-muted-dark" id="tools-diy">Template customization and manual QA checklist</p>
                            </div>
                            <div class="rounded-xl bg-muted/10 p-3 dark:bg-muted-dark/20">
                                <p class="font-semibold text-foreground dark:text-foreground-dark">Local Sourcing</p>
                                <p class="mt-1 text-muted dark:text-muted-dark" id="tools-local">Local freelancers, university tech communities</p>
                            </div>
                        </div>
                    </div>

                    <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Timeline</p>
                        <p class="mt-4 text-2xl font-semibold text-foreground dark:text-foreground-dark" id="timeline-weeks">12 weeks</p>
                        <p class="mt-2 text-sm text-muted dark:text-muted-dark" id="timeline-label">Estimated delivery window</p>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">AI Budget Analysis</p>
                        <span class="text-xs text-muted dark:text-muted-dark">Cards, meter and comparisons</span>
                    </div>

                    <div class="mt-4 grid gap-4 sm:grid-cols-3">
                        <div class="rounded-xl bg-muted/10 p-4 dark:bg-muted-dark/20">
                            <p class="text-xs uppercase tracking-wide text-muted dark:text-muted-dark">Minimum Budget</p>
                            <p id="budget-card-minimum" class="mt-2 text-xl font-semibold text-foreground dark:text-foreground-dark">$5,000</p>
                            <p class="mt-1 text-xs text-muted dark:text-muted-dark">Lean launch target</p>
                        </div>
                        <div class="rounded-xl bg-muted/10 p-4 dark:bg-muted-dark/20">
                            <p class="text-xs uppercase tracking-wide text-muted dark:text-muted-dark">Ideal Budget</p>
                            <p id="budget-card-ideal" class="mt-2 text-xl font-semibold text-foreground dark:text-foreground-dark">$25,000</p>
                            <p class="mt-1 text-xs text-muted dark:text-muted-dark">Recommended quality target</p>
                        </div>
                        <div class="rounded-xl bg-muted/10 p-4 dark:bg-muted-dark/20">
                            <p class="text-xs uppercase tracking-wide text-muted dark:text-muted-dark">Budget Buffer</p>
                            <p id="budget-card-buffer" class="mt-2 text-xl font-semibold text-foreground dark:text-foreground-dark">$20,000</p>
                            <p class="mt-1 text-xs text-muted dark:text-muted-dark">Difference between minimum and ideal</p>
                        </div>
                    </div>

                    <div class="mt-5 rounded-xl border border-muted/10 bg-background p-4 dark:border-muted-dark/20 dark:bg-background-dark">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Budget Progress Meter</p>
                            <span id="budget-meter-label" class="text-xs text-muted dark:text-muted-dark">20% of ideal budget</span>
                        </div>
                        <div class="mt-3 h-3 w-full overflow-hidden rounded-full bg-muted/20 dark:bg-muted-dark/30">
                            <div id="budget-meter-fill" class="h-full rounded-full bg-gradient-to-r from-primary/80 to-primary transition-all duration-500" style="width: 20%;"></div>
                        </div>
                    </div>

                    <div class="mt-5">
                        <div class="flex items-center justify-between">
                            <p class="text-sm font-medium text-foreground dark:text-foreground-dark">Component Comparison Bars</p>
                            <span class="text-xs text-muted dark:text-muted-dark">Minimum vs ideal by component</span>
                        </div>
                        <div id="budget-comparison-bars" class="mt-4 space-y-4">
                            <div class="space-y-2">
                                <div class="flex items-center justify-between text-xs text-muted dark:text-muted-dark">
                                    <span>Development</span>
                                    <span>$2,500 - $13,000</span>
                                </div>
                                <div class="h-2 rounded-full bg-muted/20 dark:bg-muted-dark/30">
                                    <div class="h-2 rounded-full bg-primary/35" style="width: 19%;"></div>
                                </div>
                                <div class="h-2 rounded-full bg-muted/20 dark:bg-muted-dark/30">
                                    <div class="h-2 rounded-full bg-primary" style="width: 100%;"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Execution Steps</p>
                        <span class="text-xs text-muted dark:text-muted-dark">Timeline view</span>
                    </div>
                    <div class="mt-5 space-y-5" id="steps-timeline">
                        <div class="relative pl-12">
                            <span class="absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-primary text-xs font-semibold text-white">1</span>
                            <span class="absolute left-4 top-8 h-[calc(100%-0.5rem)] w-px bg-muted/30 dark:bg-muted-dark/30"></span>
                            <p class="font-semibold text-foreground dark:text-foreground-dark">Requirements and Scope Definition</p>
                            <p class="mt-1 text-sm text-muted dark:text-muted-dark">Finalize core goals, users, and success metrics.</p>
                        </div>
                        <div class="relative pl-12">
                            <span class="absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full bg-muted/20 text-xs font-semibold text-muted dark:bg-muted-dark/30 dark:text-muted-dark">2</span>
                            <p class="font-semibold text-foreground dark:text-foreground-dark">Build and Validate MVP</p>
                            <p class="mt-1 text-sm text-muted dark:text-muted-dark">Implement prioritized features and test early.</p>
                        </div>
                    </div>
                </div>

                <div class="rounded-[2rem] border border-muted/10 bg-background-secondary p-5 shadow-sm dark:border-muted-dark/10 dark:bg-background-secondary-dark">
                    <div class="flex items-center justify-between">
                        <p class="text-sm font-medium text-muted dark:text-muted-dark">Key Risks</p>
                        <span class="text-xs text-muted dark:text-muted-dark">Watchlist</span>
                    </div>
                    <ul class="mt-4 list-disc space-y-2 pl-5 text-sm text-foreground dark:text-foreground-dark" id="risks-list">
                        <li>Scope creep from unclear requirements.</li>
                        <li>Timeline shifts due to external dependencies.</li>
                        <li>Budget variance without milestone reviews.</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
@keyframes builderTypingBounce {
    0%, 80%, 100% { transform: translateY(0); opacity: 0.4; }
    40% { transform: translateY(-4px); opacity: 1; }
}

/* Ensure text visibility in Smart Builder textarea */
#idea-input {
    color: #0f172a !important;
}

.dark #idea-input {
    color: #f8fafc !important;
}

#idea-input::placeholder {
    color: #64748b !important;
}

.dark #idea-input::placeholder {
    color: #cbd5e1 !important;
}

/* Focus states for better visibility */
#idea-input:focus {
    color: #0f172a !important;
    background-color: #ffffff !important;
}

.dark #idea-input:focus {
    color: #f8fafc !important;
    background-color: #0f172a !important;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('#builder-form');
    const textarea = document.querySelector('#idea-input');
    const submitButton = document.querySelector('button[type="submit"]');
    const chatMessages = document.querySelector('#chat-messages');
    const analysisPanel = document.querySelector('.analysis-panel');
    let typingIndicatorEl = null;

    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const idea = textarea.value.trim();
        if (!idea) {
            return;
        }

        setLoadingState(true);
        addMessageToChat('user', idea);
        showAITypingIndicator();
        textarea.value = '';

        try {
            const response = await fetch('{{ route('builder.analyze') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({ idea })
            });

            const result = await response.json();
            hideAITypingIndicator();

            if (result.success) {
                addMessageToChat('ai', 'Your project analysis is ready. I updated feasibility, budget, tools, timeline, and execution steps.');
                updateAnalysisPanel(result.data);
            } else {
                addMessageToChat('ai', 'Sorry, I encountered an error analyzing your idea. Please try again.');
            }
        } catch (error) {
            hideAITypingIndicator();
            console.error('Error:', error);
            addMessageToChat('ai', 'Sorry, there was an error connecting to the analysis service. Please try again later.');
        } finally {
            setLoadingState(false);
            textarea.focus();
        }
    });

    function addMessageToChat(type, message) {
        const messageDiv = document.createElement('div');
        messageDiv.className = type === 'user' ? 'flex gap-3 justify-end' : 'flex gap-3';

        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-purple-500 flex-shrink-0';

        const bubble = document.createElement('div');
        bubble.className = type === 'user'
            ? 'bg-primary text-primary-foreground rounded-2xl rounded-tr-none p-4 max-w-[85%] ml-auto'
            : 'bg-muted/10 rounded-2xl rounded-tl-none p-4 max-w-[85%]';

        const text = document.createElement('p');
        text.className = 'text-sm ' + (type === 'user' ? '' : 'text-foreground dark:text-foreground-dark');
        text.textContent = message;

        bubble.appendChild(text);

        if (type === 'ai') {
            const actions = document.createElement('div');
            actions.className = 'mt-3 flex justify-end';

            const copyButton = document.createElement('button');
            copyButton.type = 'button';
            copyButton.className = 'inline-flex items-center rounded-lg border border-muted/20 px-2.5 py-1 text-xs font-medium text-muted hover:text-foreground hover:border-muted/40 transition';
            copyButton.textContent = 'Copy';
            copyButton.setAttribute('aria-label', 'Copy AI response');

            copyButton.addEventListener('click', async function() {
                const originalLabel = copyButton.textContent;
                try {
                    if (navigator.clipboard && navigator.clipboard.writeText) {
                        await navigator.clipboard.writeText(message);
                    } else {
                        const tempInput = document.createElement('textarea');
                        tempInput.value = message;
                        tempInput.setAttribute('readonly', '');
                        tempInput.style.position = 'absolute';
                        tempInput.style.left = '-9999px';
                        document.body.appendChild(tempInput);
                        tempInput.select();
                        document.execCommand('copy');
                        document.body.removeChild(tempInput);
                    }
                    copyButton.textContent = 'Copied';
                } catch (error) {
                    copyButton.textContent = 'Failed';
                } finally {
                    setTimeout(() => {
                        copyButton.textContent = originalLabel;
                    }, 1200);
                }
            });

            actions.appendChild(copyButton);
            bubble.appendChild(actions);
        }

        if (type === 'user') {
            messageDiv.appendChild(bubble);
            messageDiv.appendChild(avatar);
        } else {
            messageDiv.appendChild(avatar);
            messageDiv.appendChild(bubble);
        }

        chatMessages.appendChild(messageDiv);
        scrollChatToLatest(true);
    }

    function showAITypingIndicator() {
        if (typingIndicatorEl) {
            return;
        }

        typingIndicatorEl = document.createElement('div');
        typingIndicatorEl.className = 'flex gap-3';
        typingIndicatorEl.setAttribute('data-typing-indicator', 'true');

        const avatar = document.createElement('div');
        avatar.className = 'w-8 h-8 rounded-lg bg-gradient-to-br from-primary to-purple-500 flex-shrink-0';

        const bubble = document.createElement('div');
        bubble.className = 'bg-muted/10 rounded-2xl rounded-tl-none p-4 max-w-[85%]';

        const dotsWrap = document.createElement('div');
        dotsWrap.className = 'flex items-center gap-1';

        for (let i = 0; i < 3; i++) {
            const dot = document.createElement('span');
            dot.className = 'inline-block h-2 w-2 rounded-full bg-muted dark:bg-muted-dark';
            dot.style.animation = `builderTypingBounce 1s ${i * 0.15}s infinite ease-in-out`;
            dotsWrap.appendChild(dot);
        }

        bubble.appendChild(dotsWrap);
        typingIndicatorEl.appendChild(avatar);
        typingIndicatorEl.appendChild(bubble);
        chatMessages.appendChild(typingIndicatorEl);
        scrollChatToLatest(true);
    }

    function hideAITypingIndicator() {
        if (!typingIndicatorEl) {
            return;
        }

        typingIndicatorEl.remove();
        typingIndicatorEl = null;
        scrollChatToLatest(true);
    }

    function scrollChatToLatest(smooth = true) {
        chatMessages.scrollTo({
            top: chatMessages.scrollHeight,
            behavior: smooth ? 'smooth' : 'auto',
        });
    }

    function setLoadingState(isLoading) {
        textarea.disabled = isLoading;
        submitButton.disabled = isLoading;

        submitButton.innerHTML = isLoading
            ? '<div class="w-4 h-4 border-2 border-white border-t-transparent rounded-full animate-spin"></div>'
            : `
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                </svg>
            `;
    }

    function updateAnalysisPanel(data) {
        const projectType = analysisPanel.querySelector('#overview-project-type');
        const difficulty = analysisPanel.querySelector('#overview-difficulty');
        const summary = analysisPanel.querySelector('#overview-summary');
        const feasibilityScore = analysisPanel.querySelector('#feasibility-score');
        const timelineWeeks = analysisPanel.querySelector('#timeline-weeks');
        const timelineLabel = analysisPanel.querySelector('#timeline-label');

        projectType.textContent = data.project_type;
        difficulty.textContent = data.difficulty;
        summary.textContent = `A ${String(data.difficulty).toLowerCase()} ${String(data.project_type).toLowerCase()} with practical execution guidance.`;
        feasibilityScore.textContent = `${data.feasibility_score}%`;
        timelineWeeks.textContent = `${data.timeline.weeks} weeks`;
        timelineLabel.textContent = data.timeline.label;

        const budgetRange = analysisPanel.querySelector('#budget-range');
        const budgetHours = analysisPanel.querySelector('#budget-hours');
        const minBudgetRaw = Number(data?.estimated_budget?.range?.min ?? 0);
        const maxBudgetRaw = Number(data?.estimated_budget?.range?.max ?? 0);
        const minBudget = minBudgetRaw.toLocaleString();
        const maxBudget = maxBudgetRaw.toLocaleString();
        budgetRange.textContent = `$${minBudget} - $${maxBudget}`;
        budgetHours.textContent = `Est. ${data.estimated_budget.estimated_hours} hrs - ${data.estimated_budget.currency}`;

        const budgetCardMinimum = analysisPanel.querySelector('#budget-card-minimum');
        const budgetCardIdeal = analysisPanel.querySelector('#budget-card-ideal');
        const budgetCardBuffer = analysisPanel.querySelector('#budget-card-buffer');
        const meterLabel = analysisPanel.querySelector('#budget-meter-label');
        const meterFill = analysisPanel.querySelector('#budget-meter-fill');
        const comparisonBars = analysisPanel.querySelector('#budget-comparison-bars');

        const formatCurrency = (amount) => `$${Number(amount || 0).toLocaleString()}`;
        const budgetBuffer = Math.max(0, maxBudgetRaw - minBudgetRaw);
        const meterPercent = maxBudgetRaw > 0 ? Math.min(100, Math.round((minBudgetRaw / maxBudgetRaw) * 100)) : 0;

        budgetCardMinimum.textContent = formatCurrency(minBudgetRaw);
        budgetCardIdeal.textContent = formatCurrency(maxBudgetRaw);
        budgetCardBuffer.textContent = formatCurrency(budgetBuffer);
        meterLabel.textContent = `${meterPercent}% of ideal budget`;
        meterFill.style.width = `${meterPercent}%`;

        const components = Array.isArray(data?.estimated_budget?.component_cost_breakdown)
            ? data.estimated_budget.component_cost_breakdown
            : [];

        comparisonBars.innerHTML = '';
        if (components.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'text-sm text-muted dark:text-muted-dark';
            empty.textContent = 'No component comparison data available for this analysis yet.';
            comparisonBars.appendChild(empty);
        } else {
            const highestIdeal = Math.max(...components.map((item) => Number(item.ideal_cost || 0)), 1);

            components.forEach((item) => {
                const componentName = String(item.component || 'Component');
                const minimumCost = Number(item.minimum_cost || 0);
                const idealCost = Number(item.ideal_cost || 0);
                const minimumWidth = Math.max(4, Math.round((minimumCost / highestIdeal) * 100));
                const idealWidth = Math.max(4, Math.round((idealCost / highestIdeal) * 100));

                const wrapper = document.createElement('div');
                wrapper.className = 'space-y-2';

                const meta = document.createElement('div');
                meta.className = 'flex items-center justify-between text-xs text-muted dark:text-muted-dark';

                const label = document.createElement('span');
                label.textContent = componentName;
                const range = document.createElement('span');
                range.textContent = `${formatCurrency(minimumCost)} - ${formatCurrency(idealCost)}`;
                meta.appendChild(label);
                meta.appendChild(range);
                wrapper.appendChild(meta);

                const minimumTrack = document.createElement('div');
                minimumTrack.className = 'h-2 rounded-full bg-muted/20 dark:bg-muted-dark/30';
                const minimumBar = document.createElement('div');
                minimumBar.className = 'h-2 rounded-full bg-primary/35';
                minimumBar.style.width = `${minimumWidth}%`;
                minimumTrack.appendChild(minimumBar);
                wrapper.appendChild(minimumTrack);

                const idealTrack = document.createElement('div');
                idealTrack.className = 'h-2 rounded-full bg-muted/20 dark:bg-muted-dark/30';
                const idealBar = document.createElement('div');
                idealBar.className = 'h-2 rounded-full bg-primary';
                idealBar.style.width = `${idealWidth}%`;
                idealTrack.appendChild(idealBar);
                wrapper.appendChild(idealTrack);

                comparisonBars.appendChild(wrapper);
            });
        }

        const toolData = data?.recommended_tools ?? {};
        const primaryTools = analysisPanel.querySelector('#tools-primary');
        const cheapTools = analysisPanel.querySelector('#tools-cheap');
        const freeTools = analysisPanel.querySelector('#tools-free');
        const diyTools = analysisPanel.querySelector('#tools-diy');
        const localTools = analysisPanel.querySelector('#tools-local');

        const toolListToText = (list, fallback) => {
            return Array.isArray(list) && list.length > 0 ? list.join(', ') : fallback;
        };

        primaryTools.textContent = toolListToText(toolData.primary_tools, 'No primary tools available');
        cheapTools.textContent = toolListToText(toolData.cheap_alternatives, 'No cheap alternatives available');
        freeTools.textContent = toolListToText(toolData.free_software_alternatives, 'No free software alternatives available');
        diyTools.textContent = toolListToText(toolData.diy_options, 'No DIY options available');
        localTools.textContent = toolListToText(toolData.local_sourcing_suggestions, 'No local sourcing suggestions available');

        const stepsTimeline = analysisPanel.querySelector('#steps-timeline');
        stepsTimeline.innerHTML = '';
        const steps = (data.steps || []).slice(0, 6);

        steps.forEach((step, index) => {
            const stepNumber = index + 1;
            const isLast = stepNumber === steps.length;

            const stepBlock = document.createElement('div');
            stepBlock.className = 'relative pl-12';

            const dot = document.createElement('span');
            dot.className = `absolute left-0 top-0 flex h-8 w-8 items-center justify-center rounded-full text-xs font-semibold ${stepNumber === 1 ? 'bg-primary text-white' : 'bg-muted/20 text-muted dark:bg-muted-dark/30 dark:text-muted-dark'}`;
            dot.textContent = stepNumber;
            stepBlock.appendChild(dot);

            if (!isLast) {
                const line = document.createElement('span');
                line.className = 'absolute left-4 top-8 h-[calc(100%-0.5rem)] w-px bg-muted/30 dark:bg-muted-dark/30';
                stepBlock.appendChild(line);
            }

            const stepTitle = document.createElement('p');
            stepTitle.className = 'font-semibold text-foreground dark:text-foreground-dark';
            stepTitle.textContent = step;
            stepBlock.appendChild(stepTitle);

            stepsTimeline.appendChild(stepBlock);
        });

        const risksList = analysisPanel.querySelector('#risks-list');
        risksList.innerHTML = '';
        (data.risks || []).forEach(risk => {
            const li = document.createElement('li');
            li.textContent = risk;
            risksList.appendChild(li);
        });
    }
});
</script>
@endsection
