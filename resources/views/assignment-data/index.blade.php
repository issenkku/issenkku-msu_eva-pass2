@extends('layouts.app')

@section('title', 'จัดการรอบการประเมิน')

@section('content')
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @if(session('success'))
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
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
    <div id="errorMessage" class="fixed top-4 right-4 bg-red-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
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
                        {{-- <button onclick="location.reload()" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 transition-colors flex items-center">
                            <i class="fas fa-sync-alt mr-2"></i>รีเฟรช
                        </button> --}}
                        <a href="{{ route('assignment-data.create') }}" class="px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700 transition-colors flex items-center">
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
                    <div class="relative overflow-x-auto">
                        <table class="divide-y divide-gray-200 min-w-[900px] w-full">
                            <thead class="bg-gray-50 ">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ระยะเวลาประเมิน
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        เกณฑ์การประเมิน
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        ตำแหน่งที่เกี่ยวข้อง
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        สถานะ
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        การดำเนินการ
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($assignmentData as $assignment)
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex flex-col">
                                            <div class="text-sm font-medium text-gray-900">
                                                {{ \Carbon\Carbon::parse($assignment->start_time)->format('d/m/Y') }} - 
                                                {{ \Carbon\Carbon::parse($assignment->end_time)->format('d/m/Y') }}
                                            </div>
                                            <div class="text-sm text-gray-500">
                                                {{ \Carbon\Carbon::parse($assignment->start_time)->diffInDays($assignment->end_time) + 1 }} วัน
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="text-sm text-gray-900">
                                            @php
                                                $reportData = $assignment->assignments->first()?->report?->reportData;
                                            @endphp
                                            {{ $reportData?->report_title ?? '-' }}
                                        </div>
                                        @if($reportData?->report_description)
                                        <div class="text-sm text-gray-500 mt-1">
                                            {{ Str::limit($reportData->report_description, 50) }}
                                        </div>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="space-y-3">
                                            @php
                                                $evaluateePosition = $assignment->evaluateePosition;
                                                $evaluatorPosition = $assignment->evaluatorPosition;
                                            @endphp
                                            
                                            @if($evaluateePosition)
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                    <i class="fas fa-user-check mr-1"></i>
                                                    ผู้รับการประเมิน: {{ $evaluateePosition->name }}
                                                </span>
                                            </div>
                                            @endif
                                            
                                            @if($evaluatorPosition)
                                            <div>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                    <i class="fas fa-user-tie mr-1"></i>
                                                    ผู้ประเมิน: {{ $evaluatorPosition->name }}
                                                </span>
                                            </div>
                                            @endif
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $now = now();
                                            $startTime = \Carbon\Carbon::parse($assignment->start_time);
                                            $endTime = \Carbon\Carbon::parse($assignment->end_time);
                                        @endphp
                                        
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
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div class="flex items-center space-x-2">
                                            <button onclick="editAssignment({{ $assignment->id }})" 
                                                class="px-3 py-1 bg-yellow-500 text-white text-xs rounded-md hover:bg-yellow-600 transition-colors" 
                                                title="แก้ไข">
                                                แก้ไข
                                            </button>
                                            <button onclick="deleteAssignment({{ $assignment->id }})" 
                                                class="px-3 py-1 bg-red-500 text-white text-xs rounded-md hover:bg-red-600 transition-colors" 
                                                title="ลบ">
                                                ลบ
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
    </body>

    <script>
        $(document).ready(function() {
            // Setup CSRF token for all AJAX requests
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            // Auto hide notifications after 5 seconds
            setTimeout(function() {
                $('#successMessage, #errorMessage').fadeOut();
            }, 5000);
        });

        function editAssignment(id) {
            // Redirect to edit page
            window.location.href = `/assignment-data/${id}/edit`;
        }

        function deleteAssignment(id) {
            if (confirm('คุณแน่ใจหรือไม่ที่ต้องการลบรอบการประเมินนี้? การดำเนินการนี้ไม่สามารถย้อนกลับได้')) {
                // Show loading
                const button = event.target;
                const originalContent = button.innerHTML;
                button.innerHTML = 'กำลังลบ...';
                button.disabled = true;
                button.classList.add('opacity-50', 'cursor-not-allowed');

                $.ajax({
                    url: `/assignment-data/${id}`,
                    type: 'DELETE',
                    success: function(response) {
                        if (response.success) {
                            // Show success message
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
                            
                            // Auto hide after 3 seconds
                            setTimeout(() => {
                                $('#deleteSuccessMessage').fadeOut(() => {
                                    location.reload();
                                });
                            }, 2000);
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

        /* Custom pagination styles */
        .pagination {
            display: flex;
            justify-content: center;
            align-items: center;
            space-x: 1rem;
        }

        .pagination .page-link {
            padding: 0.5rem 0.75rem;
            margin: 0 0.125rem;
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
        }

        /* Table hover effects */
        tbody tr:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
        }

        /* Status badges animation */
        .inline-flex {
            transition: all 0.3s ease;
        }

        .inline-flex:hover {
            transform: scale(1.05);
        }

        /* Modal animation */
        .fixed {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Button hover effects */
        .transition-colors {
            transition: color 0.3s ease, background-color 0.3s ease;
        }

        /* Cards hover effect */
        .bg-white {
            transition: all 0.3s ease;
        }

        .bg-white:hover {
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }
    </style>
@endsection
