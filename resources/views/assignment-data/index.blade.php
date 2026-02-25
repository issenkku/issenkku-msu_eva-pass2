@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\assignment-data\index.blade.php --}}

@section('title', 'จัดการรอบการประเมิน')

@section('content')
@php
        use Carbon\Carbon;

        function formatThaiDate($date)
        {
            if (!$date) return '-';

            Carbon::setLocale('th'); 
            setlocale(LC_TIME, 'th_TH.UTF-8');

            $thaiMonth = $date->translatedFormat('j F'); 
            $buddhistYear = $date->year + 543;
            $time = $date->format('H:i');

            return "{$thaiMonth} {$buddhistYear}";
        }
    @endphp

    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(session('success'))
    {{--  --}}
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif

    @if(session('error'))
    {{--  --}}
    <div id="errorMessage" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.732 16.5c-.77.833.192 2.5 1.732 2.5z"></path>
            </svg>
            <span>{{ session('error') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
    @endif

    <body class="bg-gray-50 min-h-screen py-8">
        {{-- บล็อกเนื้อหา --}}
        <div class="py-12 max-w-7xl mx-auto px-4">
            <!-- Header Section -->
            <div class="bg-white shadow-sm rounded-lg p-6 mb-6">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <h1 class="text-2xl font-bold text-gray-800 flex items-center">
                            <i class="fas fa-clipboard-list mr-3 text-blue-600"></i>
                            จัดการรอบการประเมิน
                        </h1>
                        <p class="text-gray-600 mt-1">ดูข้อมูลและจัดการรอบการประเมินทั้งหมด</p>
                    </div>
                    <div class="flex flex-col sm:flex-row gap-3">
                        <a href="{{ route('assignment-data.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center justify-center">
                            <i class="fas fa-plus mr-2"></i>สร้างรอบการประเมินใหม่
                        </a>
                    </div>
                </div>
            </div>

            <!-- Assignment Data List -->
            <div class="bg-white shadow-sm rounded-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200">
                    <h2 class="text-lg font-semibold text-gray-800">รายการรอบการประเมิน</h2>
                </div>

                @if($assignmentData->count() > 0)
                    <div class="overflow-x-auto bg-white rounded-lg shadow-sm border border-gray-200">
                        {{-- ตารางข้อมูล --}}
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                                        ระยะเวลาประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[220px]">
                                        เกณฑ์การประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[200px]">
                                        ผู้ประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[160px]">
                                        ผู้รับการประเมิน
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[120px]">
                                        สถานะ
                                    </th>
                                    <th class="px-4 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider min-w-[140px]">
                                        การดำเนินการ
                                    </th>
                                </tr>
                            </thead>

                            <tbody class="bg-white divide-y divide-gray-100">
                                @foreach($assignmentData as $assignment)
                                @php
                                    $start = optional($assignment)->start_time ? Carbon::parse($assignment->start_time) : null;
                                    $end = optional($assignment)->end_time ? Carbon::parse($assignment->end_time) : null;
                                    $startFormatted = formatThaiDate($start);
                                    $endFormatted = formatThaiDate($end);
                                    $reportData = $assignment->assignments->first()?->report?->reportData;
                                    $evaluateeCount = $assignment->assignments->count();
                                    $now = now();
                                    $startTime = \Carbon\Carbon::parse($assignment->start_time);
                                    $endTime = \Carbon\Carbon::parse($assignment->end_time);
                                @endphp

                                <tr class="hover:bg-gray-50 transition-all">
                                    <!-- ระยะเวลาประเมิน -->
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-sm font-medium text-gray-900 break-words leading-snug">
                                            {{ $startFormatted }} <br><span class="text-gray-400">–</span> {{ $endFormatted }}
                                        </div>
                                        <div class="text-xs text-gray-500 mt-0.5">
                                            {{ \Carbon\Carbon::parse($assignment->start_time)->diffInDays($assignment->end_time) + 1 }} วัน
                                        </div>
                                    </td>

                                    <!-- เกณฑ์การประเมิน -->
                                    <td class="px-4 py-3 align-top">
                                        <div class="text-sm font-medium text-gray-800 max-w-[250px]">
                                            {{ $reportData?->report_title ?? '-' }}
                                        </div>
                                        @if($reportData?->report_description)
                                        <div class="text-xs text-gray-500 mt-1 truncate max-w-[250px]">
                                            {{ Str::limit($reportData->report_description, 60) }}
                                        </div>
                                        @endif
                                    </td>

                                    <!-- ผู้ประเมิน -->
                                    <td class="px-4 py-3 align-top">
                                        @if($assignment->evaluatorUser)
                                        <div class="flex items-center space-x-2">
                                            <div class="bg-blue-100 px-3 py-1.5 rounded-xl flex items-center">
                                                <i class="fas fa-user-check text-blue-500 mr-2"></i>
                                                <div>
                                                    <div class="text-sm font-semibold text-blue-700 leading-tight">
                                                        {{ $assignment->evaluatorUser->name }}
                                                    </div>
                                                    <div class="text-xs text-blue-600">
                                                        {{ $assignment->evaluatorUser->position?->name ?? '-' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        @else
                                        <span class="text-sm text-gray-500">-</span>
                                        @endif
                                    </td>

                                    <!-- ผู้รับการประเมิน -->
                                    <td class="px-4 py-3 text-center align-top">
                                        <div class="flex flex-col items-center justify-center gap-1 rounded-xl bg-green-100 px-3 py-1.5">
                                            <span class="inline-flex items-center px-2.5 py-0.5 text-xs font-medium text-green-800">
                                                <i class="fas fa-users mr-1"></i>{{ $evaluateeCount }} คน
                                            </span>

                                            @if($evaluateeCount > 0)
                                                <button 
                                                    onclick="showEvaluatees({{ $assignment->id }})" 
                                                    class="text-green-600 hover:text-green-800 text-xs underline mt-1"
                                                >
                                                    ดูรายละเอียด
                                                </button>
                                            @endif
                                        </div>
                                    </td>

                                    <!-- สถานะ -->
                                    <td class="px-4 py-3 align-top whitespace-nowrap">
                                        @if($now->lt($startTime))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                                <i class="fas fa-clock mr-1"></i>รอเริ่มต้น
                                            </span>
                                        @elseif($now->between($startTime, $endTime))
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="fas fa-play-circle mr-1"></i>กำลังดำเนินการ
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="fas fa-check-circle mr-1"></i>สิ้นสุดแล้ว
                                            </span>
                                        @endif
                                    </td>

                                    <!-- การดำเนินการ -->
                                    <td class="px-4 py-3 align-top text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <a href="{{ route('assignment-data.edit', $assignment->id) }}" 
                                                class="px-3 py-1.5 bg-yellow-500 text-white text-xs rounded-lg hover:bg-yellow-600 shadow-sm transition-all flex items-center">
                                                <i class="fas fa-edit mr-1"></i>แก้ไข
                                            </a>
                                            <button onclick="deleteAssignment({{ $assignment->id }})" 
                                                class="px-3 py-1.5 bg-red-500 text-white text-xs rounded-lg hover:bg-red-600 shadow-sm transition-all flex items-center">
                                                <i class="fas fa-trash mr-1"></i>ลบ
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <div class="px-6 py-4 border-t border-gray-200">
                        {{ $assignmentData->links() }}
                    </div>
                @else
                    <div class="px-6 py-12 text-center">
                        <div class="max-w-sm mx-auto">
                            <div class="p-6 bg-gray-50 rounded-full w-24 h-24 mx-auto flex items-center justify-center mb-4">
                                <i class="fas fa-clipboard-list text-3xl text-gray-400"></i>
                            </div>
                            <h3 class="text-lg font-medium text-gray-900 mb-2">ยังไม่มีรอบการประเมิน</h3>
                            <p class="text-gray-500 mb-6">เริ่มต้นด้วยการสร้างรอบการประเมินแรกของคุณ</p>
                            <a href="{{ route('assignment-data.create') }}" 
                                class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors">
                                <i class="fas fa-plus mr-2"></i>สร้างรอบการประเมินใหม่
                            </a>
                        </div>
                    </div>
                @endif
            </div>
        </div>

        <!-- Modal for showing evaluatees -->
        {{-- บล็อกเนื้อหา --}}
        <div id="evaluateesModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
            <div class="relative top-20 mx-auto p-5 border w-11/12 md:w-2/3 lg:w-1/2 shadow-lg rounded-md bg-white">
                <div class="flex justify-between items-center mb-4 pb-3 border-b">
                    <h3 class="text-lg font-semibold text-gray-900">
                        <i class="fas fa-users mr-2 text-blue-600"></i>รายชื่อผู้รับการประเมิน
                    </h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
                <div id="evaluateesContent" class="mt-4 max-h-96 overflow-y-auto">
                    <!-- Content will be loaded here -->
                </div>
            </div>
        </div>
    </body>

    <script>
        $(document).ready(function() {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            setTimeout(function() {
                $('#successMessage, #errorMessage').fadeOut();
            }, 5000);
        });

        function showEvaluatees(assignmentId) {
            $('#evaluateesContent').html('<div class="text-center py-4"><i class="fas fa-spinner fa-spin text-2xl text-blue-600"></i><p class="text-sm text-gray-500 mt-2">กำลังโหลดข้อมูล...</p></div>');
            $('#evaluateesModal').removeClass('hidden');

            $.ajax({
                url: `/assignment-data/${assignmentId}`,
                type: 'GET',
                success: function(data) {
                    let html = '<div class="space-y-2">';
                    
                    if (data.assignments && data.assignments.length > 0) {
                        data.assignments.forEach((assignment, index) => {
                            if (assignment.evaluatee_user) {
                                const initial = assignment.evaluatee_user.name.charAt(0).toUpperCase();
                                const colors = [
                                    'bg-blue-100 text-blue-600',
                                    'bg-green-100 text-green-600',
                                    'bg-purple-100 text-purple-600',
                                    'bg-pink-100 text-pink-600',
                                    'bg-indigo-100 text-indigo-600'
                                ];
                                const color = colors[index % colors.length];
                                
                                html += `
                                    <div class="flex items-center p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors">
                                        <div class="flex-shrink-0 h-10 w-10">
                                            <div class="h-10 w-10 rounded-full ${color} flex items-center justify-center">
                                                <span class="font-semibold">${initial}</span>
                                            </div>
                                        </div>
                                        <div class="ml-3 flex-1">
                                            <div class="text-sm font-medium text-gray-900">
                                                ${assignment.evaluatee_user.name}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                ${assignment.evaluatee_user.email}
                                            </div>
                                        </div>
                                        <div class="flex-shrink-0">
                                            <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                ผู้รับการประเมิน
                                            </span>
                                        </div>
                                    </div>
                                `;
                            }
                        });
                    } else {
                        html += '<div class="text-center py-8"><i class="fas fa-user-slash text-4xl text-gray-300 mb-3"></i><p class="text-sm text-gray-500">ไม่พบข้อมูลผู้รับการประเมิน</p></div>';
                    }
                    
                    html += '</div>';
                    
                    $('#evaluateesContent').html(html);
                },
                error: function(xhr) {
                    console.error('Error loading evaluatees:', xhr);
                    $('#evaluateesContent').html('<div class="text-center py-8"><i class="fas fa-exclamation-triangle text-4xl text-red-300 mb-3"></i><p class="text-sm text-red-500">เกิดข้อผิดพลาดในการโหลดข้อมูล</p></div>');
                }
            });
        }

        function closeModal() {
            $('#evaluateesModal').addClass('hidden');
        }

        // Close modal when clicking outside
        $(document).on('click', '#evaluateesModal', function(e) {
            if (e.target.id === 'evaluateesModal') {
                closeModal();
            }
        });

        function deleteAssignment(id) {
            if (confirm('คุณแน่ใจหรือไม่ที่ต้องการลบรอบการประเมินนี้?\n\nการดำเนินการนี้จะลบข้อมูลทั้งหมดที่เกี่ยวข้องและไม่สามารถย้อนกลับได้')) {
                const button = event.target.closest('button');
                const originalContent = button.innerHTML;
                button.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i>กำลังลบ...';
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');

                $.ajax({
                    url: `/assignment-data/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            const successHtml = `
                                <div id="deleteSuccessMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
                                    <div class="flex items-center space-x-3">
                                        <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                                        </svg>
                                        <span>${response.message}</span>
                                    </div>
                                </div>
                            `;
                            $('body').append(successHtml);
                            
                            setTimeout(() => {
                                $('#deleteSuccessMessage').fadeOut(() => {
                                    location.reload();
                                });
                            }, 1500);
                        } else {
                            alert('เกิดข้อผิดพลาด: ' + response.message);
                            button.innerHTML = originalContent;
                            button.disabled = false;
                            button.classList.remove('opacity-50', 'cursor-not-allowed');
                        }
                    },
                    error: function(xhr) {
                        console.error('Delete error:', xhr);
                        let errorMessage = 'เกิดข้อผิดพลาดในการลบข้อมูล';
                        
                        if (xhr.responseJSON && xhr.responseJSON.message) {
                            errorMessage = xhr.responseJSON.message;
                        } else if (xhr.status === 419) {
                            errorMessage = 'CSRF Token หมดอายุ กรุณารีเฟรชหน้าเว็บ';
                        } else if (xhr.status === 500) {
                            errorMessage = 'เกิดข้อผิดพลาดภายในเซิร์ฟเวอร์';
                        }
                        
                        alert(errorMessage);
                        button.innerHTML = originalContent;
                        button.disabled = false;
                        button.classList.remove('opacity-50', 'cursor-not-allowed');
                    }
                });
            }
        }
    </script>

    <style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            gap: 0.5rem;
        }

        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            border: 1px solid #d1d5db;
            border-radius: 0.375rem;
            color: #374151;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .pagination .page-link:hover {
            background-color: #f3f4f6;
            border-color: #9ca3af;
        }

        .pagination .page-item.active .page-link {
            background-color: #3b82f6;
            border-color: #3b82f6;
            color: white;
        }

        .pagination .page-item.disabled .page-link {
            color: #9ca3af;
            cursor: not-allowed;
            opacity: 0.5;
        }

        tbody tr {
            transition: all 0.2s ease;
        }

        tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Modal animation */
        #evaluateesModal {
            animation: fadeIn 0.2s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        #evaluateesModal > div {
            animation: slideUp 0.3s ease-out;
        }

        @keyframes slideUp {
            from {
                transform: translateY(20px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        /* Custom scrollbar for modal */
        #evaluateesContent::-webkit-scrollbar {
            width: 8px;
        }

        #evaluateesContent::-webkit-scrollbar-track {
            background: #f1f1f1;
            border-radius: 4px;
        }

        #evaluateesContent::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }

        #evaluateesContent::-webkit-scrollbar-thumb:hover {
            background: #555;
        }

        /* Status badge animation */
        .inline-flex {
            transition: all 0.3s ease;
        }

        .inline-flex:hover {
            transform: scale(1.05);
        }

        /* Button hover effects */
        .transition-colors {
            transition: color 0.3s ease, background-color 0.3s ease;
        }

        /* Table responsive */
        @media (max-width: 768px) {
            .overflow-x-auto {
                -webkit-overflow-scrolling: touch;
            }
            
            table {
                min-width: 800px;
            }
        }
    </style>
@endsection
