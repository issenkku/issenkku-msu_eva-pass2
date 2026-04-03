<script>
    document.addEventListener('DOMContentLoaded', function() {
        const statusChartConfig = @json($evaluateeOverview['statusChart']);
        const statusChartCanvas = document.getElementById(statusChartConfig.id);
        const statusChartCenterValue = document.getElementById('statusChartCenterValue');
        const statusChartCenterLabel = document.getElementById('statusChartCenterLabel');
        const overviewCards = Array.from(document.querySelectorAll('.evaluation-overview-card'));
        const clearFilterButtons = Array.from(document.querySelectorAll(
            '#evaluationClearFilterTop, #evaluationClearFilter, #evaluationClearFilterBottom'
        ));
        let statusChartInstance = null;

        const resetStatusChartCenter = () => {
            if (statusChartCenterValue) {
                statusChartCenterValue.textContent = statusChartConfig.centerValue;
            }
            if (statusChartCenterLabel) {
                statusChartCenterLabel.textContent = statusChartConfig.centerLabel;
            }
        };

        const updateStatusChartCenter = (chart, element) => {
            if (!statusChartCenterValue || !statusChartCenterLabel || !element) {
                return;
            }

            const index = element.index;
            const value = chart.data.datasets[0].data[index];
            const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

            statusChartCenterValue.textContent = `${percent}%`;
            statusChartCenterLabel.textContent = `${chart.data.labels[index]} ${value} รายการ`;
        };

        const syncOverviewSelection = (status = 'all') => {
            const activeIndex = overviewCards.findIndex((card) => card.dataset.statusFilter === status);

            overviewCards.forEach((card, index) => {
                const isActive = index === activeIndex && status !== 'all';
                card.classList.toggle('is-active', isActive);
                card.setAttribute('aria-pressed', isActive ? 'true' : 'false');
            });

            if (statusChartInstance) {
                statusChartInstance.setActiveElements(activeIndex >= 0 ? [{
                    datasetIndex: 0,
                    index: activeIndex,
                }] : []);
                statusChartInstance.update('none');
            }

            if (activeIndex >= 0 && statusChartInstance) {
                updateStatusChartCenter(statusChartInstance, { index: activeIndex });
            } else {
                resetStatusChartCenter();
            }

            clearFilterButtons.forEach((button) => {
                button.disabled = status === 'all';
                button.classList.toggle('cursor-not-allowed', status === 'all');
                button.classList.toggle('opacity-60', status === 'all');
            });
        };

        if (statusChartCanvas) {
            statusChartInstance = new Chart(statusChartCanvas, {
                type: 'doughnut',
                data: {
                    labels: statusChartConfig.labels,
                    datasets: [{
                        data: statusChartConfig.data,
                        filters: statusChartConfig.filters,
                        backgroundColor: statusChartConfig.colors,
                        hoverBackgroundColor: statusChartConfig.colors,
                        hoverOffset: 8,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        spacing: 1
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '72%',
                    plugins: {
                        legend: { display: false },
                        tooltip: { enabled: false }
                    },
                    animation: {
                        animateRotate: true,
                        duration: 900
                    },
                    onClick(event, elements, chart) {
                        if (!elements.length) {
                            return;
                        }

                        const index = elements[0].index;
                        const targetFilter = chart.data.datasets[0].filters?.[index];
                        if (targetFilter && typeof window.applyEvaluationStatusFilter === 'function') {
                            window.applyEvaluationStatusFilter(targetFilter);
                        }
                    },
                    onHover(event, elements, chart) {
                        chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                        if (elements.length) {
                            updateStatusChartCenter(chart, elements[0]);
                        } else {
                            resetStatusChartCenter();
                        }
                    }
                }
            });
        }

        overviewCards.forEach((card) => {
            card.addEventListener('click', () => {
                const targetFilter = card.dataset.statusFilter;
                if (targetFilter && typeof window.applyEvaluationStatusFilter === 'function') {
                    window.applyEvaluationStatusFilter(targetFilter);
                }
            });
        });

        clearFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                if (button.disabled) {
                    return;
                }

                if (typeof window.applyEvaluationStatusFilter === 'function') {
                    window.applyEvaluationStatusFilter('all');
                }
            });
        });

        document.addEventListener('evaluation-status-filter:changed', (event) => {
            syncOverviewSelection(event.detail?.status || 'all');
        });

        syncOverviewSelection('all');

        const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
        messages.forEach(function(message) {
            setTimeout(function() {
                if (message.parentElement) {
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (message.parentElement) {
                            message.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
    });
</script>
