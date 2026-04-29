/**
 * Dashboard JavaScript Module
 * Extracted from dashboard.blade.php for better organization
 */

// Chart initialization functions
function initializeCharts() {
    // Common chart options
    const chartColors = {
        primary: '#9333ea',
        secondary: '#2563eb',
        success: '#22c55e',
        warning: '#f59e0b',
        danger: '#ef4444',
        gray: '#6b7280'
    };

    const commonOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                position: 'top',
                labels: {
                    font: { family: 'Inter', size: 12 },
                    color: chartColors.gray,
                    padding: 15
                }
            },
            tooltip: {
                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                titleFont: { family: 'Inter', size: 14 },
                bodyFont: { family: 'Inter', size: 12 },
                padding: 12,
                cornerRadius: 8
            }
        },
        scales: {
            x: {
                grid: { display: false },
                ticks: { 
                    font: { family: 'Inter', size: 11 },
                    color: chartColors.gray 
                }
            },
            y: {
                grid: { 
                    color: 'rgba(0, 0, 0, 0.05)',
                    drawBorder: false 
                },
                ticks: { 
                    font: { family: 'Inter', size: 11 },
                    color: chartColors.gray 
                }
            }
        }
    };

    // Weekly Signups Chart
    const weeklySignupsCtx = document.getElementById('weeklySignupsChart');
    if (weeklySignupsCtx) {
        new Chart(weeklySignupsCtx, {
            type: 'bar',
            data: {
                labels: ['Week 1', 'Week 2', 'Week 3', 'Week 4', 'Week 5', 'Week 6'],
                datasets: [{
                    label: 'New Users',
                    data: [12, 19, 15, 25, 22, 30],
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: '#9333ea',
                    borderWidth: 2,
                    borderRadius: 8,
                    barThickness: 40
                }]
            },
            options: commonOptions
        });
    }

    // Project Completion Chart
    const projectCompletionCtx = document.getElementById('projectCompletionChart');
    if (projectCompletionCtx) {
        new Chart(projectCompletionCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Completed',
                    data: [8, 12, 15, 18, 22, 28],
                    borderColor: 'rgba(34, 197, 94, 1)',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgba(34, 197, 94, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }, {
                    label: 'Total',
                    data: [10, 15, 18, 22, 28, 35],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 5,
                    pointHoverRadius: 7,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        beginAtZero: true
                    }
                }
            }
        });
    }

    // AI Usage Chart
    const aiUsageCtx = document.getElementById('aiUsageChart');
    if (aiUsageCtx) {
        new Chart(aiUsageCtx, {
            type: 'doughnut',
            data: {
                labels: ['Code Generation', 'Data Analysis', 'Content Creation', 'Problem Solving'],
                datasets: [{
                    data: [35, 25, 20, 20],
                    backgroundColor: [
                        'rgba(147, 51, 234, 0.8)',
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(34, 197, 94, 0.8)',
                        'rgba(245, 158, 11, 0.8)'
                    ],
                    borderColor: '#fff',
                    borderWidth: 2,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '70%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 15,
                            font: { family: 'Inter', size: 11 },
                            color: chartColors.gray
                        }
                    },
                    tooltip: {
                        ...commonOptions.plugins.tooltip,
                        callbacks: {
                            label: function(context) {
                                return context.label + ': ' + context.parsed + '%';
                            }
                        }
                    }
                }
            }
        });
    }

    // Revenue Chart
    const revenueCtx = document.getElementById('revenueChart');
    if (revenueCtx) {
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
                datasets: [{
                    label: 'Revenue',
                    data: [12000, 15000, 18000, 22000, 28000, 32000],
                    borderColor: 'rgba(59, 130, 246, 1)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    borderWidth: 3,
                    fill: true,
                    tension: 0.4,
                    pointRadius: 4,
                    pointHoverRadius: 6,
                    pointBackgroundColor: 'rgba(59, 130, 246, 1)',
                    pointBorderColor: '#fff',
                    pointBorderWidth: 2
                }]
            },
            options: {
                ...commonOptions,
                interaction: { intersect: false, mode: 'index' },
                scales: {
                    ...commonOptions.scales,
                    y: {
                        ...commonOptions.scales.y,
                        beginAtZero: true,
                        ticks: {
                            ...commonOptions.scales.y.ticks,
                            callback: function(value) {
                                return '$' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });
    }
}

// Interactive features
function addInteractiveFeatures() {
    // Add smooth hover effects to cards
    const cards = document.querySelectorAll('.premium-card');
    cards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-4px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Add click handlers for buttons
    const buttons = document.querySelectorAll('.btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            // Add ripple effect
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add search functionality
    const searchInput = document.querySelector('input[placeholder*="Search"]');
    if (searchInput) {
        searchInput.addEventListener('input', function(e) {
            const searchTerm = e.target.value.toLowerCase();
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                const text = row.textContent.toLowerCase();
                row.style.display = text.includes(searchTerm) ? '' : 'none';
            });
        });
    }

    // Add filter functionality
    const statusFilter = document.querySelector('select');
    if (statusFilter) {
        statusFilter.addEventListener('change', function(e) {
            const filterValue = e.target.value;
            const rows = document.querySelectorAll('tbody tr');
            
            rows.forEach(row => {
                if (filterValue === 'All Status') {
                    row.style.display = '';
                } else {
                    const badge = row.querySelector('.badge');
                    const status = badge ? badge.textContent.trim() : '';
                    row.style.display = status.includes(filterValue) ? '' : 'none';
                }
            });
        });
    }
}

// Animation initialization
function initializeAnimations() {
    // Add staggered animation to elements
    const animatedElements = document.querySelectorAll('.animate-fade-in-up');
    animatedElements.forEach((element, index) => {
        element.style.animationDelay = `${index * 0.1}s`;
    });

    // Add intersection observer for scroll animations
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('animate-fade-in-up');
            }
        });
    });

    document.querySelectorAll('.premium-card').forEach(card => {
        observer.observe(card);
    });
}

// Initialize everything when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    initializeCharts();
    addInteractiveFeatures();
    initializeAnimations();
});

// Add CSS for ripple effect
const style = document.createElement('style');
style.textContent = `
    .btn {
        position: relative;
        overflow: hidden;
    }
    
    .ripple {
        position: absolute;
        border-radius: 50%;
        background: rgba(255, 255, 255, 0.6);
        transform: scale(0);
        animation: ripple-animation 0.6s ease-out;
        pointer-events: none;
    }
    
    @keyframes ripple-animation {
        to {
            transform: scale(4);
            opacity: 0;
        }
    }
    
    .premium-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }
    
    .premium-card:hover {
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
    }
    
    .badge {
        transition: all 0.2s ease;
    }
    
    .badge:hover {
        transform: scale(1.05);
    }
`;
document.head.appendChild(style);

// Export functions for global access if needed
window.DashboardJS = {
    initializeCharts,
    addInteractiveFeatures,
    initializeAnimations
};
