@extends('layouts.app')

@section('content')
    <style>
        /* Custom animations */
        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fadeIn {
            animation: fadeIn 0.5s ease-out forwards;
        }

        .stat-card {
            transition: all 0.3s ease;
            opacity: 0;
            animation: fadeIn 0.5s ease-out forwards;
        }

        .stat-card:nth-child(1) {
            animation-delay: 0.1s;
        }

        .stat-card:nth-child(2) {
            animation-delay: 0.2s;
        }

        .stat-card:nth-child(3) {
            animation-delay: 0.3s;
        }

        .hover-scale:hover {
            transform: translateY(-5px);
        }

        .loading-skeleton {
            background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
            background-size: 200% 100%;
            animation: loading 1.5s infinite;
        }

        @keyframes loading {
            0% {
                background-position: 200% 0;
            }

            100% {
                background-position: -200% 0;
            }
        }
    </style>

    <div class="min-h-screen py-8 bg-gray-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-row justify-between">
                <!-- Header -->
                <div class="mb-8 animate-fadeIn">
                    <h1 class="text-3xl font-bold text-gray-900 mb-2">แดชบอร์ด</h1>
                    <p class="text-gray-600">ภาพรวมผลการประเมินและตัวชี้วัดประสิทธิภาพ</p>
                </div>
                <!-- Filter Summary -->
                <div class="mb-6">
                    @if (request('start_time') || request('end_time') || request('department_name'))
                        <span class="text-black ml-4 inline-flex text-sm border px-2 py-1 rounded bg-gray-100">
                            มีการกรองข้อมูล
                        </span>
                    @endif
                </div>
            </div>

            <!-- Filter Inputs -->
            <div class="bg-white rounded-xl shadow-lg p-6 mb-8 animate-fadeIn">
                <h2 class="text-xl font-bold mb-6 text-gray-800">กรองข้อมูลการประเมิน</h2>
                <form id="filterForm" method="get" class="space-y-1">
                    <div class="flex flex-col md:flex-row md:space-x-4 space-y-3 md:space-y-0">
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่เริ่มต้น</label>
                            <input name="start_time" type="date" value="{{ request('start_time', '') }}"
                                class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 font-medium text-sm">วันที่สิ้นสุด</label>
                            <input name="end_time" type="date" value="{{ request('end_time', '') }}"
                                class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-48" />
                        </div>
                        <div>
                            <label class="block mb-1 text-gray-700 text-sm">หน่วยงาน/แผนก</label>
                            <select name="department_name"
                                class="text-black bg-gray-100 rounded-lg focus:ring-blue-500 focus:border-blue-500 px-4 py-2 w-full">
                                <option value="">ทุกหน่วยงาน</option>
                                @foreach ($departments ?? [] as $dept)
                                    <option value="{{ $dept->department_name }}"
                                        {{ request('department_name') == $dept->department_name ? 'selected' : '' }}>
                                        {{ $dept->department_name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="flex justify-end space-x-2 pt-2">
                        <button type="button" onclick="resetFilters()"
                            class="px-5 py-2 rounded-lg bg-gray-200 text-gray-800 hover:bg-gray-300 transition">ล้างค่า</button>
                        <button type="submit"
                            class="px-5 py-2 rounded-lg bg-blue-600 text-white hover:bg-blue-700 transition">กรองข้อมูล</button>
                    </div>
                </form>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <!-- Total Participants -->
                <div class="stat-card bg-white rounded-xl p-6 shadow-lg hover-scale border-l-4 border-blue-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">จำนวนผู้เข้ารับการประเมิน</p>
                            <p class="text-3xl font-bold mt-2 text-gray-900">{{ $totalParticipants ?? 0 }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ $evaluationPeriod ?? 'รอบการประเมินปัจจุบัน' }}</p>
                        </div>
                        <div class="p-3 bg-blue-100 rounded-lg">
                            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <!-- SVG Icon ไม่เปลี่ยนแปลง -->
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Average Score -->
                <div class="stat-card bg-white rounded-xl p-6 shadow-lg hover-scale border-l-4 border-purple-500">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-500 text-sm font-medium">คะแนนเฉลี่ย</p>
                            <p class="text-3xl font-bold mt-2 text-gray-900">{{ $averageScore ?? 0 }}</p>
                            <p class="text-gray-500 text-sm mt-1">{{ $evaluationPeriod ?? 'รอบการประเมินปัจจุบัน' }}</p>
                        </div>
                        <div class="p-3 bg-purple-100 rounded-lg">
                            <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <!-- SVG Icon ไม่เปลี่ยนแปลง -->
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Charts Row -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
                <!-- Evaluation Results Status Chart -->
                <div class="bg-white rounded-xl shadow-lg p-6 animate-fadeIn" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">สถานะผลการประเมิน</h3>
                        <div class="flex space-x-2">
                            <button id="downloadChartBtn" class="text-gray-400 hover:text-gray-600 transition-colors" title="ดาวน์โหลดกราฟ">
                                <!-- SVG Icon ไม่เปลี่ยนแปลง -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a0 3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="h-80"><canvas id="statusChart"></canvas></div>
                </div>

                <!-- Score Distribution Chart -->
                <div class="bg-white rounded-xl shadow-lg p-6 animate-fadeIn" style="animation-delay: 0.5s;">
                    <div class="flex items-center justify-between mb-6">
                        <h3 class="text-lg font-semibold text-gray-900">การกระจายตัวของคะแนน</h3>
                        <div class="flex space-x-2">
                            <button class="text-gray-400 hover:text-gray-600 transition-colors" title="ดาวน์โหลดกราฟ">
                                <!-- SVG Icon ไม่เปลี่ยนแปลง -->
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div class="h-80"><canvas id="scoreDistributionChart"></canvas></div>
                </div>
            </div>

            <!-- Users Table -->
            <div class="bg-white rounded-xl shadow-lg overflow-hidden animate-fadeIn" style="animation-delay: 0.6s;">
                <div class="px-6 py-4 border-b border-gray-200">
                    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 sm:mb-0">ผลการประเมินรายบุคคล</h3>
                        <div class="flex flex-col sm:flex-row gap-3">
                            <button onclick="exportToExcel()"
                                class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-lg text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition-all duration-200">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M12 10v6m0 0l-3-3m3 3l3-3 m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z">
                                    </path>
                                </svg>
                                ส่งออกเป็น Excel
                            </button>
                            <div class="relative">
                                <input type="text" id="searchInput" placeholder="ค้นหาชื่อผู้รับการประเมิน"
                                    class="text-black w-full sm:w-64 pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-blue-500">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path>
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    <table id="userParticipant" class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ลำดับ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อผู้รับการประเมิน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ชื่อผู้ประเมิน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">สถานะ</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">คะแนน</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">จัดการ</th>
                            </tr>
                        </thead>
                        <tbody id="userTableBody" class="bg-white divide-y divide-gray-200">
                            @forelse($reports ?? [] as $report)
                                @php
                                    // Flexible access for both arrays and objects
                                    $evaluatee_name = is_array($report)
                                        ? $report['evaluatee_name'] ?? 'Unknown User'
                                        : $report->evaluatee_name ?? 'Unknown User';
                                    $evaluator_name = is_array($report)
                                        ? $report['evaluator_name'] ?? 'Unknown User'
                                        : $report->evaluator_name ?? 'Unknown User';
                                    $score = is_array($report) ? $report['score'] ?? 0 : $report->score ?? 0;

                                    // Status field: supports 'status' (API/array) or 'report_status' (Eloquent)
                                    $status = is_array($report)
                                        ? $report['status'] ?? 'UNKNOWN'
                                        : $report->report_status ?? ($report->status ?? 'UNKNOWN');

                                    // Pretty label for status
                                    $prettyStatus = ucfirst(strtolower($status));

                                    // Color logic
                                    $statusClass = match ($status) {
                                        'Completed' => 'bg-green-100 text-green-800',
                                        'Pending' => 'bg-yellow-100 text-yellow-800',
                                        'Draft' => 'bg-gray-100 text-gray-800',
                                        default => 'bg-blue-100 text-blue-800',
                                    };
                                @endphp
                                <tr class="hover:bg-gray-50 text-gray-900 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap text-center">
                                        {{ $loop->iteration }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $evaluatee_name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ $evaluator_name }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span
                                            class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium {{ $statusClass }}">
                                            {{ $prettyStatus }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-center font-medium text-gray-900">{{ $score }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <button class="text-blue-600 hover:text-blue-900 mr-3 transition-colors" onclick="openReportDetails('{{ $report['report_id'] }}')">
                                            ดูรายละเอียด
                                        </button>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-gray-500">ไม่พบรายงานการประเมิน</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div id="loading-state" class="hidden p-10 text-center">
                    <div class="loading-skeleton h-10 w-full rounded mb-4"></div>
                    <div class="loading-skeleton h-10 w-3/4 rounded mb-4 mx-auto"></div>
                    <div class="loading-skeleton h-10 w-1/2 rounded mx-auto"></div>
                </div>

                <div id="empty-state" class="hidden p-10 text-center">
                    <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" stroke="currentColor"
                        viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2">
                        </path>
                    </svg>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">ไม่พบผู้เข้าร่วม</h3>
                    <p class="mt-1 text-sm text-gray-500">เริ่มต้นโดยการเพิ่มผู้เข้าร่วมใหม่ในการประเมิน</p>
                </div>

                <!-- Pagination if needed -->
                <div class="px-6 py-3 border-t border-gray-200">
                    <div class="flex justify-between items-center">
                        <div class="text-sm text-gray-500">
                            แสดง <span class="font-medium">1</span> ถึง <span class="font-medium">10</span> จาก <span
                                class="font-medium">{{ $totalParticipants ?? 50 }}</span> รายการ
                        </div>
                        <div class="flex space-x-2">
                            <button
                                class="px-3 py-1 rounded border border-gray-300 text-gray-500 hover:bg-gray-50">ก่อนหน้า</button>
                            <button
                                class="px-3 py-1 rounded border border-gray-300 text-gray-500 hover:bg-gray-50">ถัดไป</button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function resetFilters() {
            document.querySelector('input[name="start_time"]').value = '';
            document.querySelector('input[name="end_time"]').value = '';
            document.querySelector('select[name="department_name"]').value = '';
            document.getElementById('filterForm').submit();
        }
    </script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.sheetjs.com/xlsx-latest/package/dist/xlsx.full.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Status Chart
            const statusLabels = ['Assigned', 'Draft', 'Pending', 'Completed'];
            const statusData = [
                {{ $statusCounts_chart['Assigned'] ?? 0 }},
                {{ $statusCounts_chart['Draft'] ?? 0 }},
                {{ $statusCounts_chart['Pending'] ?? 0 }},
                {{ $statusCounts_chart['Completed'] ?? 0 }}
            ];
            const statusCtx = document.getElementById('statusChart').getContext('2d');
            new Chart(statusCtx, {
                type: 'bar',
                data: {
                    labels: statusLabels,
                    datasets: [{
                        label: 'Report Status',
                        data: statusData ?? [0, 0, 0, 0],
                        backgroundColor: [
                            'rgba(59, 130, 246, 0.8)', // ASSIGNED color
                            'rgba(156, 163, 175, 0.8)', // DRAFT color
                            'rgba(251, 191, 36, 0.8)', // PENDING color
                            'rgba(16, 185, 129, 0.8)' // COMPLETED color
                        ],
                        borderColor: [
                            'rgba(59, 130, 246, 1)',
                            'rgba(156, 163, 175, 1)',
                            'rgba(251, 191, 36, 1)',
                            'rgba(16, 185, 129, 1)'
                        ],
                        borderWidth: 1,
                        borderRadius: 8
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        },
                        tooltip: {
                            callbacks: {
                                label: function(context) {
                                    return `${context.label}: ${context.raw} report(s)`;
                                }
                            }
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                precision: 0
                            },
                            grid: {
                                color: 'rgba(0, 0, 0, 0.05)'
                            }
                        },
                        x: {
                            grid: {
                                display: false
                            }
                        }
                    },
                    animation: {
                        duration: 2000,
                        easing: 'easeOutQuart'
                    }
                }
            });

            // Score Distribution Chart (Scatter Plot)
            const scatterData_chart = {!! json_encode($scatterData_chart) !!};
            const scoreDistCtx = document.getElementById('scoreDistributionChart').getContext('2d');
            new Chart(
                scoreDistCtx, {
                    type: 'scatter',
                    data: {
                        datasets: [{
                            label: 'Report Scores',
                            data: scatterData_chart,
                            backgroundColor: 'rgba(79, 70, 229, 0.7)',
                            borderColor: 'rgba(79, 70, 229, 1)',
                            borderWidth: 1,
                            pointRadius: 6,
                            pointHoverRadius: 8,
                            pointBackgroundColor: function(context) {
                                const value = context.dataset.data[context.dataIndex].y;
                                return value >= 60 ? 'rgba(16, 185, 129, 0.8)' :
                                    'rgba(239, 68, 68, 0.8)';
                            }
                        }]
                    },
                    options: {
                        scales: {
                            x: {
                                title: {
                                    display: true,
                                    text: 'Report Number'
                                }
                            },
                            y: {
                                title: {
                                    display: true,
                                    text: 'Score'
                                }
                            }
                        },
                        plugins: {
                            tooltip: {
                                callbacks: {
                                    label: function(context) {
                                        return `Report ${context.parsed.x}: ${context.parsed.y.toFixed(2)}%`;
                                    }
                                }
                            }
                        }
                    }
                }
            );


            // Search functionality
            document.getElementById('searchInput').addEventListener('input', function(e) {
                const searchTerm = e.target.value.toLowerCase();
                const rows = document.querySelectorAll('#userTableBody tr');
                let hasMatch = false;

                rows.forEach(row => {
                    const userName = row.querySelector('td:nth-child(2) .text-sm.font-medium')
                        .textContent.toLowerCase();

                    if (userName.includes(searchTerm)) {
                        row.style.display = '';
                        hasMatch = true;
                    } else {
                        row.style.display = 'none';
                    }
                });

                // Show empty state if no matches
                document.getElementById('empty-state').style.display = hasMatch ? 'none' : 'block';
            });
        });

        window.reports = @json($reports);
        // Export functions
        function exportToExcel() {
            // Show loading indicator
            document.getElementById('loading-state').style.display = 'block';

            setTimeout(() => {
                const tableData = [];
                // Add header row in Thai
                tableData.push([
                    'ลำดับ',
                    'รอบประเมิน',
                    'ชื่อ-สกุล',
                    'แผนก',
                    'กลุ่มงาน',
                    'ตำแหน่ง',
                    'คะแนนรวม',
                    'คะแนนด้านปริมาณ',
                    'คะแนนด้านคุณภาพ',
                    'ข้อเสนอแนะ',
                    'ชื่อผู้ประเมิน',
                    'สร้างเมื่อ',
                    'แก้ไขเมื่อ',
                ]);

                (window.reports || []).forEach((report, index) => {
                    tableData.push([
                        index + 1,
                        `${report.start_time || ''} ถึง ${report.end_time || ''}`,
                        report.evaluatee_name || '',
                        report.evaluatee_department_name || '',
                        report.evaluatee_personnel_type || '',
                        report.evaluatee_position_name || '',
                        report.score ?? '',
                        report.quantity_score ?? '',
                        report.quality_score ?? '',
                        report.comment ?? '',
                        report.evaluator_name || '',
                        report.created_at || '',
                        report.updated_at || '',
                    ]);
                });

                // Create XLSX and export
                const ws = XLSX.utils.aoa_to_sheet(tableData);
                const wb = XLSX.utils.book_new();
                XLSX.utils.book_append_sheet(wb, ws, "Evaluation Results");
                XLSX.writeFile(wb, "evaluation_results.xlsx");

                // Hide loading indicator
                document.getElementById('loading-state').style.display = 'none';
            }, 1000);
        }

        function openReportDetails(reportId) {
            window.open(`/dashboard/${reportId}`, '_blank') ;
        }
    </script>
@endpush
