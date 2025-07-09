@extends('layouts.app')

@section('content')
    <div class="dashboard-container">
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">Dashboard ผู้ประเมิน</h1>
                <div class="header-date">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div>

        <div class="main-content">
            <div class="content-left">
                <div class="table-card">
                    <div class="table-container">
                        <table class="evaluation-table">
                            <thead>
                                <tr>
                                    <th>ชื่อ-สกุล</th>
                                    <th>รายละเอียดผู้ประเมิน</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td class="item-name">{{ $evaluatorInfo['name'] }}</td>
                                    <td>
                                        รหัสผู้ประเมิน: {{ $evaluatorInfo['employee_id'] }} <br>
                                        ตำแหน่ง: {{ $evaluatorInfo['position'] }} <br>
                                        แผนก: {{ $evaluatorInfo['department'] }} <br>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

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
                            <button class="btn-export">
                                <i class="fas fa-download"></i> ส่งออก
                            </button>
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
