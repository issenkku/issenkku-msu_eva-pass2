@push('scripts')
    {{-- สคริปต์กลางของ dashboard overview ใช้ร่วมกันหลายบทบาท --}}
    <script>
        (function() {
            const dashboardScope = @json($scope);
            const dashboardRootId = @json($rootId);
            const overviewChartConfig = @json($overviewChart);
            const ajaxLinkSelector = `[data-${dashboardScope}-ajax-link]`;
            const countPrefix = @json('จำนวน ');
            const countSuffix = @json(' คน');

            function initOverviewChart(root = document) {
                const canvas = root.getElementById(overviewChartConfig.id);
                if (!canvas || typeof Chart === 'undefined') {
                    return;
                }

                const valueNode = root.getElementById(`${overviewChartConfig.id}Value`);
                const labelNode = root.getElementById(`${overviewChartConfig.id}Label`);
                const metaNode = root.getElementById(`${overviewChartConfig.id}Meta`);
                const defaults = {
                    value: overviewChartConfig.centerValue,
                    label: overviewChartConfig.centerLabel,
                    meta: overviewChartConfig.centerMeta,
                };

                const setCenter = function(value, label, meta) {
                    valueNode.textContent = value;
                    labelNode.textContent = label;
                    metaNode.textContent = meta;
                };

                new Chart(canvas, {
                    type: 'doughnut',
                    data: {
                        labels: overviewChartConfig.labels,
                        datasets: [{
                            data: overviewChartConfig.data,
                            backgroundColor: overviewChartConfig.colors,
                            borderColor: '#ffffff',
                            borderWidth: 4,
                            hoverOffset: 8,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '72%',
                        plugins: {
                            legend: { display: false },
                            tooltip: { enabled: false },
                        },
                        onHover: function(event, activeElements) {
                            const target = event?.native?.target;
                            if (target) {
                                target.style.cursor = activeElements.length ? 'pointer' : 'default';
                            }

                            if (!activeElements.length) {
                                setCenter(defaults.value, defaults.label, defaults.meta);
                                return;
                            }

                            const index = activeElements[0].index;
                            const count = overviewChartConfig.data[index] || 0;
                            const total = overviewChartConfig.data.reduce((sum, item) => sum + item, 0);
                            const percent = total > 0 ? ((count / total) * 100).toFixed(1) : '0.0';
                            setCenter(`${percent}%`, overviewChartConfig.labels[index], `${countPrefix}${count}${countSuffix}`);
                        },
                        onClick: function(event, activeElements) {
                            if (!activeElements.length) {
                                return;
                            }

                            const targetUrl = overviewChartConfig.filters[activeElements[0].index];
                            if (targetUrl) {
                                applyDashboardRequest(targetUrl);
                            }
                        },
                    },
                });
            }

            function applyDashboardRequest(url, pushState = true) {
                const root = document.getElementById(dashboardRootId);
                if (!root) {
                    return Promise.resolve();
                }

                root.classList.add('opacity-70', 'pointer-events-none');

                return fetch(url, { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(response) {
                        return response.text();
                    })
                    .then(function(html) {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const incomingRoot = doc.getElementById(dashboardRootId);

                        if (!incomingRoot) {
                            window.location.href = url;
                            return;
                        }

                        root.innerHTML = incomingRoot.innerHTML;
                        root.className = incomingRoot.className;

                        if (pushState) {
                            window.history.pushState({}, '', url);
                        }

                        initOverviewChart(document);
                        initDashboardAjax();
                        initFilterToggle(document);
                    })
                    .catch(function() {
                        window.location.href = url;
                    })
                    .finally(function() {
                        const currentRoot = document.getElementById(dashboardRootId);
                        if (currentRoot) {
                            currentRoot.classList.remove('opacity-70', 'pointer-events-none');
                        }
                    });
            }

            window.resetFilters = function() {
                const form = document.getElementById('filterForm');
                if (!form) {
                    return;
                }

                form.querySelector('input[name="search"]').value = '';
                form.querySelector('input[name="start_time"]').value = '';
                form.querySelector('input[name="end_time"]').value = '';

                const departmentField = form.querySelector('select[name="department_name"]');
                if (departmentField) {
                    departmentField.value = '';
                }

                form.querySelector('select[name="status"]').value = '';
                form.querySelector('select[name="urgency"]').value = '';
                form.querySelector('select[name="year"]').value = '';

                applyDashboardRequest(window.location.pathname);
            };

            function initFilterToggle(scopeRoot = document) {
                const toggle = scopeRoot.getElementById
                    ? scopeRoot.getElementById(`${dashboardScope}FilterToggle`)
                    : scopeRoot.querySelector(`#${dashboardScope}FilterToggle`);
                const panel = scopeRoot.getElementById
                    ? scopeRoot.getElementById(`${dashboardScope}FilterPanel`)
                    : scopeRoot.querySelector(`#${dashboardScope}FilterPanel`);
                const chevron = scopeRoot.getElementById
                    ? scopeRoot.getElementById(`${dashboardScope}FilterChevron`)
                    : scopeRoot.querySelector(`#${dashboardScope}FilterChevron`);

                if (!toggle || !panel || !chevron || toggle.dataset.bound === 'true') {
                    return;
                }

                toggle.dataset.bound = 'true';
                toggle.addEventListener('click', function() {
                    panel.classList.toggle('hidden');
                    chevron.classList.toggle('rotate-180');
                });
            }

            function initDashboardAjax() {
                const root = document.getElementById(dashboardRootId);
                if (!root || root.dataset.ajaxReady === '1') {
                    return;
                }

                root.dataset.ajaxReady = '1';

                root.addEventListener('submit', function(event) {
                    const form = event.target;
                    if (!(form instanceof HTMLFormElement) || (form.method || '').toUpperCase() !== 'GET') {
                        return;
                    }

                    event.preventDefault();
                    const action = form.getAttribute('action') || window.location.pathname;
                    const targetUrl = new URL(action, window.location.origin);
                    const params = new URLSearchParams(new FormData(form));

                    params.forEach(function(value, key) {
                        if (value !== '') {
                            targetUrl.searchParams.append(key, value);
                        }
                    });

                    applyDashboardRequest(targetUrl.toString());
                });

                root.addEventListener('click', function(event) {
                    const dismissButton = event.target.closest('[data-dismiss-dashboard-message]');
                    if (dismissButton) {
                        const message = dismissButton.closest('#successMessage, #warningMessage, #errorMessage');
                        if (message) {
                            message.remove();
                        }
                        return;
                    }

                    const resetButton = event.target.closest('[data-dashboard-reset-filters]');
                    if (resetButton) {
                        event.preventDefault();
                        window.resetFilters();
                        return;
                    }

                    const link = event.target.closest(ajaxLinkSelector);
                    if (!link) {
                        return;
                    }

                    event.preventDefault();
                    applyDashboardRequest(link.href);
                });
            }

            function scheduleFlashDismiss() {
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
            }

            document.addEventListener('DOMContentLoaded', function() {
                initOverviewChart(document);
                initDashboardAjax();
                initFilterToggle(document);
                scheduleFlashDismiss();

                window.addEventListener('popstate', function() {
                    applyDashboardRequest(window.location.href, false);
                });
            });
        })();
    </script>
@endpush
