{{-- สคริปต์ของหน้า dashboard overview --}}
<script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const toggle = document.getElementById('dashboardFilterToggle');
        const panel = document.getElementById('dashboardFilterPanel');
        const chevron = document.getElementById('dashboardFilterChevron');
        const filterForm = document.getElementById('filterForm');
        const reviewerModal = document.getElementById('reviewerModal');
        const reviewerModalBody = document.getElementById('reviewerModalBody');
        const closeReviewerModal = document.getElementById('closeReviewerModal');
        const mainFilterNames = ['start_time', 'end_time', 'department_name', 'position_name'];
        let dashboardResultsRequest = null;
        let evaluationListRequest = null;
        let overviewChartInstance = null;
        let lastDashboardResultsRequest = null;

        if (toggle && panel && chevron) {
            toggle.addEventListener('click', function () {
                panel.classList.toggle('hidden');
                chevron.classList.toggle('rotate-180');
                const isExpanded = !panel.classList.contains('hidden');
                toggle.setAttribute('aria-expanded', isExpanded ? 'true' : 'false');
                panel.setAttribute('aria-hidden', isExpanded ? 'false' : 'true');
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
                    <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ลำดับที่ ${index + 1}</div>
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

        const hasMainFilters = (url) =>
            mainFilterNames.some((name) => (url.searchParams.get(name) || '').trim() !== '');

        const syncDashboardFilterForm = (url) => {
            if (!filterForm) {
                return;
            }

            mainFilterNames.forEach((name) => {
                const field = filterForm.elements.namedItem(name);
                if (field) {
                    field.value = url.searchParams.get(name) || '';
                }
            });
        };

        const syncDashboardFilterState = (url) => {
            const active = hasMainFilters(url);
            const summary = document.querySelector('[data-dashboard-filter-summary]');
            const badge = document.querySelector('[data-dashboard-filter-badge]');
            const indicator = document.querySelector('[data-dashboard-filter-indicator]');

            if (summary) {
                summary.textContent = active
                    ? 'มีตัวกรองที่กำลังใช้งานอยู่ กดเพื่อแก้ไขหรือล้างค่า'
                    : 'กดเพื่อแสดงตัวเลือกการกรองเพิ่มเติม';
            }

            badge?.classList.toggle('hidden', !active);
            indicator?.classList.toggle('hidden', !active);

            if (panel && chevron && toggle) {
                panel.classList.toggle('hidden', !active);
                chevron.classList.toggle('rotate-180', active);
                toggle.setAttribute('aria-expanded', active ? 'true' : 'false');
                panel.setAttribute('aria-hidden', active ? 'false' : 'true');
            }
        };

        const buildDashboardFilterUrl = (form) => {
            const url = new URL(form.action || window.location.pathname, window.location.origin);
            const values = new FormData(form);

            mainFilterNames.forEach((name) => {
                const value = (values.get(name) || '').toString().trim();
                if (value) {
                    url.searchParams.set(name, value);
                }
            });

            url.searchParams.delete('page');
            return url;
        };

        const setDashboardResultsBusy = (results, busy) => {
            results.setAttribute('aria-busy', busy ? 'true' : 'false');
            const loading = results.querySelector('[data-dashboard-results-loading]');
            loading?.classList.toggle('hidden', !busy);
            loading?.classList.toggle('flex', busy);
            loading?.setAttribute('aria-hidden', busy ? 'false' : 'true');

            filterForm?.querySelectorAll('button, input, select').forEach((control) => {
                control.disabled = busy;
            });
        };

        const initializeOverviewChart = () => {
            overviewChartInstance?.destroy();
            overviewChartInstance = null;

            const configNode = document.querySelector('[data-overview-chart-config]');
            if (!configNode) {
                return;
            }

            const overviewChart = JSON.parse(configNode.textContent);
            const canvas = document.getElementById(overviewChart.id);
            const centerValueEl = document.getElementById('overviewChartCenterValue');
            const centerLabelEl = document.getElementById('overviewChartCenterLabel');
            const centerSubLabelEl = document.getElementById('overviewChartCenterSubLabel');

            if (!canvas || !centerValueEl || !centerLabelEl || !centerSubLabelEl) {
                return;
            }

            const resetCenter = () => {
                centerValueEl.textContent = overviewChart.centerValue;
                centerLabelEl.textContent = overviewChart.centerLabel;
                centerSubLabelEl.textContent = overviewChart.centerSubLabel || '';
            };

            const updateCenter = (chart, element) => {
                const index = element.index;
                const value = chart.data.datasets[0].data[index];
                const total = chart.data.datasets[0].data.reduce((sum, item) => sum + item, 0);
                const percent = total > 0 ? ((value / total) * 100).toFixed(1) : '0.0';

                centerValueEl.textContent = `${percent}%`;
                centerLabelEl.textContent = chart.data.labels[index];
                centerSubLabelEl.textContent = `${value} คน`;
            };

            overviewChartInstance = new Chart(canvas, {
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
                    animation: window.matchMedia('(prefers-reduced-motion: reduce)').matches
                        ? false
                        : {
                            animateRotate: true,
                            duration: 900,
                        },
                    onHover(event, elements, chart) {
                        chart.canvas.style.cursor = elements.length ? 'pointer' : 'default';
                        elements.length ? updateCenter(chart, elements[0]) : resetCenter();
                    },
                    onClick(event, elements, chart) {
                        elements.length ? updateCenter(chart, elements[0]) : resetCenter();
                    },
                },
            });

            resetCenter();
        };

        const loadDashboardResults = async (url, { push = true, focus = null } = {}) => {
            const currentResults = document.querySelector('[data-dashboard-results]');
            if (!currentResults) {
                return;
            }

            lastDashboardResultsRequest = { url, push, focus };
            dashboardResultsRequest?.abort();
            evaluationListRequest?.abort();
            const request = new AbortController();
            dashboardResultsRequest = request;
            currentResults.querySelector('[data-dashboard-results-error]')?.classList.add('hidden');
            setDashboardResultsBusy(currentResults, true);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'text/html',
                        'X-Dashboard-Fragment': 'dashboard-results',
                    },
                    signal: request.signal,
                });

                if (!response.ok || response.headers.get('X-Dashboard-Fragment') !== 'dashboard-results') {
                    throw new Error(`Unexpected dashboard response: ${response.status}`);
                }

                const container = document.createElement('div');
                container.innerHTML = await response.text();
                const nextResults = container.querySelector('[data-dashboard-results]');
                if (!nextResults) {
                    throw new Error('Dashboard results fragment is missing');
                }

                overviewChartInstance?.destroy();
                overviewChartInstance = null;
                currentResults.replaceWith(nextResults);

                const nextUrl = new URL(url, window.location.href);
                if (push) {
                    window.history.pushState({ dashboardFragment: 'dashboard-results' }, '', nextUrl);
                }

                syncDashboardFilterForm(nextUrl);
                syncDashboardFilterState(nextUrl);
                initializeOverviewChart();

                if (focus) {
                    document.querySelector(focus)?.focus();
                }
            } catch (error) {
                if (error.name === 'AbortError') {
                    return;
                }

                currentResults.querySelector('[data-dashboard-results-error]')?.classList.remove('hidden');
            } finally {
                if (dashboardResultsRequest === request) {
                    const activeResults = document.querySelector('[data-dashboard-results]');
                    if (activeResults) {
                        setDashboardResultsBusy(activeResults, false);
                    }
                    dashboardResultsRequest = null;
                }
            }
        };

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

            dashboardResultsRequest?.abort();
            evaluationListRequest?.abort();
            const request = new AbortController();
            evaluationListRequest = request;
            setEvaluationListBusy(currentList, true);

            try {
                const response = await fetch(url, {
                    headers: {
                        Accept: 'text/html',
                        'X-Dashboard-Fragment': 'evaluation-list',
                    },
                    signal: request.signal,
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
                    window.history.pushState({ dashboardFragment: 'evaluation-list' }, '', url);
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

                currentList.querySelector('[data-evaluation-request-error]')?.classList.remove('hidden');
            } finally {
                if (evaluationListRequest === request) {
                    const activeList = document.querySelector('[data-evaluation-list]');
                    if (activeList) {
                        setEvaluationListBusy(activeList, false);
                    }
                    evaluationListRequest = null;
                }
            }
        };

        const buildEvaluationSearchUrl = (form, { clearSearch = false } = {}) => {
            const url = new URL(form.action, window.location.href);
            url.search = new URLSearchParams(new FormData(form)).toString();
            url.searchParams.delete('page');

            if (clearSearch || !(url.searchParams.get('search') || '').trim()) {
                url.searchParams.delete('search');
            }

            return url.toString();
        };

        document.addEventListener('click', (event) => {
            const retryButton = event.target.closest('[data-dashboard-results-retry]');
            if (!retryButton || !lastDashboardResultsRequest) {
                return;
            }

            event.preventDefault();
            loadDashboardResults(lastDashboardResultsRequest.url, {
                push: lastDashboardResultsRequest.push,
                focus: lastDashboardResultsRequest.focus,
            });
        });

        document.addEventListener('click', (event) => {
            const resetButton = event.target.closest('[data-reset-filters]');
            if (!resetButton || !filterForm) {
                return;
            }

            event.preventDefault();
            mainFilterNames.forEach((name) => {
                const field = filterForm.elements.namedItem(name);
                if (field) {
                    field.value = '';
                }
            });

            loadDashboardResults(new URL(window.location.pathname, window.location.origin).toString(), {
                focus: '#dashboardFilterToggle',
            });
        });

        document.addEventListener('click', (event) => {
            const clearButton = event.target.closest('[data-auto-search-clear]');
            if (!clearButton || !clearButton.closest('[data-evaluation-list]')) {
                return;
            }

            const form = clearButton.closest('[data-auto-search-form]');
            const input = form?.querySelector('[data-auto-search-input]');
            if (!form || !input) {
                return;
            }

            event.preventDefault();
            event.stopImmediatePropagation();
            input.value = '';

            loadEvaluationList(buildEvaluationSearchUrl(form, { clearSearch: true }));
        }, true);

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
            const form = event.target.closest('#filterForm');
            if (!form) {
                return;
            }

            event.preventDefault();
            loadDashboardResults(buildDashboardFilterUrl(form).toString(), {
                focus: '[data-dashboard-filter-submit]',
            });
        });

        document.addEventListener('submit', (event) => {
            const form = event.target.closest('[data-evaluation-list] form');
            const isListFilterForm = form?.matches('[data-auto-search-form]')
                || form?.querySelector('[data-auto-submit-select]');
            if (!form || !isListFilterForm) {
                return;
            }

            event.preventDefault();
            loadEvaluationList(buildEvaluationSearchUrl(form), {
                focus: form.querySelector('[data-auto-submit-select]') ? 'heading' : null,
            });
        });

        window.addEventListener('popstate', () => {
            loadDashboardResults(window.location.href, { push: false, focus: '#evaluation-list-heading' });
        });

        initializeOverviewChart();
    });

    function openReportDetails(reportId) {
        window.open(`/dashboard-data/${reportId}`, '_blank');
    }
</script>
