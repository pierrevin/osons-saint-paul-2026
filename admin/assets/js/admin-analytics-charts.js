/**
 * Gestion des graphiques Google Analytics
 * Utilise Chart.js pour afficher les données de manière visuelle
 */

const AdminAnalytics = {
    charts: {
        timeSeries: null,
        trafficSources: null
    },
    
    currentPeriod: 30,
    
    /**
     * Initialisation des graphiques
     */
    init: function() {
        // Attendre que le DOM soit chargé et que les données soient disponibles
        if (typeof window.analyticsData === 'undefined') {
            return;
        }
        
        this.currentPeriod = window.analyticsData.currentPeriod || 30;
        
        // Initialiser le graphique d'évolution temporelle
        this.initTimeSeriesChart();
        
        // Initialiser le graphique des sources de trafic
        this.initTrafficSourcesChart();
    },
    
    /**
     * Graphique d'évolution temporelle (ligne)
     */
    initTimeSeriesChart: function() {
        const canvas = document.getElementById('analyticsTimeSeriesChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const data = window.analyticsData.timeSeries[this.currentPeriod];
        
        // Détruire le graphique existant s'il existe
        if (this.charts.timeSeries) {
            this.charts.timeSeries.destroy();
        }
        
        this.charts.timeSeries = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Visiteurs',
                        data: data.users,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#3b82f6',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    },
                    {
                        label: 'Pages vues',
                        data: data.pageviews,
                        borderColor: '#10b981',
                        backgroundColor: 'rgba(16, 185, 129, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 5,
                        pointBackgroundColor: '#10b981',
                        pointBorderColor: '#fff',
                        pointBorderWidth: 2
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 2.5,
                interaction: {
                    mode: 'index',
                    intersect: false
                },
                plugins: {
                    legend: {
                        display: true,
                        position: 'top',
                        labels: {
                            usePointStyle: true,
                            padding: 15,
                            font: {
                                size: 13,
                                weight: '500'
                            }
                        }
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        bodySpacing: 6,
                        usePointStyle: true,
                        callbacks: {
                            label: function(context) {
                                let label = context.dataset.label || '';
                                if (label) {
                                    label += ': ';
                                }
                                label += context.parsed.y.toLocaleString('fr-FR');
                                return label;
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        grid: {
                            display: false
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            maxRotation: 45,
                            minRotation: 0
                        }
                    },
                    y: {
                        beginAtZero: true,
                        grid: {
                            color: 'rgba(0, 0, 0, 0.05)'
                        },
                        ticks: {
                            font: {
                                size: 11
                            },
                            callback: function(value) {
                                return value.toLocaleString('fr-FR');
                            }
                        }
                    }
                }
            }
        });
    },
    
    /**
     * Graphique des sources de trafic (donut)
     */
    initTrafficSourcesChart: function() {
        const canvas = document.getElementById('trafficSourcesChart');
        if (!canvas) return;
        
        const ctx = canvas.getContext('2d');
        const sources = window.analyticsData.trafficSources;
        
        // Détruire le graphique existant s'il existe
        if (this.charts.trafficSources) {
            this.charts.trafficSources.destroy();
        }
        
        // Préparer les données
        const labels = sources.map(s => s.source);
        const data = sources.map(s => s.sessions);
        
        // Palette de couleurs moderne
        const colors = [
            '#3b82f6', // Bleu
            '#10b981', // Vert
            '#f59e0b', // Orange
            '#ef4444', // Rouge
            '#8b5cf6', // Violet
            '#ec4899', // Rose
            '#06b6d4', // Cyan
            '#84cc16'  // Lime
        ];
        
        this.charts.trafficSources = new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    backgroundColor: colors.slice(0, data.length),
                    borderWidth: 2,
                    borderColor: '#fff',
                    hoverBorderWidth: 3,
                    hoverOffset: 10
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
                aspectRatio: 1.5,
                plugins: {
                    legend: {
                        display: false // On affiche déjà une liste en dessous
                    },
                    tooltip: {
                        backgroundColor: 'rgba(0, 0, 0, 0.8)',
                        padding: 12,
                        titleFont: {
                            size: 14,
                            weight: 'bold'
                        },
                        bodyFont: {
                            size: 13
                        },
                        callbacks: {
                            label: function(context) {
                                const label = context.label || '';
                                const value = context.parsed;
                                const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                const percentage = ((value / total) * 100).toFixed(1);
                                return `${label}: ${value} sessions (${percentage}%)`;
                            }
                        }
                    }
                },
                cutout: '65%'
            }
        });
    },
    
    /**
     * Changer la période d'analyse
     */
    changePeriod: function(days) {
        this.currentPeriod = days;
        
        // Mettre à jour les boutons actifs
        document.querySelectorAll('.period-btn').forEach(btn => {
            btn.classList.remove('active');
            if (btn.getAttribute('data-period') == days) {
                btn.classList.add('active');
            }
        });
        
        // Mettre à jour le graphique d'évolution
        this.updateTimeSeriesChart(days);
        
        // Mettre à jour les KPI
        this.updateKPIs(days);
    },
    
    /**
     * Mettre à jour le graphique d'évolution
     */
    updateTimeSeriesChart: function(days) {
        const data = window.analyticsData.timeSeries[days];
        
        if (this.charts.timeSeries && data) {
            this.charts.timeSeries.data.labels = data.labels;
            this.charts.timeSeries.data.datasets[0].data = data.users;
            this.charts.timeSeries.data.datasets[1].data = data.pageviews;
            this.charts.timeSeries.update('active');
        }
    },
    
    /**
     * Mettre à jour les indicateurs KPI
     */
    updateKPIs: function(days) {
        const data = window.analyticsData.timeSeries[days];
        
        if (!data) return;
        
        // Calculer les totaux
        const totalUsers = data.users.reduce((a, b) => a + b, 0);
        const totalPageviews = data.pageviews.reduce((a, b) => a + b, 0);
        
        // Mettre à jour les valeurs affichées avec animation
        this.animateValue('kpi-visitors', totalUsers);
        this.animateValue('kpi-pageviews', totalPageviews);
    },
    
    /**
     * Animer le changement de valeur d'un KPI
     */
    animateValue: function(elementId, newValue) {
        const element = document.getElementById(elementId);
        if (!element) return;
        
        const currentValue = parseInt(element.textContent.replace(/\s/g, '')) || 0;
        const duration = 500; // ms
        const steps = 30;
        const stepValue = (newValue - currentValue) / steps;
        const stepDuration = duration / steps;
        
        let currentStep = 0;
        
        const interval = setInterval(() => {
            currentStep++;
            const value = Math.round(currentValue + (stepValue * currentStep));
            element.textContent = value.toLocaleString('fr-FR');
            
            if (currentStep >= steps) {
                element.textContent = newValue.toLocaleString('fr-FR');
                clearInterval(interval);
            }
        }, stepDuration);
    }
};

// Initialiser les graphiques quand le DOM est chargé
document.addEventListener('DOMContentLoaded', function() {
    // Attendre un peu pour être sûr que les données sont chargées
    setTimeout(() => {
        AdminAnalytics.init();
    }, 100);
});

// Réinitialiser les graphiques quand on navigue vers le dashboard
document.addEventListener('sectionChanged', function(e) {
    if (e.detail && e.detail.section === 'dashboard') {
        setTimeout(() => {
            AdminAnalytics.init();
        }, 100);
    }
});

