// Charts and Analytics JavaScript for Student Management System

class SMSCharts {
    constructor() {
        this.charts = {};
        this.colors = {
            primary: '#667eea',
            secondary: '#764ba2',
            success: '#28a745',
            warning: '#ffc107',
            danger: '#dc3545',
            info: '#17a2b8',
            light: '#f8f9fa',
            dark: '#343a40'
        };
    }

    // Initialize all charts on page
    init() {
        this.initAttendanceChart();
        this.initPerformanceChart();
        this.initSubjectChart();
        this.initClassDistributionChart();
    }

    // Attendance Chart
    initAttendanceChart() {
        const ctx = document.getElementById('attendanceChart');
        if (!ctx) return;

        this.charts.attendance = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'],
                datasets: [{
                    label: 'Present',
                    data: [85, 78, 92, 88, 76, 65],
                    backgroundColor: this.colors.success,
                    borderColor: this.colors.success,
                    borderWidth: 1
                }, {
                    label: 'Absent',
                    data: [15, 22, 8, 12, 24, 35],
                    backgroundColor: this.colors.danger,
                    borderColor: this.colors.danger,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Percentage (%)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    },
                    tooltip: {
                        mode: 'index',
                        intersect: false
                    }
                }
            }
        });
    }

    // Performance Chart
    initPerformanceChart() {
        const ctx = document.getElementById('performanceChart');
        if (!ctx) return;

        this.charts.performance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Unit Test 1', 'Mid Term', 'Unit Test 2', 'Final Exam'],
                datasets: [{
                    label: 'Mathematics',
                    data: [75, 82, 78, 85],
                    borderColor: this.colors.primary,
                    backgroundColor: this.hexToRgba(this.colors.primary, 0.1),
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Physics',
                    data: [68, 75, 80, 78],
                    borderColor: this.colors.success,
                    backgroundColor: this.hexToRgba(this.colors.success, 0.1),
                    tension: 0.4,
                    fill: true
                }, {
                    label: 'Chemistry',
                    data: [72, 78, 75, 80],
                    borderColor: this.colors.warning,
                    backgroundColor: this.hexToRgba(this.colors.warning, 0.1),
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        max: 100,
                        title: {
                            display: true,
                            text: 'Marks (%)'
                        }
                    }
                },
                plugins: {
                    legend: {
                        position: 'top',
                    }
                }
            }
        });
    }

    // Subject Distribution Chart
    initSubjectChart() {
        const ctx = document.getElementById('subjectChart');
        if (!ctx) return;

        this.charts.subjects = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Mathematics', 'Physics', 'Chemistry', 'English', 'Computer Science'],
                datasets: [{
                    data: [25, 20, 18, 22, 15],
                    backgroundColor: [
                        this.colors.primary,
                        this.colors.success,
                        this.colors.warning,
                        this.colors.info,
                        this.colors.secondary
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed}%`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Class Distribution Chart
    initClassDistributionChart() {
        const ctx = document.getElementById('classChart');
        if (!ctx) return;

        this.charts.classDistribution = new Chart(ctx, {
            type: 'pie',
            data: {
                labels: ['First Year', 'Second Year', 'Third Year', 'Final Year'],
                datasets: [{
                    data: [120, 95, 88, 76],
                    backgroundColor: [
                        this.hexToRgba(this.colors.primary, 0.8),
                        this.hexToRgba(this.colors.success, 0.8),
                        this.hexToRgba(this.colors.warning, 0.8),
                        this.hexToRgba(this.colors.info, 0.8)
                    ],
                    borderWidth: 2,
                    borderColor: '#ffffff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return `${context.label}: ${context.parsed} students`;
                            }
                        }
                    }
                }
            }
        });
    }

    // Utility function to convert hex to rgba
    hexToRgba(hex, alpha) {
        const r = parseInt(hex.slice(1, 3), 16);
        const g = parseInt(hex.slice(3, 5), 16);
        const b = parseInt(hex.slice(5, 7), 16);
        return `rgba(${r}, ${g}, ${b}, ${alpha})`;
    }

    // Update chart data dynamically
    updateChart(chartId, newData) {
        if (this.charts[chartId]) {
            this.charts[chartId].data = newData;
            this.charts[chartId].update();
        }
    }

    // Destroy a specific chart
    destroyChart(chartId) {
        if (this.charts[chartId]) {
            this.charts[chartId].destroy();
            delete this.charts[chartId];
        }
    }

    // Export chart as image
    exportChart(chartId, filename = 'chart') {
        if (this.charts[chartId]) {
            const link = document.createElement('a');
            link.download = `${filename}.png`;
            link.href = this.charts[chartId].toBase64Image();
            link.click();
        }
    }

    // Generate random data for demo purposes
    generateDemoData(count, min, max) {
        return Array.from({ length: count }, () => 
            Math.floor(Math.random() * (max - min + 1)) + min
        );
    }
}

// Initialize charts when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    const smsCharts = new SMSCharts();
    smsCharts.init();
    
    // Make available globally for debugging
    window.smsCharts = smsCharts;
});

// Additional chart utilities
const ChartUtils = {
    // Create a progress chart for single value
    createProgressChart(canvasId, value, max = 100) {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        const percentage = (value / max) * 100;
        let color = '#dc3545'; // red
        if (percentage >= 75) color = '#28a745'; // green
        else if (percentage >= 50) color = '#ffc107'; // yellow
        
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [percentage, 100 - percentage],
                    backgroundColor: [color, '#e9ecef'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '70%',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: { enabled: false }
                }
            }
        });
    },

    // Create a simple bar chart
    createBarChart(canvasId, labels, data, color = '#667eea') {
        const ctx = document.getElementById(canvasId);
        if (!ctx) return;
        
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: color,
                    borderColor: color,
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true }
                },
                plugins: {
                    legend: { display: false }
                }
            }
        });
    }
};