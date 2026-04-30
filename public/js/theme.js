/**
 * Theme Management for Smart Project Hub
 * Handles dark mode toggle and localStorage persistence
 */

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dark mode
    initDarkMode();
});

function initDarkMode() {
    // Check for saved theme preference or default to light mode
    const savedTheme = localStorage.getItem('theme');
    const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
    
    if (savedTheme === 'dark' || (!savedTheme && prefersDark)) {
        document.documentElement.classList.add('dark');
    }

    // Create global dark mode toggle function
    window.$dark = {
        toggle: function() {
            const html = document.documentElement;
            const isDark = html.classList.contains('dark');
            
            if (isDark) {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            } else {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            }
            
            // Dispatch custom event for components that need to react to theme changes
            window.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: isDark ? 'light' : 'dark' } 
            }));
        },
        
        set: function(theme) {
            const html = document.documentElement;
            
            if (theme === 'dark') {
                html.classList.add('dark');
                localStorage.setItem('theme', 'dark');
            } else {
                html.classList.remove('dark');
                localStorage.setItem('theme', 'light');
            }
            
            window.dispatchEvent(new CustomEvent('themeChanged', { 
                detail: { theme: theme } 
            }));
        },
        
        get: function() {
            return document.documentElement.classList.contains('dark') ? 'dark' : 'light';
        }
    };

    // Listen for system theme changes
    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function(e) {
        if (!localStorage.getItem('theme')) {
            window.$dark.set(e.matches ? 'dark' : 'light');
        }
    });

    // Add keyboard shortcut for theme toggle (Ctrl/Cmd + Shift + D)
    document.addEventListener('keydown', function(e) {
        if ((e.ctrlKey || e.metaKey) && e.shiftKey && e.key === 'D') {
            e.preventDefault();
            window.$dark.toggle();
        }
    });
}
