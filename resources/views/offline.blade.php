<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - SmartProjectHub</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.5; }
        }
        .pulse-animation {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="max-w-md w-full">
        <div class="text-center">
            <!-- Offline Icon -->
            <div class="mx-auto w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center mb-6">
                <svg class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-2xl font-bold text-gray-900 mb-2">You're Offline</h1>
            
            <!-- Description -->
            <p class="text-gray-600 mb-6">
                It looks like you've lost your internet connection. Some features may not be available until you're back online.
            </p>

            <!-- Connection Status -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-500">Connection Status</span>
                    <span class="flex items-center text-sm text-red-600">
                        <span class="w-2 h-2 bg-red-600 rounded-full mr-2 pulse-animation"></span>
                        Disconnected
                    </span>
                </div>
            </div>

            <!-- Available Offline Features -->
            <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-4 mb-6">
                <h3 class="font-semibold text-gray-900 mb-3">Available Offline</h3>
                <ul class="space-y-2 text-sm text-gray-600">
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        View cached projects
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Edit cached tasks
                    </li>
                    <li class="flex items-center">
                        <svg class="w-4 h-4 text-green-500 mr-2" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                        </svg>
                        Read cached messages
                    </li>
                </ul>
            </div>

            <!-- Retry Button -->
            <button 
                onclick="window.location.reload()" 
                class="w-full bg-blue-600 text-white py-3 px-4 rounded-lg font-medium hover:bg-blue-700 transition-colors duration-200 mb-4"
            >
                Try Again
            </button>

            <!-- Help Text -->
            <p class="text-xs text-gray-500">
                Your changes will be automatically synced when you reconnect.
            </p>
        </div>
    </div>

    <script>
        // Monitor connection status
        function updateConnectionStatus() {
            const statusElement = document.querySelector('.flex.items-center.justify-between span:last-child');
            const pulseElement = document.querySelector('.pulse-animation');
            
            if (navigator.onLine) {
                statusElement.innerHTML = `
                    <span class="w-2 h-2 bg-green-600 rounded-full mr-2"></span>
                    Connected
                `;
                statusElement.className = 'flex items-center text-sm text-green-600';
                pulseElement.classList.remove('pulse-animation');
                
                // Auto-reload when connection is restored
                setTimeout(() => {
                    window.location.reload();
                }, 1000);
            } else {
                statusElement.innerHTML = `
                    <span class="w-2 h-2 bg-red-600 rounded-full mr-2 pulse-animation"></span>
                    Disconnected
                `;
                statusElement.className = 'flex items-center text-sm text-red-600';
                pulseElement.classList.add('pulse-animation');
            }
        }

        // Listen for connection changes
        window.addEventListener('online', updateConnectionStatus);
        window.addEventListener('offline', updateConnectionStatus);

        // Initial status check
        updateConnectionStatus();

        // Periodic connection check
        setInterval(updateConnectionStatus, 5000);
    </script>
</body>
</html>
