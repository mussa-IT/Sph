import { Chart, registerables } from 'chart.js';

Chart.register(...registerables);

// Expose Chart globally for inline dashboard scripts
window.Chart = Chart;

// Dark mode detection helper for charts
window.isDarkMode = () => document.documentElement.classList.contains('dark');

// Chart color utilities
window.chartColors = {
    get foreground() { return window.isDarkMode() ? '#e2e8f0' : '#1e293b'; },
    get muted() { return window.isDarkMode() ? '#64748b' : '#94a3b8'; },
    get grid() { return window.isDarkMode() ? 'rgba(148,163,184,0.1)' : 'rgba(148,163,184,0.2)'; },
    get background() { return window.isDarkMode() ? '#0f172a' : '#ffffff'; },
    primary: '#7c3aed',
    secondary: '#3b82f6',
    emerald: '#10b981',
    amber: '#f59e0b',
    rose: '#f43f5e',
    violet: '#8b5cf6',
    cyan: '#06b6d4',
};
