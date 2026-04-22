{{-- สคริปต์ของหน้า dashboard overview --}}
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
    function resetFilters() {
        document.querySelector('input[name="start_time"]').value = '';
        document.querySelector('input[name="end_time"]').value = '';
        document.querySelector('select[name="department_name"]').value = '';
        document.querySelector('select[name="position_name"]').value = '';
        document.getElementById('filterForm').submit();
    }

    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('dashboardFilterToggle');
        const panel = document.getElementById('dashboardFilterPanel');
        const chevron = document.getElementById('dashboardFilterChevron');
        const reviewerModal = document.getElementById('reviewerModal');
        const reviewerModalBody = document.getElementById('reviewerModalBody');
        const closeReviewerModal = document.getElementById('closeReviewerModal');
        const reviewerButtons = Array.from(document.querySelectorAll('[data-reviewer-modal-button]'));
        const statusFilterButtons = Array.from(document.querySelectorAll('.dashboard-status-filter'));
        const overviewFilterButtons = Array.from(document.querySelectorAll('[data-overview-filter]'));
        const tableRows = Array.from(document.querySelectorAll('[data-dashboard-row]'));
        const searchInput = document.getElementById('searchInput');
        const emptyState = document.getElementById('empty-state');
        let activeStatusFilter = 'all';

        if (toggle && panel && chevron) {
            toggle.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
            });
        }

        const clearStatusQuery = () => {
            const url = new URL(window.location.href);
            if (url.searchParams.has('status')) {
                url.searchParams.delete('status');
                window.history.replaceState({}, '', url);
            }
        };

        const setActiveStatusButton = (status) => {
            statusFilterButtons.forEach((button) => {
                const isActive = button.dataset.statusFilter === status;
                button.classList.toggle('ring-2', isActive);
                button.classList.toggle('ring-offset-2', isActive);
                button.classList.toggle('ring-blue-300', isActive);
            });

            overviewFilterButtons.forEach((button) => {
                button.classList.toggle('is-active', button.dataset.overviewFilter === status);
            });
        };

        const applyDashboardFilters = () => {
            const searchTerm = (searchInput?.value || '').trim().toLowerCase();
            let hasMatch = false;

            tableRows.forEach((row) => {
                const statusMatches = activeStatusFilter === 'all' || row.dataset.statusGroup === activeStatusFilter;
                const nameCell = row.querySelector('td:nth-child(2) .text-sm.font-medium');
                const userName = nameCell ? nameCell.textContent.toLowerCase() : '';
                const searchMatches = !searchTerm || userName.includes(searchTerm);
                const matches = statusMatches && searchMatches;

                row.style.display = matches ? '' : 'none';
                if (matches) {
                    hasMatch = true;
                }
            });

            if (emptyState) {
                emptyState.style.display = hasMatch ? 'none' : 'block';
            }
        };

        const closeReviewerDialog = () => {
            if (!reviewerModal) {
                return;
            }

            reviewerModal.classList.add('hidden');
            if (reviewerModalBody) {
                reviewerModalBody.innerHTML = '';
            }
        };

        const openReviewerDialog = (reviewers) => {
            if (!reviewerModal || !reviewerModalBody) {
                return;
            }

            reviewerModalBody.innerHTML = reviewers.map((reviewer, index) => `
                <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ลำดับที่ ${index + 1}</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">${reviewer.label}: ${reviewer.name}</div>
                    <div class="mt-1 text-xs text-slate-500">${reviewer.position ?? '-'}</div>
                </div>
            `).join('');

            reviewerModal.classList.remove('hidden');
        };

        reviewerButtons.forEach((button) => {
            button.addEventListener('click', () => {
                try {
                    const reviewers = JSON.parse(button.dataset.reviewers || '[]');
                    openReviewerDialog(reviewers);
                } catch (error) {
                    console.error('Failed to parse reviewer list', error);
                }
            });
        });

        closeReviewerModal?.addEventListener('click', closeReviewerDialog);
        reviewerModal?.addEventListener('click', (event) => {
            if (event.target === reviewerModal) {
                closeReviewerDialog();
            }
        });

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape') {
                closeReviewerDialog();
            }
        });

        const overviewChart = @json($overviewChart);
        const canvas = document.getElementById(overviewChart.id);
        const centerValueEl = document.getElementById('overviewChartCenterValue');
        const centerLabelEl = document.getElementById('overviewChartCenterLabel');
        const centerSubLabelEl = document.getElementById('overviewChartCenterSubLabel');
        const overviewFilterStateEl = document.getElementById('overviewFilterState');
        const overviewClearFilterButton = document.getElementById('overviewClearFilterButton');
        const overviewFilterStateClasses = {
            'มอบหมาย': ['bg-red-50', 'text-red-700'],
            'เริ่มกรอกข้อมูล': ['bg-orange-50', 'text-orange-700'],
            'กำลังดำเนินการ': ['bg-blue-50', 'text-blue-700'],
            'ประเมินเสร็จสิ้น': ['bg-green-50', 'text-green-700'],
        };

        const applyStatusFilter = (status) => {
            clearStatusQuery();
            activeStatusFilter = status || 'all';
            setActiveStatusButton(activeStatusFilter);
            applyDashboardFilters();

            if (overviewFilterStateEl) {
                Object.values(overviewFilterStateClasses).flat().forEach((className) => {
                    overviewFilterStateEl.classList.remove(className);
                });

                if (activeStatusFilter !== 'all') {
                    overviewFilterStateEl.textContent = `กรองอยู่: ${activeStatusFilter}`;
                    (overviewFilterStateClasses[activeStatusFilter] || ['bg-blue-50', 'text-blue-700']).forEach((className) => {
                        overviewFilterStateEl.classList.add(className);
                    });
                    overviewFilterStateEl.classList.remove('hidden');
                } else {
                    overviewFilterStateEl.textContent = '';
                    overviewFilterStateEl.classList.add('hidden');
                }
            }

            if (overviewClearFilterButton) {
                overviewClearFilterButton.classList.toggle('hidden', activeStatusFilter === 'all');
            }
        };

        const resetOverviewCenter = () => {
            if (centerValueEl) {
                centerValueEl.textContent = overviewChart.centerValue;
            }

            if (centerLabelEl) {
                centerLabelEl.textContent = overviewChart.centerLabel;
            }

            if (centerSubLabelEl) {
                centerSubLabelEl.textContent = overviewChart.centerSubLabel || '';
            }
        };

        const updateOverviewCenter = (chart, element) => {
            if (!centerValueEl || !centerLabelEl || !element) {
                return;
            }

            const index = element.index;
            const value = chart.data.datasets[0].data[index];
            const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
            const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

            centerValueEl.textContent = `${percent}%`;
            centerLabelEl.textContent = chart.data.labels[index];

            if (centerSubLabelEl) {
                centerSubLabelEl.textContent = `${value} คน`;
            }
        };

        if (canvas) {
            new Chart(canvas, {
                type: 'doughnut',
                data: {
                    labels: overviewChart.labels,
                    datasets: [{
                        data: overviewChart.data,
                        backgroundColor: overviewChart.colors,
                        hoverBackgroundColor: overviewChart.colors,
                        hoverOffset: 8,
                        borderColor: '#ffffff',
                        borderWidth: 3,
                        spacing: 1,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    cutout: '74%',
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            enabled: false,
                        },
                    },
                    animation: {
                        animateRotate: true,
                        duration: 900,
                    },
                    onHover(event, elements, chart) {
                        chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                        if (elements.length) {
                            updateOverviewCenter(chart, elements[0]);
                        } else {
                            resetOverviewCenter();
                        }
                    },
                    onClick(event, elements, chart) {
                        if (!elements.length) {
                            applyStatusFilter('all');
                            resetOverviewCenter();
                            return;
                        }

                        const index = elements[0].index;
                        const nextFilter = overviewChart.filters?.[index] || 'all';
                        applyStatusFilter(activeStatusFilter === nextFilter ? 'all' : nextFilter);
                        updateOverviewCenter(chart, elements[0]);
                    },
                },
            });
        }

        statusFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                applyStatusFilter(button.dataset.statusFilter || 'all');
            });
        });

        overviewFilterButtons.forEach((button) => {
            button.addEventListener('click', () => {
                const nextFilter = button.dataset.overviewFilter || 'all';
                applyStatusFilter(activeStatusFilter === nextFilter ? 'all' : nextFilter);
            });
        });

        if (overviewClearFilterButton) {
            overviewClearFilterButton.addEventListener('click', () => {
                applyStatusFilter('all');
                resetOverviewCenter();
            });
        }

        if (searchInput) {
            searchInput.addEventListener('input', function () {
                applyDashboardFilters();
            });
        }

        applyStatusFilter(activeStatusFilter);
        resetOverviewCenter();
    });

    function openReportDetails(reportId) {
        window.open(`/dashboard-data/${reportId}`, '_blank');
    }
</script>
