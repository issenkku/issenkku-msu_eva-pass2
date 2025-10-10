@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <x-header 
            title="บันทึกประวัติการเข้าใช้งาน" 
            text="ระบบบันทึกประวัติการเข้าใช้งานของผู้ใช้"
            icon="fas fa-history" />

        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-gradient-primary text-white py-3" style="border-radius: 15px 15px 0 0;">
                        <h5 class="mb-0">
                            <i class="fas fa-filter me-2"></i>ตัวกรองข้อมูล
                        </h5>
                    </div>
                    <div class="card-body p-4">
                        <form method="GET" action="{{ route('user.management.log') }}" class="row g-3">
                            <!-- Search Input -->
                            <div class="col-md-4">
                                <label for="search" class="form-label fw-semibold">ค้นหาชื่อผู้ใช้</label>
                                <div class="input-group">
                                    <span class="input-group-text bg-light border-end-0">
                                        <i class="fas fa-search text-muted"></i>
                                    </span>
                                    <input type="text" 
                                           class="form-control border-start-0" 
                                           id="search" 
                                           name="search" 
                                           value="{{ request('search') }}" 
                                           placeholder="กรอกชื่อผู้ใช้...">
                                </div>
                            </div>

                            <!-- Log Name Filter -->
                            <div class="col-md-3">
                                <label for="log_name" class="form-label fw-semibold">ประเภทกิจกรรม</label>
                                <select class="form-select" id="log_name" name="log_name">
                                    <option value="all" {{ request('log_name', 'all') == 'all' ? 'selected' : '' }}>
                                        ทั้งหมด
                                    </option>
                                    @foreach($logNames as $logName)
                                        <option value="{{ $logName }}" {{ request('log_name') == $logName ? 'selected' : '' }}>
                                            {{ ucfirst($logName) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <!-- Period Filter -->
                            <div class="col-md-3">
                                <label for="period" class="form-label fw-semibold">ช่วงเวลา</label>
                                <select class="form-select" id="period" name="period">
                                    <option value="all" {{ request('period', 'all') == 'all' ? 'selected' : '' }}>
                                        ทั้งหมด
                                    </option>
                                    <option value="today" {{ request('period') == 'today' ? 'selected' : '' }}>
                                        วันนี้
                                    </option>
                                    <option value="week" {{ request('period') == 'week' ? 'selected' : '' }}>
                                        สัปดาห์นี้
                                    </option>
                                    <option value="month" {{ request('period') == 'month' ? 'selected' : '' }}>
                                        เดือนนี้
                                    </option>
                                    <option value="year" {{ request('period') == 'year' ? 'selected' : '' }}>
                                        ปีนี้
                                    </option>
                                </select>
                            </div>

                            <!-- Filter Buttons -->
                            <div class="col-md-2 d-flex align-items-end">
                                <div class="w-100">
                                    <button type="submit" class="btn btn-primary w-100 mb-2">
                                        <i class="fas fa-search me-1"></i>ค้นหา
                                    </button>
                                    <a href="{{ route('user.management.log') }}" class="btn btn-outline-secondary w-100">
                                        <i class="fas fa-redo me-1"></i>รีเซ็ต
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <!-- Activity Log Table -->
        <div class="row mt-4">
            <div class="col-12">
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-gradient-primary text-white py-3 d-flex justify-content-between align-items-center" style="border-radius: 15px 15px 0 0;">
                        <h5 class="mb-0">
                            <i class="fas fa-list me-2"></i>รายการกิจกรรม
                        </h5>
                        <span class="badge bg-light text-dark px-3 py-2">
                            ทั้งหมด {{ $activities->total() }} รายการ
                        </span>
                    </div>
                    <div class="card-body p-0">
                        @if($activities->count() > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="bg-light">
                                        <tr>
                                            <th class="px-4 py-3 border-0">#</th>
                                            <th class="px-4 py-3 border-0">ผู้ใช้</th>
                                            <th class="px-4 py-3 border-0">ประเภท</th>
                                            <th class="px-4 py-3 border-0">กิจกรรม</th>
                                            <th class="px-4 py-3 border-0">รายละเอียด</th>
                                            <th class="px-4 py-3 border-0">วันที่</th>
                                            <th class="px-4 py-3 border-0 text-center">การดำเนินการ</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($activities as $index => $activity)
                                            <tr class="border-bottom">
                                                <td class="px-4 py-3">
                                                    {{ $activities->firstItem() + $index }}
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar-sm bg-gradient-info rounded-circle d-flex align-items-center justify-content-center me-3">
                                                            <i class="fas fa-user text-white"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-semibold">
                                                                {{ $activity->causer->name ?? 'ระบบ' }}
                                                            </div>
                                                            @if($activity->causer)
                                                                <small class="text-muted">{{ $activity->causer->email }}</small>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @php
                                                        $logNameClasses = [
                                                            'จัดการผู้ใช้' => 'bg-green-100 text-green-800',
                                                            'การเข้าใช้งาน' => 'bg-blue-100 text-blue-800',
                                                            'การประเมิน' => 'bg-yellow-100 text-yellow-800',
                                                            'จัดการรอบการประเมิน' => 'bg-orange-100 text-orange-800',
                                                            'จัดการตำแหน่งงาน' => 'bg-purple-100 text-purple-800',
                                                            'จัดการหน่วยงาน' => 'bg-pink-100 text-pink-800',
                                                        ];

                                                        // Normalize key to lowercase to match your data
                                                        $logKey = strtolower($activity->log_name);
                                                        $logClass = $logNameClasses[$logKey] ?? 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    <span class="badge {{ $logClass }} px-3 py-2">
                                                        {{ ucfirst($activity->log_name) }}
                                                    </span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    <span class="fw-semibold">{{ $activity->description }}</span>
                                                </td>
                                                <td class="px-4 py-3">
                                                    @if($activity->subject)
                                                        <small class="text-muted">
                                                            {{ class_basename($activity->subject_type) }} 
                                                            #{{ $activity->subject_id }}
                                                        </small>
                                                    @else
                                                        <small class="text-muted">-</small>
                                                    @endif
                                                </td>
                                                <td class="px-4 py-3">
                                                    <div class="text-nowrap">
                                                        <div class="fw-semibold">
                                                            {{ $activity->thai_created_at }}
                                                        </div>
                                                        <small class="text-muted">
                                                            {{ $activity->thai_time }}
                                                        </small>
                                                    </div>
                                                </td>
                                                <td class="px-4 py-3 text-center">
                                                    <button type="button" 
                                                            class="btn btn-outline-info btn-sm" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#activityModal{{ $activity->id }}">
                                                        <i class="fas fa-eye me-1"></i>ดูรายละเอียด
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <!-- Pagination -->
                            <div class="d-flex justify-content-between align-items-center p-4">
                                <div class="text-muted">
                                    แสดง {{ $activities->firstItem() }} ถึง {{ $activities->lastItem() }} 
                                    จากทั้งหมด {{ $activities->total() }} รายการ
                                </div>
                                {{ $activities->links() }}
                            </div>
                        @else
                            <div class="text-center py-5">
                                <div class="mb-3">
                                    <i class="fas fa-inbox fa-3x text-muted"></i>
                                </div>
                                <h5 class="text-muted">ไม่พบข้อมูลกิจกรรม</h5>
                                <p class="text-muted">ลองเปลี่ยนตัวกรองหรือค้นหาด้วยคำอื่น</p>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    @foreach($activities as $activity)
        <div class="modal fade" id="activityModal{{ $activity->id }}" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header bg-gradient-primary text-white">
                        <h5 class="modal-title">
                            <i class="fas fa-info-circle me-2"></i>รายละเอียดกิจกรรม
                        </h5>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="mb-2"><strong>ผู้ใช้: </strong> {{ $activity->causer->name ?? 'ระบบ' }}</p>
                        <p class="mb-2"><strong>ประเภท: </strong> {{ ucfirst($activity->log_name) }}</p>
                        <p class="mb-2"><strong>กิจกรรม: </strong> {{ $activity->description }}</p>
                        <p class="mb-2"><strong>วันที่และเวลา: </strong> {{ $activity->thai_created_at }} เวลา {{ $activity->thai_time }}</p>
                        @if($activity->properties->count() > 0)
                            <pre class="bg-light p-3 rounded small">
                                {{ json_encode($activity->properties, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}
                            </pre>
                        @endif
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    </div>
                </div>
            </div>
        </div>
    @endforeach

    <!-- Custom Styles -->
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #a855f7;
            --light-blue: #e3f2fd;
            --light-purple: #f3e5f5;
        }

        .bg-gradient-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        }

        .bg-soft-primary {
            background-color: var(--light-blue);
        }

        .text-primary {
            color: var(--primary-color) !important;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
        }

        .btn-outline-info {
            color: var(--primary-color);
            border-color: var(--primary-color);
        }

        .btn-outline-info:hover {
            background-color: var(--primary-color);
            border-color: var(--primary-color);
            color: #ffffffff;
        }

        .card {
            transition: all 0.3s ease;
        }

        .card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .table-hover tbody tr:hover {
            background-color: var(--light-blue);
        }

        .avatar-sm {
            width: 40px;
            height: 40px;
        }

        .bg-gradient-info {
            background: linear-gradient(135deg, #af4cddff 0%, #1a58caff 100%);
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }

        .input-group-text {
            background-color: transparent;
        }

        .badge {
            font-size: 0.75em;
            font-weight: 600;
        }

        .border-bottom {
            border-bottom: 1px solid rgba(0,0,0,0.05) !important;
        }

        .modal-header.bg-gradient-primary {
            border-bottom: none;
        }

        .btn-close-white {
            filter: invert(1) grayscale(100%) brightness(200%);
        }

        .pagination .page-link {
            color: var(--primary-color);
            border-color: #dee2e6;
        }

        .pagination .page-item.active .page-link {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border-color: var(--primary-color);
            color:#dee2e6;
        }

        .pagination .page-link:hover {
            color: var(--secondary-color);
            background-color: var(--light-blue);
            border-color: var(--primary-color);
        }

        pre {
            font-size: 0.85em;
            max-height: 300px;
            overflow-y: auto;
        }
    </style>
@endsection