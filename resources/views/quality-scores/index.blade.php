@extends('layouts.app')
@section('title', 'จัดการคะแนนคุณภาพ')
@section('content')
    <style>
        .table-container {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-header {
            background-color: #f3e8ff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }

        .table-header h4 {
            color: #2c2c2c;
            margin: 0;
            font-weight: 500;
            font-size: 1.1rem;
        }

        .table-custom {
            margin: 0;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-custom thead th {
            background-color: #ffffff;
            border: none;
            border-bottom: 2px solid #e0e0e0;
            padding: 16px 24px;
            font-weight: 500;
            color: #2c2c2c;
            text-align: center;
            font-size: 0.9rem;
        }

        .table-custom tbody td {
            padding: 16px 24px;
            vertical-align: middle;
            text-align: center;
            border: none;
            border-bottom: 1px solid #f0f0f0;
            color: #333333;
            font-size: 0.9rem;
        }

        .table-custom tbody tr:hover {
            background-color: #f8f8f8;
            transition: background-color 0.15s ease;
        }

        .table-custom tbody tr:last-child td {
            border-bottom: none;
        }

        .table-custom tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table-custom tbody tr:nth-child(even):hover {
            background-color: #f0f0f0;
        }

        .btn-action {
            padding: 6px 12px;
            border-radius: 3px;
            font-weight: 400;
            margin: 0 2px;
            font-size: 0.8rem;
            border: 1px solid;
            transition: all 0.15s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #cccccc;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            margin-bottom: 16px;
            font-size: 0.9rem;
            transition: all 0.15s ease;
            text-decoration: none;
        }

        .btn-primary:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
            text-decoration: none;
        }

        .btn-edit {
            background-color: #ffffff;
            color: #333333;
            border-color: #cccccc;
        }

        .btn-edit:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        .btn-delete {
            background-color: #ffffff;
            color: #dc3545;
            border-color: #dc3545;
        }

        .btn-delete:hover {
            background-color: #dc3545;
            border-color: #dc3545;
            color: #ffffff;
        }

        .empty-state {
            text-align: center;
            padding: 48px 20px;
            color: #666666;
            background-color: #ffffff;
        }

        .empty-state i {
            font-size: 2.5rem;
            margin-bottom: 16px;
            color: #cccccc;
        }

        .empty-state h5 {
            color: #333333;
            font-weight: 500;
            margin-bottom: 8px;
        }

        .empty-state p {
            color: #666666;
            margin: 0;
        }

        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        .badge {
            padding: 6px 12px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: 500;
        }

        .badge-success {
            background-color: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background-color: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background-color: #f8d7da;
            color: #721c24;
        }

        .card {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            margin-bottom: 24px;
        }

        .card-header {
            background-color: #f8f9fa;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }

        .card-body {
            padding: 24px;
        }

        .alert {
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            border: 1px solid;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
        }

        .alert-warning {
            background-color: #fff3cd;
            border-color: #ffeaa7;
            color: #856404;
        }

        .text-primary {
            color: #007bff !important;
        }

        .bg-secondary {
            background-color: #6c757d !important;
        }

        .bg-info {
            background-color: #17a2b8 !important;
        }

        .btn-outline-primary {
            color: #007bff;
            border-color: #007bff;
            background-color: transparent;
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        .btn-outline-primary:hover {
            color: #fff;
            background-color: #007bff;
            border-color: #007bff;
        }

        .btn-outline-info {
            color: #17a2b8;
            border-color: #17a2b8;
            background-color: transparent;
            font-size: 0.75rem;
            padding: 4px 8px;
        }

        .btn-outline-info:hover {
            color: #fff;
            background-color: #17a2b8;
            border-color: #17a2b8;
        }

        .align-middle {
            vertical-align: middle !important;
        }

        .me-1 {
            margin-right: 0.25rem !important;
        }

        .me-2 {
            margin-right: 0.5rem !important;
        }

        .mb-4 {
            margin-bottom: 1.5rem !important;
        }

        .my-4 {
            margin-top: 1.5rem !important;
            margin-bottom: 1.5rem !important;
        }

        .version-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 12px 18px;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }

        .version-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .version-header:hover::before {
            opacity: 1;
        }

        .version-header h5 {
            margin: 0;
            font-size: 1.1rem;
            font-weight: 500;
            text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .version-header i {
            background: rgba(255, 255, 255, 0.2);
            padding: 6px;
            border-radius: 6px;
            font-size: 0.9rem;
        }

        .version-divider {
            border: none;
            height: 3px;
            background: #000;
            margin: 30px 0;
            border-radius: 2px;
        }
    </style>

    <div class="container-fluid">
        <!-- Header -->
        <div class="card">
            <div class="card-header">
                <h4><i class="fas fa-star me-2"></i>จัดการคะแนนคุณภาพ</h4>
                <p class="mb-0 text-muted">ระบบจัดการคะแนนคุณภาพสำหรับผู้ใช้งาน</p>
            </div>
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end mb-3">
            <a href="{{ route('quality-scores.create') }}" class="btn-primary">
                <i class="fas fa-plus me-2"></i>เพิ่มคะแนนคุณภาพ
            </a>
        </div>

        <!-- Alert Messages -->
        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle me-2"></i>{{ session('error') }}
            </div>
        @endif

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-table me-2"></i>รายงานการประเมินคุณภาพ</h4>
            </div>

            <div class="table-responsive">
                @if(isset($reportDatas) && $reportDatas->count() > 0)
                    @foreach($reportDatas as $reportData)
                        <div class="mb-4">
                            <div class="version-header">
                                <h5>
                                    <i class="fas fa-clipboard-list"></i>
                                    {{ $reportData->report_title }}
                                </h5>
                            </div>
                            
                            @if($reportData->criteriaVersion && $reportData->criteriaVersion->qualityMainCriterias->count() > 0)
                                <table class="table table-custom">
                                    <thead>
                                        <tr>
                                            <th style="width: 15%">ลำดับ</th>
                                            <th style="width: 40%">หมวดหมู่หลัก</th>
                                            <th style="width: 35%">เกณฑ์ย่อย</th>
                                            <th style="width: 10%">น้ำหนัก (%)</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($reportData->criteriaVersion->qualityMainCriterias as $mainIndex => $mainCriteria)
                                            @php
                                                $subCriteriasCount = $mainCriteria->qualitySubCriterias->count();
                                            @endphp
                                            
                                            @foreach($mainCriteria->qualitySubCriterias as $subIndex => $subCriteria)
                                                <tr>
                                                    <td>{{ $mainIndex + 1 }}.{{ $subIndex + 1 }}</td>
                                                    
                                                    <!-- แสดงชื่อหมวดหมู่หลักเฉพาะแถวแรกของแต่ละหมวด -->
                                                    @if($subIndex === 0)
                                                        <td rowspan="{{ $subCriteriasCount }}" class="align-middle" style="background-color: #f8f9fa; border-right: 2px solid #dee2e6;">
                                                            <strong>{{ $mainCriteria->name }}</strong>
                                                            @if($mainCriteria->tooltips)
                                                                <br><div>{!! $mainCriteria->tooltips !!}</div>
                                                            @endif
                                                        </td>
                                                    @endif
                                                    
                                                    <td>
                                                        <strong>{{ $subCriteria->name }}</strong>
                                                    </td>
                                                    
                                                    @if($subIndex === 0)
                                                        <td rowspan="{{ $subCriteriasCount }}" class="align-middle text-center" style="background-color: #f8f9fa; border-right: 2px solid #dee2e6;">
                                                            <span class="badge bg-secondary">{{ $mainCriteria->ratio }}%</span>
                                                        </td>
                                                    @endif
                                                </tr>
                                            @endforeach
                                        @endforeach
                                    </tbody>
                                </table>
                            @else
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    ยังไม่มีเกณฑ์การประเมินในรายงานนี้
                                </div>
                            @endif
                        </div>
                        
                        @if(!$loop->last)
                            <hr class="version-divider">
                        @endif
                    @endforeach
                @else
                    <div class="empty-state">
                        <i class="fas fa-clipboard-list"></i>
                        <h5>ยังไม่มีรายงานการประเมิน</h5>
                        <p>กรุณาสร้างรายงานการประเมินก่อน</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- คะแนนล่าสุดที่บันทึก -->
        {{-- @if(isset($qualityScores) && $qualityScores->count() > 0)
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-history me-2"></i>คะแนนล่าสุดที่บันทึก ({{ $qualityScores->total() }} รายการ)</h4>
            </div>

            <div class="table-responsive">
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th style="width: 8%">ลำดับ</th>
                            <th style="width: 25%">ผู้ใช้งาน</th>
                            <th style="width: 30%">เกณฑ์การประเมิน</th>
                            <th style="width: 15%">คะแนน</th>
                            <th style="width: 15%">วันที่บันทึก</th>
                            <th style="width: 7%">การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($qualityScores as $index => $score)
                            <tr>
                                <td>{{ ($qualityScores->currentPage() - 1) * $qualityScores->perPage() + $index + 1 }}</td>
                                <td>
                                    @if($score->user)
                                        <strong>{{ $score->user->name }}</strong>
                                        @if($score->user->email)
                                            <br><small class="text-muted">{{ $score->user->email }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">ผู้ใช้งานถูกลบ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($score->qualitySubCriteria)
                                        <strong>{{ $score->qualitySubCriteria->name }}</strong>
                                        @if($score->qualitySubCriteria->qualityMainCriteria)
                                            <br><small class="text-muted">{{ $score->qualitySubCriteria->qualityMainCriteria->name ?? 'N/A' }}</small>
                                        @endif
                                    @else
                                        <span class="text-muted">เกณฑ์ถูกลบ</span>
                                    @endif
                                </td>
                                <td>
                                    @if($score->score >= 80)
                                        <span class="badge badge-success">{{ number_format($score->score, 1) }}</span>
                                    @elseif($score->score >= 60)
                                        <span class="badge badge-warning">{{ number_format($score->score, 1) }}</span>
                                    @else
                                        <span class="badge badge-danger">{{ number_format($score->score, 1) }}</span>
                                    @endif
                                </td>
                                <td>{{ $score->created_at ? $score->created_at->format('d/m/Y H:i') : 'N/A' }}</td>
                                <td>
                                    <div class="d-flex gap-1 justify-content-center">
                                        <a href="{{ route('quality-scores.edit', $score->id) }}" class="btn-action btn-edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <form method="POST" action="{{ route('quality-scores.destroy', $score->id) }}" 
                                              style="display: inline-block;" 
                                              onsubmit="return confirm('คุณแน่ใจหรือไม่ที่จะลบคะแนนนี้?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn-action btn-delete">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <!-- Pagination -->
                <div class="p-3">
                    {{ $qualityScores->links() }}
                </div>
            </div>
        </div>
        @endif --}}
    </div>

    <script>
        function showScoreDetails(subCriteriaId) {
            // ฟังก์ชันแสดงรายละเอียดคะแนนของเกณฑ์ย่อยนั้นๆ
            // สำหรับตอนนี้จะแสดงคะแนนทั้งหมดของเกณฑ์นั้นๆ
            const url = `{{ route('quality-scores.index') }}?filter_criteria=${subCriteriaId}`;
            window.location.href = url;
        }
    </script>

@endsection
