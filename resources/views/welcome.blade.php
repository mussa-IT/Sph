@extends('layouts.landing')

@section('content')
    @include('partials.public-navbar')

    <!-- Hero Section -->
    <section class="hero-section">
        <div class="container">
            <div class="text-center animate-fade-in">
                <div class="mb-8 inline-flex items-center gap-2.5 rounded-full border border-white/20 bg-white/10 px-5 py-2 text-xs font-semibold uppercase tracking-widest text-white backdrop-blur-sm">
                    <span class="relative flex h-2 w-2">
                        <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-white opacity-60"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full bg-white"></span>
                    </span>
                    AI-Powered Project Management
                </div>
                <h1 class="text-5xl font-extrabold tracking-tight text-white sm:text-7xl lg:text-8xl">
                    Turn Ideas Into<br class="hidden sm:block" />
                    <span class="text-white">Real Projects With AI</span>
                </h1>
                <p class="mx-auto mt-8 max-w-2xl text-xl leading-relaxed text-white/90">
                    Describe your idea in plain language and watch our AI build your project structure,
                    assign tasks, estimate budgets, and generate everything you need to get started.
                </p>
                <div class="mt-12 flex flex-col items-center justify-center gap-4 sm:flex-row">
                    <a href="{{ route('register') }}" class="btn-primary">
                        Get Started Free
                    </a>
                    <a href="{{ route('login') }}" class="btn-secondary">
                        Explore Demo
                    </a>
                </div>
                <p class="mt-6 text-sm text-white/60">No credit card required. Start building in seconds.</p>
            </div>
        </div>
    </section>

    <!-- Features Grid -->
    <section>
        <div class="container">
            <div class="text-center mb-20 animate-fade-in">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary mb-4">Features</p>
                <h2 class="text-4xl font-extrabold tracking-tight">Everything you need to<br class="hidden sm:block" /> build smarter</h2>
                <p class="mt-5 text-lg text-gray-600">Powerful AI-driven tools designed for modern teams who move fast.</p>
            </div>
            <div class="grid grid-cols-3">
                <!-- Purple Card -->
                <div class="feature-card card-purple animate-fade-in">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl">
                        🤖
                    </div>
                    <h3>AI Chat Assistant</h3>
                    <p class="mt-3 text-sm leading-relaxed">Ask questions, get suggestions, and let AI guide your project decisions in real-time.</p>
                </div>
                
                <!-- Dark Blue Card -->
                <div class="feature-card card-dark-blue animate-fade-in" style="animation-delay: 0.1s">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl">
                        🛠️
                    </div>
                    <h3>Smart Builder</h3>
                    <p class="mt-3 text-sm leading-relaxed">Describe your idea and let AI generate project structures, tasks, and timelines automatically.</p>
                </div>
                
                <!-- Light Blue Card -->
                <div class="feature-card card-light-blue animate-fade-in" style="animation-delay: 0.2s">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-white/20 text-3xl">
                        💰
                    </div>
                    <h3>Budget Planner</h3>
                    <p class="mt-3 text-sm leading-relaxed">AI estimates costs, tracks spending, and alerts you before budgets go off track.</p>
                </div>
                
                <!-- White Card with Purple Accent -->
                <div class="feature-card card-white animate-fade-in" style="animation-delay: 0.3s">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-purple-100 text-3xl">
                        📚
                    </div>
                    <h3>Resource Library</h3>
                    <p class="mt-3 text-sm leading-relaxed">Store documents, links, and references. AI helps organize and surface what you need.</p>
                </div>
                
                <!-- White Card with Dark Blue Accent -->
                <div class="feature-card card-white animate-fade-in" style="animation-delay: 0.4s">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-blue-100 text-3xl">
                        ✅
                    </div>
                    <h3>Task Management</h3>
                    <p class="mt-3 text-sm leading-relaxed">Create, assign, and track tasks. AI prioritizes work and suggests deadlines.</p>
                </div>
                
                <!-- White Card with Light Blue Accent -->
                <div class="feature-card card-white animate-fade-in" style="animation-delay: 0.5s">
                    <div class="feature-icon mb-5 inline-flex h-14 w-14 items-center justify-center rounded-2xl bg-cyan-100 text-3xl">
                        📊
                    </div>
                    <h3>Live Dashboard</h3>
                    <p class="mt-3 text-sm leading-relaxed">Real-time insights into project health, team activity, and progress at a glance.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- How It Works -->
    <section>
        <div class="container">
            <div class="text-center mb-20 animate-fade-in">
                <p class="text-sm font-semibold uppercase tracking-[0.2em] text-primary mb-4">Process</p>
                <h2 class="text-4xl font-extrabold tracking-tight">How it works</h2>
                <p class="mt-5 text-lg text-gray-600">From idea to execution in three simple steps.</p>
            </div>
            <div class="grid grid-cols-3">
                <!-- Step 1 -->
                <div class="text-center animate-fade-in">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-primary to-secondary text-2xl font-black text-white shadow-xl transition-transform duration-500 hover:scale-110">
                        01
                    </div>
                    <h3 class="text-xl font-bold">Describe Your Idea</h3>
                    <p class="mt-4 text-sm leading-relaxed text-gray-600 max-w-xs mx-auto">Tell our AI what you want to build using plain language. No templates or complex setup required.</p>
                </div>
                
                <!-- Step 2 -->
                <div class="text-center animate-fade-in" style="animation-delay: 0.1s">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-secondary to-accent text-2xl font-black text-white shadow-xl transition-transform duration-500 hover:scale-110">
                        02
                    </div>
                    <h3 class="text-xl font-bold">AI Builds the Plan</h3>
                    <p class="mt-4 text-sm leading-relaxed text-gray-600 max-w-xs mx-auto">Our Smart Builder generates project structure, tasks, budgets, and timelines automatically.</p>
                </div>
                
                <!-- Step 3 -->
                <div class="text-center animate-fade-in" style="animation-delay: 0.2s">
                    <div class="mx-auto mb-8 flex h-20 w-20 items-center justify-center rounded-3xl bg-gradient-to-br from-accent to-purple text-2xl font-black text-white shadow-xl transition-transform duration-500 hover:scale-110">
                        03
                    </div>
                    <h3 class="text-xl font-bold">Launch & Iterate</h3>
                    <p class="mt-4 text-sm leading-relaxed text-gray-600 max-w-xs mx-auto">Invite your team, track progress, and use AI chat to refine and improve as you go.</p>
                </div>
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
