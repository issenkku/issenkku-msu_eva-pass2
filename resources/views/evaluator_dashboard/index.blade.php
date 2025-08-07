@extends('layouts.app')

@section('content')
    <div class="dashboard-container">
        <!-- <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">Dashboard ผู้ประเมิน</h1>
                <div class="header-date">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div> -->

        <div class="main-content max-w-8xl mx-auto space-y-6">
            <div>
                <x-profile-card 
                    :user="$evaluatorInfo"
                    title="ข้อมูลผู้ประเมิน"/>

                <div class="table-card" style="margin-top: 2rem;">
                    <div class="table-header">
                        <h2 class="table-title">รายการประเมินล่าสุด</h2>
                        <div class="table-actions">
                            <form method="GET" action="{{ route('evaluator.index') }}">
                                <select name="status" onchange="this.form.submit()" class="form-select">
                                    <option value="" {{ $statusFilter == '' ? 'selected' : '' }}>แสดงทั้งหมด</option>
                                    <option value="Pending" {{ $statusFilter == 'Pending' ? 'selected' : '' }}>รอผลประเมิน
                                        (รอกดอนุมัติ)</option>
                                    <option value="Completed" {{ $statusFilter == 'Completed' ? 'selected' : '' }}>
                                        ประเมินเสร็จสิ้น (อนุมัติแล้ว)</option>
                                </select>
                            </form>
                            {{-- <button class="btn-export">
                                <i class="fas fa-download"></i> ส่งออก
                            </button> --}}
                        </div>
                    </div>

                    <div class="table-container">
                        <table class="evaluation-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>รายการที่ต้องประเมิน</th>
                                    <th>ผู้รับการประเมิน</th>
                                    <th>วันที่ส่งประเมิน</th>
                                    <th>วันที่สิ้นสุดประเมิน</th>
                                    <th>สถานะ</th>
                                    <th>จัดการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($assignments as $assignment)
                                    <tr>
                                        <td>{{ $assignment->sequence }}</td>
                                        <td class="item-name">{{ $assignment->report_title }}</td>
                                        <td>{{ $assignment->evaluatee_name }}</td>
                                        <td>{{ $assignment->start_date }}</td>
                                        <td>{{ $assignment->end_date }}</td>
                                        <td>
                                            <span class="status {{ $assignment->status_class }}"
                                                style="background-color: {{ $assignment->status_color }};">
                                                {{ $assignment->status_text }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('evaluator.evaluatee.show', $assignment->report_id) }}"
                                                    class="btn-view" title="ดูรายละเอียด">
                                                    <i class="fa-solid fa-eye"></i>
                                                </a>

                                                @if ($assignment->status_class !== 'Completed')
                                                    <a href="{{ route('evaluator.evaluatee.edit', $assignment->report_id) }}"
                                                        class="btn-edit" title="แก้ไข">
                                                        <i class="fa-solid fa-pen-to-square"></i>
                                                    </a>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center">ไม่พบรายการ</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>

                    <div class="table-footer flex justify-between items-center mt-4">
                        <div class="table-info">
                            แสดง {{ $assignments->firstItem() }} - {{ $assignments->lastItem() }} จาก
                            {{ $assignments->total() }} รายการ
                        </div>
                        <div class="pagination">
                            {{ $assignments->links() }}
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
        }

        .dashboard-container {
            /* background-color: #ffffff; */
            min-height: 100vh;
        }

        /* Header */
        .dashboard-header {
            background: #ffffff;
            padding: 1.5rem 2rem;
            border-bottom: 2px solid #e0e0e0;
            margin-bottom: 2rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-title {
            font-size: 1.75rem;
            font-weight: 600;
            color: #2c2c2c;
            letter-spacing: -0.5px;
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #666666;
            font-size: 0.95rem;
            font-weight: 500;
        }

        .header-date i {
            color: #999999;
        }

        /* Main Content */
        .main-content {
            grid-template-columns: 1fr 300px;
            gap: 2rem;
            max-width: 1400px;
            margin: 10px;
            padding: 0 2rem;
        }

        .main-contente {
            max-width: 1400px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
        }

        .content-left {
            min-width: 0;
        }

        /* Table Card */
        .table-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .table-header {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #e0e0e0;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c2c2c;
        }

        .table-actions {
            display: flex;
            gap: 0.5rem;
        }

        .btn-filter,
        .btn-export {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #cccccc;
            background: #ffffff;
            border-radius: 3px;
            color: #555555;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
            font-weight: 500;
        }

        .btn-filter:hover,
        .btn-export:hover {
            background: #f5f5f5;
            border-color: #999999;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
            background: #ffffff;
        }

        .evaluation-table th {
            background: #f8f8f8;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #444444;
            font-size: 0.875rem;
            border-bottom: 1px solid #e0e0e0;
            white-space: nowrap;
        }

        .evaluation-table td {
            padding: 1rem;
            border-bottom: 1px solid #f0f0f0;
            vertical-align: middle;
            color: #555555;
        }

        .evaluation-table tbody tr:hover {
            background: #f9f9f9;
        }

        .evaluation-table tbody tr:nth-child(even) {
            background: #fbfbfb;
        }

        .evaluation-table tbody tr:nth-child(even):hover {
            background: #f5f5f5;
        }

        .item-name {
            max-width: 300px;
            font-weight: 500;
            color: #333333;
        }

        /* Status badges - เรียบง่าย ไม่มีสีสัน */
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 3px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
            border: 1px solid;
        }

        .status.pending {
            background: #f8f8f8;
            color: #666666;
            border-color: #cccccc;
        }

        .status.completed {
            background: #f0f0f0;
            color: #333333;
            border-color: #999999;
        }

        .status.overdue {
            background: #f5f5f5;
            color: #555555;
            border-color: #aaaaaa;
        }

        /* Action buttons - เรียบง่าย */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view,
        .btn-edit,
        .btn-download {
            width: 32px;
            height: 32px;
            border: 1px solid #cccccc;
            border-radius: 3px;
            background: #ffffff;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
            color: #666666;
        }

        .btn-view:hover,
        .btn-edit:hover,
        .btn-download:hover {
            background: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        /* Table Footer */
        .table-footer {
            padding: 1rem 1.5rem;
            background: #fafafa;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e0e0e0;
        }

        .table-info {
            color: #666666;
            font-size: 0.875rem;
        }

        .pagination {
            display: flex;
            gap: 0.25rem;
        }

        .page-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid #cccccc;
            background: #ffffff;
            border-radius: 3px;
            color: #555555;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-btn:hover:not(.disabled) {
            background: #f0f0f0;
        }

        .page-btn.active {
            background: #333333;
            color: #ffffff;
            border-color: #333333;
        }

        .page-btn.disabled {
            opacity: 0.5;
            cursor: not-allowed;
        }

        /* Sidebar */
        .content-right {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
        }

        .sidebar-card {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            padding: 1.5rem;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
        }

        .sidebar-title {
            font-size: 1rem;
            font-weight: 600;
            color: #333333;
            margin-bottom: 1rem;
        }

        /* Quick Actions */
        .quick-actions {
            display: flex;
            flex-direction: column;
            gap: 0.75rem;
        }

        .action-btn {
            display: flex;
            align-items: center;
            gap: 0.75rem;
            padding: 0.75rem 1rem;
            border: 1px solid #cccccc;
            border-radius: 3px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            text-align: left;
            background: #ffffff;
            color: #555555;
        }

        .action-btn.primary {
            background: #333333;
            color: #ffffff;
            border-color: #333333;
        }

        .action-btn.primary:hover {
            background: #444444;
            border-color: #444444;
        }

        .action-btn.secondary:hover {
            background: #f0f0f0;
            border-color: #999999;
        }

        /* Responsive */
        @media (max-width: 1024px) {
            .main-content {
                grid-template-columns: 1fr;
            }

            .content-right {
                order: -1;
            }
        }

        @media (max-width: 768px) {

            .dashboard-header,
            .main-contente,
            .main-content {
                padding: 0 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .table-header {
                flex-direction: column;
                gap: 1rem;
                align-items: stretch;
            }

            .table-actions {
                justify-content: center;
            }

            .table-footer {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .evaluation-table {
                font-size: 0.8rem;
            }

            .evaluation-table th,
            .evaluation-table td {
                padding: 0.5rem;
            }

        }

        .status {
            color: #fff;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            display: inline-block;
            min-width: 100px;
            text-align: center;
        }

        .status.pending {
            background-color: #6c757d;
            /* สีเทาเข้ม */
            color: #fff;
        }

        .status.in-progress {
            background-color: #ffc107;
            /* สีเหลือง */
            color: #000;
        }

        .status.completed {
            background-color: #28a745;
            /* สีเขียว */
            color: #fff;
        }

        .status.overdue {
            background-color: #dc3545;
            /* สีแดง */
            color: #fff;
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Simple fade in animation
            $('.table-card, .sidebar-card').css('opacity', '0').animate({
                opacity: 1
            }, 200);

            // Simple hover effect for table rows
            $('.evaluation-table tbody tr').hover(
                function() {
                    $(this).addClass('hover-row');
                },
                function() {
                    $(this).removeClass('hover-row');
                }
            );

            // Auto refresh every 5 minutes
            setInterval(function() {
                console.log('Auto refreshing data...');
            }, 300000);
        });
    </script>
@endpush
