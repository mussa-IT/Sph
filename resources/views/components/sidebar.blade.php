<!-- Premium Sidebar Navigation -->
<aside 
    x-data="{ collapsed: false, mobileOpen: false }"
    class="fixed inset-y-0 left-0 z-50 flex flex-col bg-white border-r border-gray-200 shadow-xl transition-all duration-300 ease-in-out lg:relative lg:shadow-none"
    :class="{ 
        'w-64': !collapsed && !mobileOpen, 
        'w-16': collapsed && !mobileOpen, 
        'w-64': mobileOpen,
        '-translate-x-full lg:translate-x-0': !mobileOpen && !collapsed,
        'translate-x-0': mobileOpen || !collapsed
    }"
    @resize.window="if (window.innerWidth >= 1024) mobileOpen = false"
    @keydown.escape.window="mobileOpen = false"
>
    <!-- Mobile overlay -->
    <div 
        x-show="mobileOpen" 
        x-transition.opacity.duration.200ms
        class="fixed inset-0 bg-black/50 lg:hidden"
        @click="mobileOpen = false"
    ></div>

    <!-- Logo Section -->
    <div class="flex items-center justify-between p-4 border-b border-gray-200">
        <div class="flex items-center gap-3" x-show="!collapsed || mobileOpen">
            <div class="w-8 h-8 bg-gradient-to-br from-purple-600 to-blue-600 rounded-lg flex items-center justify-center">
                <span class="text-white font-bold text-sm">SP</span>
            </div>
            <div>
                <h1 class="text-lg font-bold text-gray-900">Smart Project Hub</h1>
                <p class="text-xs text-gray-500">AI-Powered Platform</p>
            </div>
        </div>
        
        <!-- Toggle Button -->
        <button 
            @click="collapsed ? collapsed = false : mobileOpen = !mobileOpen"
            class="p-2 rounded-lg hover:bg-gray-100 transition-colors lg:hidden"
        >
            <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
        
        <!-- Collapse Toggle (Desktop) -->
        <button 
            @click="collapsed = !collapsed"
            class="hidden lg:block p-2 rounded-lg hover:bg-gray-100 transition-colors"
        >
            <svg class="w-5 h-5 text-gray-600 transition-transform" :class="{ 'rotate-180': collapsed }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path>
            </svg>
        </button>
    </div>

    <!-- Navigation -->
    <nav class="flex-1 p-4 space-y-2 overflow-y-auto">
        <!-- Dashboard -->
        <a href="{{ route('dashboard') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
           :class="{ 
               'bg-purple-100 text-purple-700': request()->routeIs('dashboard'), 
               'text-gray-700 hover:bg-gray-100': !request()->routeIs('dashboard')
           }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Dashboard</span>
            <span x-show="request()->routeIs('dashboard') && (!collapsed || mobileOpen)" class="ml-auto w-2 h-2 bg-purple-600 rounded-full"></span>
        </a>

        <!-- My Projects -->
        <a href="{{ route('projects.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
           :class="{ 
               'bg-purple-100 text-purple-700': request()->routeIs('projects.*'), 
               'text-gray-700 hover:bg-gray-100': !request()->routeIs('projects.*')
           }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">My Projects</span>
            <span x-show="!collapsed || mobileOpen" class="ml-auto">
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-purple-600 text-white rounded-full">12</span>
            </span>
        </a>

        <!-- AI Builder -->
        <a href="{{ route('builder') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
           :class="{ 
               'bg-purple-100 text-purple-700': request()->routeIs('builder'), 
               'text-gray-700 hover:bg-gray-100': !request()->routeIs('builder')
           }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">AI Builder</span>
            <span x-show="request()->routeIs('builder') && (!collapsed || mobileOpen)" class="ml-auto w-2 h-2 bg-purple-600 rounded-full animate-pulse"></span>
        </a>

        <!-- Analytics -->
        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 group">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Analytics</span>
        </a>

        <!-- Team Workspace -->
        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 group">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Team Workspace</span>
        </a>

        <!-- Marketplace -->
        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 group">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Marketplace</span>
        </a>

        <!-- Billing/Subscription -->
        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 group">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Billing</span>
        </a>

        <!-- Onchain Activity -->
        <a href="{{ route('web3.profile') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
           :class="{ 
               'bg-purple-100 text-purple-700': request()->routeIs('web3.*'), 
               'text-gray-700 hover:bg-gray-100': !request()->routeIs('web3.*')
           }">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
            </svg>
            <span x-show="!collapsed || mobileOpen" class="transition-opacity duration-200">Onchain Activity</span>
        </a>

        <!-- Divider -->
        <div class="border-t border-gray-200 my-2" x-show="!collapsed || mobileOpen"></div>

        <!-- Notifications -->
        <a href="#" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-700 hover:bg-gray-100 transition-all duration-200 group"
           x-show="!collapsed || mobileOpen">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path>
            </svg>
            <span class="transition-opacity duration-200">Notifications</span>
            <span class="ml-auto">
                <span class="inline-flex items-center justify-center px-2 py-0.5 text-xs font-medium bg-red-500 text-white rounded-full">3</span>
            </span>
        </a>

        <!-- Settings -->
        <a href="{{ route('settings') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium transition-all duration-200 group"
           :class="{ 
               'bg-purple-100 text-purple-700': request()->routeIs('settings'), 
               'text-gray-700 hover:bg-gray-100': !request()->routeIs('settings')
           }"
           x-show="!collapsed || mobileOpen">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
            </svg>
            <span class="transition-opacity duration-200">Settings</span>
        </a>

        <!-- Admin (only if admin) -->
        @if(auth()->user()?->isAdmin())
        <a href="{{ route('admin.index') }}" 
           class="flex items-center gap-3 px-3 py-2.5 rounded-lg text-sm font-medium text-gray-600 hover:bg-gray-100 transition-all duration-200 group"
           x-show="!collapsed || mobileOpen">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
            </svg>
            <span class="transition-opacity duration-200">Admin</span>
        </a>
        @endif
    </nav>

    <!-- User Profile Section -->
    <div class="p-4 border-t border-gray-200" x-show="!collapsed || mobileOpen">
        <div class="flex items-center gap-3">
            <img src="https://picsum.photos/seed/user1/40/40.jpg" alt="User" class="w-10 h-10 rounded-full">
            <div class="flex-1 min-w-0">
                <p class="text-sm font-medium text-gray-900 truncate">{{ Auth::user()->name ?? 'John Doe' }}</p>
                <p class="text-xs text-gray-500 truncate">{{ Auth::user()->email ?? 'john@example.com' }}</p>
            </div>
            <button class="p-1 rounded-lg hover:bg-gray-100 transition-colors">
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
        </div>
        
        <div class="mt-3 flex items-center justify-between text-xs">
            <span class="text-gray-500">Storage</span>
            <span class="text-gray-700 font-medium">75%</span>
        </div>
        <div class="w-full bg-gray-200 rounded-full h-1.5 mt-1">
            <div class="bg-gradient-to-r from-purple-500 to-blue-500 h-1.5 rounded-full" style="width: 75%"></div>
        </div>
    </div>
</aside>
