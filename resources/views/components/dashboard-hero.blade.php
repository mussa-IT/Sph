@props([
    'userName' => 'User',
    'totalProjects' => 0,
    'completedProjects' => 0,
    'chatSessions' => 0
])

<section class="mb-12 animate-fade-in-up">
    <div class="premium-card p-8 bg-gradient-to-br from-blue-600 via-purple-600 to-indigo-700 text-white relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute top-0 right-0 w-96 h-96 bg-white rounded-full -translate-y-48 translate-x-48"></div>
            <div class="absolute bottom-0 left-0 w-72 h-72 bg-white rounded-full translate-y-36 -translate-x-36"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-gradient-to-br from-white/20 to-transparent rounded-full blur-3xl"></div>
        </div>
        
        <!-- Content -->
        <div class="relative z-10">
            <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-8">
                <div class="flex-1">
                    <div class="mb-6">
                        <span class="inline-flex items-center px-3 py-1 rounded-full bg-white/20 text-white text-sm font-medium backdrop-blur-sm">
                            Welcome back
                        </span>
                    </div>
                    <h1 class="text-4xl lg:text-5xl font-bold mb-4 leading-tight">
                        Hello, {{ $userName }}! 👋
                    </h1>
                    <p class="text-xl text-white/90 mb-8 max-w-2xl">
                        Your AI-powered workspace is ready. Let's build something amazing today.
                    </p>
                    <div class="flex flex-wrap gap-4">
                        <button class="btn bg-white text-blue-600 hover:bg-white/90 shadow-lg hover:shadow-xl transform hover:-translate-y-1 transition-all duration-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path>
                            </svg>
                            Create Project
                        </button>
                        <button class="btn bg-white/20 text-white hover:bg-white/30 backdrop-blur-sm border border-white/30">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path>
                            </svg>
                            Ask AI
                        </button>
                    </div>
                </div>
                
                <!-- Quick Stats -->
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 lg:gap-8">
                    <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $totalProjects }}</div>
                        <div class="text-sm text-white/80">Active Projects</div>
                    </div>
                    <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $completedProjects }}</div>
                        <div class="text-sm text-white/80">Completed</div>
                    </div>
                    <div class="text-center bg-white/10 backdrop-blur-sm rounded-2xl p-6 border border-white/20">
                        <div class="text-3xl lg:text-4xl font-bold mb-2">{{ $chatSessions }}</div>
                        <div class="text-sm text-white/80">AI Chats</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
