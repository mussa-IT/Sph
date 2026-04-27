@extends('layouts.landing')

@section('content')
    @include('partials.public-navbar')

    <!-- Hero Section -->
    <section class="relative overflow-hidden pt-40 pb-28 lg:pt-56 lg:pb-40">
        <!-- Animated Gradient Background -->
        <div class="absolute inset-0 animate-gradient bg-gradient-to-br from-violet-600/25 via-blue-600/20 to-cyan-500/25 dark:from-violet-500/20 dark:via-blue-500/15 dark:to-cyan-400/20"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(139,92,246,0.2),_transparent_50%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(59,130,246,0.2),_transparent_50%)]"></div>
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-muted/30 to-transparent"></div>

        <div class="relative mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-4xl text-center scroll-reveal">
                <div class="mb-8 inline-flex items-center gap-2.5 rounded-full border border-primary/20 bg-primary/5 px-5 py-2 text-xs font-semibold uppercase tracking-widest text-primary backdrop-blur-sm dark:border-primary/30 dark:bg-primary/10">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-primary opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-primary"></span>
                    </span>
                    AI-Powered Project Management
                </div>
                <h1 class="text-5xl font-extrabold tracking-tight text-foreground sm:text-7xl lg:text-8xl dark:text-foreground-dark">
                    Turn Ideas Into<br class="hidden sm:block" />
                    <span class="bg-gradient-to-r from-violet-600 via-blue-500 to-cyan-400 bg-clip-text text-transparent dark:from-violet-400 dark:via-blue-400 dark:to-cyan-300">Real Projects With AI</span>
                </h1>
                <p class="mx-auto mt-8 max-w-2xl text-xl leading-relaxed text-muted/90 dark:text-muted-dark/90 scroll-reveal scroll-reveal-delay-1">
                    Describe your idea in plain language and watch our AI build your project structure,
                    assign tasks, estimate budgets, and generate everything you need to get started.
                </p>
                <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row scroll-reveal scroll-reveal-delay-2">
                    <a href="{{ route('register') }}" class="btn-gradient inline-flex h-14 items-center justify-center rounded-2xl bg-primary px-10 text-base font-bold text-primary-foreground transition-all duration-200 ease-out hover:shadow-xl hover:shadow-primary/30 hover:scale-[1.04] active:scale-[0.96]">
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="inline-flex h-14 items-center justify-center rounded-2xl border border-muted/30 bg-background/80 px-10 text-base font-semibold text-foreground backdrop-blur-sm transition-all duration-200 ease-out hover:bg-muted/10 hover:scale-[1.04] active:scale-[0.96] dark:border-muted-dark/30 dark:bg-background-dark/80 dark:text-foreground-dark dark:hover:bg-muted-dark/10">
                        Explore Demo
                    </a>
                </div>
                <p class="mt-6 text-sm text-muted/60 dark:text-muted-dark/60 scroll-reveal scroll-reveal-delay-3">No credit card required. Start building in seconds.</p>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section class="relative py-28 lg:py-36">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-20 scroll-reveal">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary mb-4">Features</p>
                <h2 class="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl dark:text-foreground-dark">Everything you need to<br class="hidden sm:block" /> build smarter</h2>
                <p class="mt-5 text-lg text-muted/80 dark:text-muted-dark/80">Powerful AI-driven tools designed for modern teams who move fast.</p>
            </div>
            <div class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                @foreach([
                    ['icon' => '🤖', 'title' => 'AI Chat Assistant', 'desc' => 'Ask questions, get suggestions, and let AI guide your project decisions in real-time.', 'from' => 'from-violet-500', 'to' => 'to-purple-500'],
                    ['icon' => '🛠️', 'title' => 'Smart Builder', 'desc' => 'Describe your idea and let AI generate project structures, tasks, and timelines automatically.', 'from' => 'from-blue-500', 'to' => 'to-cyan-500'],
                    ['icon' => '💰', 'title' => 'Budget Planner', 'desc' => 'AI estimates costs, tracks spending, and alerts you before budgets go off track.', 'from' => 'from-emerald-500', 'to' => 'to-green-500'],
                    ['icon' => '📚', 'title' => 'Resource Library', 'desc' => 'Store documents, links, and references. AI helps organize and surface what you need.', 'from' => 'from-amber-500', 'to' => 'to-orange-500'],
                    ['icon' => '✅', 'title' => 'Task Management', 'desc' => 'Create, assign, and track tasks. AI prioritizes work and suggests deadlines.', 'from' => 'from-rose-500', 'to' => 'to-pink-500'],
                    ['icon' => '📊', 'title' => 'Live Dashboard', 'desc' => 'Real-time insights into project health, team activity, and progress at a glance.', 'from' => 'from-indigo-500', 'to' => 'to-violet-500'],
                ] as $i => $feature)
                <div class="feature-card group relative scroll-reveal scroll-reveal-delay-{{ ($i % 3) + 1 }} rounded-2xl border border-muted/10 bg-background p-8 transition-all duration-300 hover:border-primary/30 hover:shadow-xl hover:-translate-y-1 dark:border-muted-dark/10 dark:bg-background-dark">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-gradient-to-br {{ $feature['from'] }}/15 {{ $feature['to'] }}/15 text-3xl transition-transform duration-300 group-hover:scale-110 dark:{{ $feature['from'] }}/25 dark:{{ $feature['to'] }}/25">
                        {{ $feature['icon'] }}
                    </div>
                    <h3 class="text-lg font-bold text-foreground dark:text-foreground-dark">{{ $feature['title'] }}</h3>
                    <p class="mt-3 text-sm leading-relaxed text-muted dark:text-muted-dark">{{ $feature['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section class="relative py-28 lg:py-36 bg-muted/[0.03] dark:bg-muted-dark/[0.03]">
        <div class="absolute inset-x-0 top-0 h-px bg-gradient-to-r from-transparent via-muted/20 to-transparent"></div>
        <div class="absolute inset-x-0 bottom-0 h-px bg-gradient-to-r from-transparent via-muted/20 to-transparent"></div>
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-20 scroll-reveal">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary mb-4">Process</p>
                <h2 class="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl dark:text-foreground-dark">How it works</h2>
                <p class="mt-5 text-lg text-muted/80 dark:text-muted-dark/80">From idea to execution in three simple steps.</p>
            </div>
            <div class="grid gap-10 lg:grid-cols-3">
                @foreach([
                    ['num' => '01', 'title' => 'Describe Your Idea', 'desc' => 'Tell our AI what you want to build using plain language. No templates or complex setup required.'],
                    ['num' => '02', 'title' => 'AI Builds the Plan', 'desc' => 'Our Smart Builder generates project structure, tasks, budgets, and timelines automatically.'],
                    ['num' => '03', 'title' => 'Launch & Iterate', 'desc' => 'Invite your team, track progress, and use AI chat to refine and improve as you go.'],
                ] as $i => $step)
                <div class="relative text-center scroll-reveal scroll-reveal-delay-{{ $i + 1 }}">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-primary to-secondary text-2xl font-black text-primary-foreground shadow-xl shadow-primary/20 transition-transform duration-500 hover:scale-110">
                        {{ $step['num'] }}
                    </div>
                    <h3 class="text-xl font-bold text-foreground dark:text-foreground-dark">{{ $step['title'] }}</h3>
                    <p class="mt-4 text-sm leading-relaxed text-muted/70 dark:text-muted-dark/70 max-w-xs mx-auto">{{ $step['desc'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="relative py-28 lg:py-36">
        <div class="mx-auto max-w-7xl px-6 lg:px-8">
            <div class="mx-auto max-w-2xl text-center mb-20 scroll-reveal">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary mb-4">Testimonials</p>
                <h2 class="text-4xl font-extrabold tracking-tight text-foreground sm:text-5xl dark:text-foreground-dark">Loved by builders</h2>
                <p class="mt-5 text-lg text-muted/80 dark:text-muted-dark/80">See what teams are saying about Smart Project Hub.</p>
            </div>
            <div class="grid gap-6 md:grid-cols-3">
                @foreach([
                    ['stars' => '⭐⭐⭐⭐⭐', 'quote' => 'We went from concept to a fully structured project in under 10 minutes. The AI builder understood exactly what we needed.', 'initials' => 'JD', 'name' => 'James D.', 'role' => 'Product Lead, TechStart', 'from' => 'from-violet-500', 'to' => 'to-purple-500'],
                    ['stars' => '⭐⭐⭐⭐⭐', 'quote' => 'The budget estimator saved us from a major overspend. AI flagged risks we had not even considered. Game changer.', 'initials' => 'AM', 'name' => 'Amina M.', 'role' => 'Operations Manager, BuildRight', 'from' => 'from-blue-500', 'to' => 'to-cyan-500'],
                    ['stars' => '⭐⭐⭐⭐⭐', 'quote' => 'Our team communicates better since we started using the AI chat. It feels like having a project manager available 24/7.', 'initials' => 'CK', 'name' => 'Chris K.', 'role' => 'Founder, GreenStack', 'from' => 'from-emerald-500', 'to' => 'to-green-500'],
                ] as $i => $t)
                <div class="scroll-reveal scroll-reveal-delay-{{ $i + 1 }} rounded-2xl border border-muted/10 bg-background p-8 shadow-card transition-all duration-500 hover:shadow-xl hover:-translate-y-1 dark:border-muted-dark/10 dark:bg-background-dark">
                    <div class="mb-5 text-lg tracking-widest">{{ $t['stars'] }}</div>
                    <p class="text-sm leading-relaxed text-muted/80 dark:text-muted-dark/80">"{{ $t['quote'] }}"</p>
                    <div class="mt-8 flex items-center gap-4">
                        <div class="h-11 w-11 rounded-full bg-gradient-to-br {{ $t['from'] }} {{ $t['to'] }} flex items-center justify-center text-xs font-bold text-white shadow-lg">
                            {{ $t['initials'] }}
                        </div>
                        <div>
                            <p class="text-sm font-bold text-foreground dark:text-foreground-dark">{{ $t['name'] }}</p>
                            <p class="text-xs text-muted/60 dark:text-muted-dark/60">{{ $t['role'] }}</p>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative py-20 lg:py-28">
        <div class="mx-auto max-w-5xl px-6 lg:px-8">
            <div class="relative overflow-hidden rounded-[2rem] bg-gradient-to-br from-primary via-secondary to-violet-600 p-12 text-center shadow-2xl shadow-primary/20 sm:p-20 scroll-reveal">
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_top_right,_rgba(255,255,255,0.15),_transparent_50%)]"></div>
                <div class="absolute inset-0 bg-[radial-gradient(circle_at_bottom_left,_rgba(255,255,255,0.08),_transparent_50%)]"></div>
                <div class="absolute -top-24 -right-24 h-64 w-64 rounded-full bg-white/5 blur-3xl"></div>
                <div class="relative">
                    <h2 class="text-4xl font-extrabold tracking-tight text-primary-foreground sm:text-5xl">Ready to build smarter?</h2>
                    <p class="mx-auto mt-6 max-w-lg text-lg text-primary-foreground/85 leading-relaxed">Join thousands of teams using AI to plan, build, and ship projects faster than ever before.</p>
                    <div class="mt-10 flex flex-col items-center justify-center gap-4 sm:flex-row">
                        <a href="{{ route('register') }}" class="btn-gradient inline-flex h-14 items-center justify-center rounded-2xl bg-primary-foreground px-10 text-base font-bold text-primary transition-all duration-200 ease-out hover:shadow-xl hover:scale-[1.04] active:scale-[0.96]">
                            Start Building Free
                        </a>
                        <a href="{{ route('login') }}" class="inline-flex h-14 items-center justify-center rounded-2xl border-2 border-primary-foreground/30 bg-transparent px-10 text-base font-bold text-primary-foreground transition-all duration-200 ease-out hover:bg-primary-foreground/10 hover:scale-[1.04] active:scale-[0.96]">
                            Sign In
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('partials.public-footer')

    <!-- Scroll Reveal Script -->
    <script>
        (function() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('revealed');
                        observer.unobserve(entry.target);
                    }
                });
            }, { threshold: 0.1, rootMargin: '0px 0px -50px 0px' });

            document.querySelectorAll('.scroll-reveal').forEach(el => observer.observe(el));
        })();
    </script>
@endsection
