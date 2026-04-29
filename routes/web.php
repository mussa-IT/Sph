<?php

use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\PublicSeoController;
use App\Http\Controllers\BuilderController;
use App\Http\Controllers\BudgetController;
use App\Http\Controllers\ChatController;
use App\Http\Controllers\LanguageController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\Auth\GoogleController;
use App\Http\Controllers\CommentController;
use App\Http\Controllers\ReferralController;
use App\Http\Controllers\RevenueController;
use App\Http\Controllers\AIAutopilotController;
use App\Http\Controllers\LocalizationController;
use App\Http\Controllers\ProjectAnalyticsController;
use App\Http\Controllers\PWAController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TemplateController;
use App\Http\Controllers\TrialController;
use App\Http\Controllers\UpgradePromptController;
use App\Http\Controllers\Web3Controller;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BlueprintController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');
Route::get('/sitemap.xml', [PublicSeoController::class, 'sitemap'])->name('seo.sitemap');
Route::get('/robots.txt', [PublicSeoController::class, 'robots'])->name('seo.robots');

Route::get('auth/google', [GoogleController::class, 'redirectToGoogle'])->name('auth.google.redirect');
Route::get('auth/google/callback', [GoogleController::class, 'handleGoogleCallback'])->name('auth.google.callback');

Route::middleware('guest')->group(function () {
    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store'])->middleware('throttle:auth-attempts');

    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store'])->middleware('throttle:auth-attempts');

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email')->middleware('throttle:auth-attempts');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update')->middleware('throttle:auth-attempts');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware(['auth', 'throttle:auth-attempts'])->name('logout');

Route::get('/language/{locale}', [LanguageController::class, 'switch'])->name('language.switch');

// Public pricing page
Route::get('/pricing', [SubscriptionController::class, 'pricing'])->name('pricing');

// Public referral pages
Route::get('/ref/{code}', [ReferralController::class, 'publicPage'])->name('referrals.public');
Route::get('/referrals/{code}', [ReferralController::class, 'accept'])->name('referrals.accept');

Route::middleware('auth')->group(function () {
    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'index'])->name('index');
        Route::get('users', [AdminController::class, 'users'])->name('users.index');
        Route::patch('users/{user}/suspend', [AdminController::class, 'suspendUser'])->name('users.suspend');
    });

    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/stats', [DashboardController::class, 'apiStats'])->name('dashboard.stats');

    Route::resource('projects', ProjectController::class);
    Route::resource('tasks', TaskController::class);
    Route::resource('budgets', BudgetController::class);
    Route::post('projects/{project}/budgets', [BudgetController::class, 'store'])->name('projects.budgets.store');

    Route::get('/chat', [ChatController::class, 'index'])->name('chat');
    Route::post('/chat/sessions', [ChatController::class, 'storeSession'])
        ->middleware('throttle:ai-chat')
        ->name('chat.sessions.store');
    Route::get('/chat/sessions/{chatSession}', [ChatController::class, 'showSession'])->name('chat.sessions.show');
    Route::post('/chat/sessions/{chatSession}/messages', [ChatController::class, 'sendMessage'])
        ->middleware('throttle:ai-chat')
        ->name('chat.messages.send');
    Route::patch('/chat/sessions/{chatSession}', [ChatController::class, 'renameSession'])->name('chat.sessions.rename');
    Route::delete('/chat/sessions/{chatSession}', [ChatController::class, 'deleteSession'])->name('chat.sessions.delete');

    Route::get('/builder', function () {
        return view('pages.builder');
    })->name('builder');

    Route::post('/builder/analyze', [BuilderController::class, 'analyzeIdea'])
        ->middleware('throttle:ai-builder')
        ->name('builder.analyze');

    Route::get('/resources', function () {
        return view('pages.resources');
    })->name('resources');

    Route::get('/api-docs', function () {
        return view('pages.api-docs');
    })->name('api-docs');

    Route::get('/integrations', function () {
        return view('pages.integrations');
    })->name('integrations');

    Route::get('/profile', [ProfileController::class, 'show'])->name('profile');

    Route::get('/settings', [SettingsController::class, 'show'])->name('settings');
    Route::get('/settings/security', [SettingsController::class, 'security'])->name('settings.security');
    Route::patch('/settings/profile', [SettingsController::class, 'updateProfile'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.profile.update');
    Route::patch('/settings/password', [SettingsController::class, 'updatePassword'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.password.update');
    Route::patch('/settings/security/logout-other-sessions', [SettingsController::class, 'logoutOtherSessions'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.security.sessions.logout-others');
    Route::patch('/settings/security/two-factor-placeholder', [SettingsController::class, 'updateTwoFactorPlaceholder'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.security.two-factor-placeholder.update');
    Route::patch('/settings/preferences', [SettingsController::class, 'updatePreferences'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.preferences.update');
    Route::delete('/settings/account', [SettingsController::class, 'destroy'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.account.destroy');
    Route::delete('/settings/account/google', [SettingsController::class, 'disconnectGoogle'])
        ->middleware('throttle:sensitive-actions')
        ->name('settings.account.google.disconnect');

    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/feed', [NotificationController::class, 'feed'])->name('notifications.feed');
    Route::patch('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.mark-read');
    Route::patch('/notifications/mark-all-read', [NotificationController::class, 'markAllAsRead'])->name('notifications.mark-all-read');

    // Localization Routes
    Route::prefix('localization')->name('localization.')->group(function () {
        Route::get('/currencies', [LocalizationController::class, 'currencies'])->name('currencies');
        Route::get('/timezones', [LocalizationController::class, 'timezones'])->name('timezones');
        Route::post('/convert-currency', [LocalizationController::class, 'convertCurrency'])->name('convert_currency');
        Route::get('/exchange-rates', [LocalizationController::class, 'exchangeRates'])->name('exchange_rates');
        Route::get('/detect-settings', [LocalizationController::class, 'detectUserSettings'])->name('detect_settings');
        Route::post('/user-preferences', [LocalizationController::class, 'setUserPreferences'])->name('set_user_preferences')->middleware('auth');
        Route::get('/user-preferences', [LocalizationController::class, 'getUserPreferences'])->name('get_user_preferences')->middleware('auth');
        Route::post('/format-amount', [LocalizationController::class, 'formatAmount'])->name('format_amount');
        Route::post('/convert-time', [LocalizationController::class, 'convertTime'])->name('convert_time');
        Route::post('/local-times', [LocalizationController::class, 'getLocalTimes'])->name('local_times');
        Route::post('/working-hours', [LocalizationController::class, 'getWorkingHours'])->name('working_hours');
        Route::get('/currency-trends', [LocalizationController::class, 'getCurrencyTrends'])->name('currency_trends');
        Route::post('/refresh-rates', [LocalizationController::class, 'refreshExchangeRates'])->name('refresh_rates');
        Route::post('/dst-info', [LocalizationController::class, 'getDSTInfo'])->name('dst_info');
        Route::post('/time-difference', [LocalizationController::class, 'getTimeDifference'])->name('time_difference');
    });

    // Project Analytics Routes
    Route::prefix('projects/{project}/analytics')->name('project.analytics.')->group(function () {
        Route::get('/insights', [ProjectAnalyticsController::class, 'insights'])->name('insights');
        Route::post('/optimize-schedule', [ProjectAnalyticsController::class, 'optimizeSchedule'])->name('optimize_schedule');
        Route::get('/collaboration', [ProjectAnalyticsController::class, 'collaborationInsights'])->name('collaboration');
        Route::get('/team-performance', [ProjectAnalyticsController::class, 'teamPerformance'])->name('team_performance');
        Route::get('/budget-analysis', [ProjectAnalyticsController::class, 'budgetAnalysis'])->name('budget_analysis');
        Route::get('/risk-assessment', [ProjectAnalyticsController::class, 'riskAssessment'])->name('risk_assessment');
        Route::get('/timeline-prediction', [ProjectAnalyticsController::class, 'timelinePrediction'])->name('timeline_prediction');
        Route::get('/comparative-analysis', [ProjectAnalyticsController::class, 'comparativeAnalysis'])->name('comparative_analysis');
        Route::get('/performance-metrics', [ProjectAnalyticsController::class, 'performanceMetrics'])->name('performance_metrics');
        Route::get('/critical-path', [ProjectAnalyticsController::class, 'criticalPath'])->name('critical_path');
        Route::get('/workload-balance', [ProjectAnalyticsController::class, 'workloadBalance'])->name('workload_balance');
        Route::get('/resource-optimization', [ProjectAnalyticsController::class, 'resourceOptimization'])->name('resource_optimization');
        Route::get('/dependency-analysis', [ProjectAnalyticsController::class, 'dependencyAnalysis'])->name('dependency_analysis');
        Route::get('/recommendations', [ProjectAnalyticsController::class, 'recommendations'])->name('recommendations');
        Route::get('/health-score', [ProjectAnalyticsController::class, 'healthScore'])->name('health_score');
        Route::get('/dashboard', [ProjectAnalyticsController::class, 'dashboard'])->name('dashboard');
    });

    // Web3 Routes
    Route::get('/web3/profile', [Web3Controller::class, 'profile'])->name('web3.profile');
    Route::get('/web3/verification', [Web3Controller::class, 'verification'])->name('web3.verification');
    Route::post('/web3/verify', [Web3Controller::class, 'verifyProject'])->name('web3.verify');
    Route::post('/web3/projects/{project}/publish', [Web3Controller::class, 'publishProject'])->name('web3.projects.publish');

    // Blueprint routes
    Route::get('/blueprints', [BlueprintController::class, 'index'])->name('blueprints.index');
    Route::post('/blueprints', [BlueprintController::class, 'store'])->name('blueprints.store');
    Route::get('/blueprints/{blueprint}', [BlueprintController::class, 'show'])->name('blueprints.show');
    Route::post('/blueprints/{blueprint}/anchor', [BlueprintController::class, 'anchorOnchain'])->name('blueprints.anchor');
    Route::delete('/blueprints/{blueprint}', [BlueprintController::class, 'destroy'])->name('blueprints.destroy');
    Route::post('/blueprints/verify', [BlueprintController::class, 'verify'])->name('blueprints.verify');

    // Bounty routes
    Route::get('/bounties', function () {
        return view('pages.bounties');
    })->name('bounties.index');

    // Subscription Routes
    Route::prefix('subscription')->name('subscription.')->group(function () {
        Route::get('/checkout/{plan}', [SubscriptionController::class, 'checkout'])->name('checkout');
        Route::post('/checkout/{plan}', [SubscriptionController::class, 'processCheckout'])->name('process');
        Route::get('/success', [SubscriptionController::class, 'success'])->name('success');
        Route::get('/billing', [SubscriptionController::class, 'billing'])->name('billing');
        Route::post('/cancel/{subscription}', [SubscriptionController::class, 'cancel'])->name('cancel');
        Route::post('/resume/{subscription}', [SubscriptionController::class, 'resume'])->name('resume');
        Route::post('/update-payment-method', [SubscriptionController::class, 'updatePaymentMethod'])->name('update_payment_method');
        Route::get('/invoice/{billingHistory}', [SubscriptionController::class, 'downloadInvoice'])->name('download_invoice');
    });

    // Comment Routes
    Route::prefix('comments')->name('comments.')->group(function () {
        Route::get('/', [CommentController::class, 'index'])->name('index');
        Route::post('/', [CommentController::class, 'store'])->name('store');
        Route::patch('/{comment}', [CommentController::class, 'update'])->name('update');
        Route::delete('/{comment}', [CommentController::class, 'destroy'])->name('destroy');
        Route::get('/{comment}/replies', [CommentController::class, 'getReplies'])->name('replies');
        Route::get('/search-users', [CommentController::class, 'searchUsers'])->name('search_users');
        Route::get('/mentions', [CommentController::class, 'getMentions'])->name('mentions');
        Route::post('/mentions/read', [CommentController::class, 'markAsRead'])->name('mark_read');
        Route::get('/mentions/unread-count', [CommentController::class, 'getUnreadCount'])->name('unread_count');
        Route::get('/activity', [CommentController::class, 'getActivityFeed'])->name('activity');
        Route::get('/stats/{project}', [CommentController::class, 'getStats'])->name('stats');
    });

    // Team Routes
    Route::prefix('teams')->name('teams.')->group(function () {
        Route::get('/', [TeamController::class, 'index'])->name('index');
        Route::post('/', [TeamController::class, 'store'])->name('store');
        Route::get('/create', [TeamController::class, 'create'])->name('create');
        Route::get('/{team}', [TeamController::class, 'show'])->name('show');
        Route::get('/{team}/edit', [TeamController::class, 'edit'])->name('edit');
        Route::patch('/{team}', [TeamController::class, 'update'])->name('update');
        Route::delete('/{team}', [TeamController::class, 'destroy'])->name('destroy');
        Route::post('/{team}/invite', [TeamController::class, 'inviteMember'])->name('invite');
        Route::post('/{team}/leave', [TeamController::class, 'leave'])->name('leave');
        Route::post('/{team}/transfer-ownership', [TeamController::class, 'transferOwnership'])->name('transfer_ownership');
        Route::post('/{team}/members/{member}/remove', [TeamController::class, 'removeMember'])->name('remove_member');
        Route::post('/{team}/members/{member}/role', [TeamController::class, 'updateMemberRole'])->name('update_member_role');
        Route::get('/invitations/{token}/accept', [TeamController::class, 'acceptInvitation'])->name('invitations.accept');
        Route::post('/invitations/{token}/accept', [TeamController::class, 'processInvitation'])->name('invitations.process');
        Route::post('/invitations/{token}/decline', [TeamController::class, 'declineInvitation'])->name('invitations.decline');
        Route::post('/invitations/{invitation}/resend', [TeamController::class, 'resendInvitation'])->name('invitations.resend');
        Route::post('/invitations/{invitation}/cancel', [TeamController::class, 'cancelInvitation'])->name('invitations.cancel');
        Route::get('/invitations', [TeamController::class, 'getInvitations'])->name('invitations');
    });

    // Referral Routes
    Route::prefix('referrals')->name('referrals.')->group(function () {
        Route::get('/', [ReferralController::class, 'index'])->name('index');
        Route::post('/', [ReferralController::class, 'create'])->name('create');
        Route::post('/share', [ReferralController::class, 'share'])->name('share');
        Route::post('/{code}/process', [ReferralController::class, 'process'])->name('process');
        Route::post('/{code}/convert', [ReferralController::class, 'convert'])->name('convert');
        Route::get('/stats', [ReferralController::class, 'stats'])->name('stats');
        Route::get('/leaderboard', [ReferralController::class, 'leaderboard'])->name('leaderboard');
        Route::get('/earnings', [ReferralController::class, 'earnings'])->name('earnings');
        Route::post('/{referral}/resend', [ReferralController::class, 'resendInvitation'])->name('resend');
        Route::post('/{referral}/cancel', [ReferralController::class, 'cancel'])->name('cancel');
    });

    // Upgrade Prompt Routes
    Route::prefix('upgrade-prompt')->name('upgrade-prompt.')->group(function () {
        Route::get('/', [UpgradePromptController::class, 'getPrompts'])->name('index');
        Route::post('/dismiss/{promptId}', [UpgradePromptController::class, 'dismiss'])->name('dismiss');
        Route::get('/suggestion', [UpgradePromptController::class, 'getSmartSuggestion'])->name('suggestion');
        Route::get('/trial-eligibility', [UpgradePromptController::class, 'getTrialEligibility'])->name('trial_eligibility');
        Route::get('/discount', [UpgradePromptController::class, 'getDiscountOpportunity'])->name('discount');
        Route::get('/context/{context}', [UpgradePromptController::class, 'getContextualPrompt'])->name('context');
        Route::get('/personalized-message', [UpgradePromptController::class, 'getPersonalizedMessage'])->name('personalized_message');
        Route::get('/check-eligibility', [UpgradePromptController::class, 'checkEligibility'])->name('check_eligibility');
        Route::post('/track', [UpgradePromptController::class, 'trackInteraction'])->name('track');
        Route::get('/stats', [UpgradePromptController::class, 'getUpgradeStats'])->name('stats');
    });

    // Trial Routes
    Route::prefix('trial')->name('trial.')->group(function () {
        Route::get('/', [TrialController::class, 'index'])->name('index');
        Route::post('/start', [TrialController::class, 'start'])->name('start');
        Route::get('/dashboard', [TrialController::class, 'dashboard'])->name('dashboard');
        Route::post('/convert', [TrialController::class, 'convert'])->name('convert');
        Route::post('/extend', [TrialController::class, 'extend'])->name('extend');
        Route::post('/cancel', [TrialController::class, 'cancel'])->name('cancel');
        Route::get('/status', [TrialController::class, 'status'])->name('status');
        Route::get('/eligibility', [TrialController::class, 'eligibility'])->name('eligibility');
        Route::get('/benefits', [TrialController::class, 'benefits'])->name('benefits');
        Route::get('/stats', [TrialController::class, 'stats'])->name('stats');
        Route::get('/expiring', [TrialController::class, 'expiring'])->name('expiring');
        Route::post('/send-reminders', [TrialController::class, 'sendReminders'])->name('send_reminders');
        Route::post('/dismiss-prompt', [TrialController::class, 'dismissPrompt'])->name('dismiss_prompt');
        Route::get('/should-show-prompt', [TrialController::class, 'shouldShowPrompt'])->name('should_show_prompt');
    });

    // Template Marketplace Routes
    Route::prefix('templates')->name('templates.')->group(function () {
        Route::get('/', [TemplateController::class, 'index'])->name('index');
        Route::get('/{slug}', [TemplateController::class, 'show'])->name('show');
        Route::get('/create', [TemplateController::class, 'create'])->name('create')->middleware('auth');
        Route::post('/create', [TemplateController::class, 'store'])->name('store')->middleware('auth');
        Route::get('/{template}/edit', [TemplateController::class, 'edit'])->name('edit')->middleware('auth');
        Route::put('/{template}/edit', [TemplateController::class, 'update'])->name('update')->middleware('auth');
        Route::post('/{template}/purchase', [TemplateController::class, 'purchase'])->name('purchase')->middleware('auth');
        Route::post('/{template}/download', [TemplateController::class, 'download'])->name('download')->middleware('auth');
        Route::post('/{template}/review', [TemplateController::class, 'review'])->name('review')->middleware('auth');
        Route::get('/my-templates', [TemplateController::class, 'myTemplates'])->name('my_templates')->middleware('auth');
        Route::get('/my-purchases', [TemplateController::class, 'myPurchases'])->name('my_purchases')->middleware('auth');
    });

    // AI Autopilot Routes
    Route::prefix('ai-autopilot')->name('ai-autopilot.')->middleware('auth')->group(function () {
        Route::get('/', [AIAutopilotController::class, 'index'])->name('index');
        Route::get('/create', [AIAutopilotController::class, 'createProject'])->name('create');
        Route::post('/generate', [AIAutopilotController::class, 'generateProject'])->name('generate');
        Route::post('/chat', [AIAutopilotController::class, 'chat'])->name('chat');
        
        // Project-specific AI features
        Route::prefix('/projects/{project}')->group(function () {
            Route::post('/optimize', [AIAutopilotController::class, 'optimizeProject'])->name('optimize');
            Route::post('/generate-tasks', [AIAutopilotController::class, 'generateTasks'])->name('generate_tasks');
            Route::post('/generate-budget', [AIAutopilotController::class, 'generateBudget'])->name('generate_budget');
            Route::post('/generate-report', [AIAutopilotController::class, 'generateReport'])->name('generate_report');
            Route::post('/analyze', [AIAutopilotController::class, 'analyzeProject'])->name('analyze');
            Route::post('/suggest-next-steps', [AIAutopilotController::class, 'suggestNextSteps'])->name('suggest_next_steps');
            Route::post('/auto-schedule', [AIAutopilotController::class, 'autoSchedule'])->name('auto_schedule');
        });
    });

    // Revenue Routes (Admin Only)
    Route::prefix('revenue')->name('revenue.')->middleware('admin')->group(function () {
        Route::get('/', [RevenueController::class, 'index'])->name('index');
        Route::get('/mrr', [RevenueController::class, 'getMRR'])->name('mrr');
        Route::get('/revenue-by-period', [RevenueController::class, 'getRevenueByPeriod'])->name('revenue_by_period');
        Route::get('/subscriptions-by-plan', [RevenueController::class, 'getSubscriptionsByPlan'])->name('subscriptions_by_plan');
        Route::get('/churn-rate', [RevenueController::class, 'getChurnRate'])->name('churn_rate');
        Route::get('/clv', [RevenueController::class, 'getCLV'])->name('clv');
        Route::get('/conversion-rates', [RevenueController::class, 'getConversionRates'])->name('conversion_rates');
        Route::get('/revenue-by-gateway', [RevenueController::class, 'getRevenueByGateway'])->name('revenue_by_gateway');
    });
});
