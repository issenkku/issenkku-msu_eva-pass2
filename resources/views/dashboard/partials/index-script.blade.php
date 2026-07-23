{{-- เธชเธเธฃเธดเธเธ•เนเธเธญเธเธซเธเนเธฒ dashboard overview --}}
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
        let evaluationListRequest = null;

        if (toggle && panel && chevron) {
            toggle.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
                const isExpanded = !panel.classList.contains('hidden');
                toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                panel.setAttribute('aria-hidden', isExpanded ? 'false' : 'true');
            });
        }

        if (!window.__dashboardResetFiltersBound) {
            window.__dashboardResetFiltersBound = true;

            document.addEventListener('click', function (event) {
                const resetButton = event.target.closest('[data-reset-filters]');
                if (!resetButton) {
                    return;
                }

                resetFilters();
            });
        }

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
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">เธฅเธณเธ”เธฑเธเธ—เธตเน ${index + 1}</div>
                    <div class="mt-1 text-sm font-semibold text-slate-900">${reviewer.label}: ${reviewer.name}</div>
                    <div class="mt-1 text-xs text-slate-500">${reviewer.position ?? '-'}</div>
                </div>
            `).join('');

            reviewerModal.classList.remove('hidden');
        };

        document.addEventListener('click', (event) => {
            const button = event.target.closest('[data-reviewer-modal-button]');
            if (!button) {
                return;
            }

            try {
                openReviewerDialog(JSON.parse(button.dataset.reviewers || '[]'));
            } catch (error) {
                console.error('Failed to parse reviewer list', error);
            }
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

        const setEvaluationListBusy = (list, busy) => {
            list.setAttribute('aria-busy', busy ? 'true' : 'false');
            list.querySelectorAll('button, input, select').forEach((control) => {
                control.disabled = busy;
            });
            list.querySelectorAll('a').forEach((link) => {
                link.classList.toggle('pointer-events-none', busy);
                link.setAttribute('aria-disabled', busy ? 'true' : 'false');
            });
            list.querySelector('[data-evaluation-loading]')?.classList.toggle('hidden', !busy);
        };

        const loadEvaluationList = async (url, { push = true, focus = null } = {}) => {
            const currentList = document.querySelector('[data-evaluation-list]');
            if (!currentList) {
                return;
            }

            evaluationListRequest?.abort();
            evaluationListRequest = new AbortController();
            setEvaluationListBusy(currentList, true);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'text/html',
                        'X-Dashboard-Fragment': 'evaluation-list',
                    },
                    signal: evaluationListRequest.signal,
                });

                if (!response.ok || response.headers.get('X-Dashboard-Fragment') !== 'evaluation-list') {
                    throw new Error(`Unexpected dashboard response: ${response.status}`);
                }

                const container = document.createElement('div');
                container.innerHTML = await response.text();
                const nextList = container.querySelector('[data-evaluation-list]');
                if (!nextList) {
                    throw new Error('Evaluation list fragment is missing');
                }

                currentList.replaceWith(nextList);
                if (push) {
                    window.history.pushState({}, '', url);
                }

                if (focus === 'heading') {
                    document.getElementById('evaluation-list-heading')?.focus();
                } else if (focus) {
                    document.querySelector(`[data-status-filter="${CSS.escape(focus)}"]`)?.focus();
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                setEvaluationListBusy(currentList, false);
                currentList.querySelector('[data-evaluation-request-error]')?.classList.remove('hidden');
            }
        };

        document.addEventListener('click', (event) => {
            const statusLink = event.target.closest('[data-status-filter]');
            const paginationLink = event.target.closest('[data-evaluation-pagination] a');
            const link = statusLink || paginationLink;
            if (!link || event.defaultPrevented || event.button !== 0
                || event.metaKey || event.ctrlKey || event.shiftKey || event.altKey) {
                return;
            }

            event.preventDefault();
            loadEvaluationList(link.href, {
                focus: statusLink ? statusLink.dataset.statusFilter : 'heading',
            });
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-evaluation-list] [data-auto-search-form]');
            if (!form) {
                return;
            }

            event.preventDefault();
            const url = new URL(form.action, window.location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            url.searchParams.delete('page');
            loadEvaluationList(url.toString(), { focus: 'heading' });
        });

        window.addEventListener('popstate', () => {
            loadEvaluationList(window.location.href, { push: false, focus: 'heading' });
        });

        const overviewChart = @json($overviewChart);
        const canvas = document.getElementById(overviewChart.id);
        const centerValueEl = document.getElementById('overviewChartCenterValue');
        const centerLabelEl = document.getElementById('overviewChartCenterLabel');
        const centerSubLabelEl = document.getElementById('overviewChartCenterSubLabel');

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
                            resetOverviewCenter();
                            return;
                        }

                        updateOverviewCenter(chart, elements[0]);
                    },
                },
            });
        }

        resetOverviewCenter();
    });

    function openReportDetails(reportId) {
        window.open(`/dashboard-data/${reportId}`, '_blank');
    }
</script>
