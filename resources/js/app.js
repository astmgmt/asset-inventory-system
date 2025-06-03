// resources/js/app.js
import Chart from 'chart.js/auto';

document.addEventListener('DOMContentLoaded', () => {
    // Helper function to get text color based on dark mode
    function getTextColor(isDark) {
        return isDark ? '#f1f5f9' : '#000000'; // black default for light mode
    }

    // Store chart instances globally to update later
    const charts = {};

    // Initialize Asset Status Chart
    const assetStatusCtx = document.getElementById('assetStatusChart').getContext('2d');
    charts.assetStatusChart = new Chart(assetStatusCtx, {
        type: 'bar',
        data: {
            labels: ['Available', 'Borrowed', 'New', 'Damaged'],
            datasets: [{
                label: 'Asset Count',
                data: [120, 45, 30, 10],
                backgroundColor: ['#4CAF50', '#FFC107', '#2196F3', '#F44336'],
                borderColor: ['#388E3C', '#FF9800', '#1976D2', '#D32F2F'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                x: {
                    ticks: {
                        color: getTextColor(document.documentElement.classList.contains('dark'))
                    }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        color: getTextColor(document.documentElement.classList.contains('dark'))
                    }
                }
            },
            plugins: {
                legend: {
                    labels: {
                        color: getTextColor(document.documentElement.classList.contains('dark'))
                    }
                }
            }
        }
    });

    // Initialize Warranty Expiry Chart
    const warrantyExpiryCtx = document.getElementById('warrantyExpiryChart').getContext('2d');
    charts.warrantyExpiryChart = new Chart(warrantyExpiryCtx, {
        type: 'pie',
        data: {
            labels: ['Assets', 'Software'],
            datasets: [{
                label: 'Warranty Expiry',
                data: [60, 40],
                backgroundColor: ['#FFEB3B', '#8BC34A'],
                borderColor: ['#FBC02D', '#388E3C'],
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            plugins: {
                legend: {
                    labels: {
                        color: getTextColor(document.documentElement.classList.contains('dark'))
                    }
                }
            }
        }
    });

    // Listen for theme changes and update charts accordingly
    window.addEventListener('theme-changed', (event) => {
        const isDark = event.detail;
        console.log('Theme changed:', isDark ? 'dark' : 'light');
        Object.values(charts).forEach(chart => {
            // Update scales ticks color if they exist
            if (chart.options.scales) {
                for (const scaleKey in chart.options.scales) {
                    if (chart.options.scales[scaleKey].ticks) {
                        chart.options.scales[scaleKey].ticks.color = getTextColor(isDark);
                    }
                }
            }
            // Update legend label colors
            if (chart.options.plugins && chart.options.plugins.legend) {
                chart.options.plugins.legend.labels.color = getTextColor(isDark);
            }
            chart.update();
        });
    });
});
