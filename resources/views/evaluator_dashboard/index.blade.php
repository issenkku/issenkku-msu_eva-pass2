@extends('layouts.app')

@section('content')
    <div class="dashboard-container">
        <!-- Header -->
        <div class="dashboard-header">
            <div class="header-content">
                <h1 class="dashboard-title">Dashboard ผู้ประเมิน</h1>
                <div class="header-date">
                    <i class="fas fa-calendar-alt"></i>
                    <span>{{ date('d/m/Y') }}</span>
                </div>
            </div>
        </div>


        <!-- Main Content -->
        <div class="main-contente">
            <!-- Left Column - Table -->
            <div class="content-left">
                <div class="table-card">
                    <div class="table-header">
                        <h2 class="table-title">ข้อมูลผู้ประเมิน</h2>
                    </div>

                    <div class="table-container">
                        <table class="evaluation-table">
                            <thead>
                                <tr>
                                    <th>ลำดับ</th>
                                    <th>ชื่อ-สกุล</th>
                                    <th>รายละเอียดผู้ประเมิน</th>

                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>1</td>
                                    <td class="item-name">ดร.สมชาย วิทยาการ</td>
                                    <td>รหัสผู้ประเมิน: EV001 ประสบการณ์: 8 ปี คะแนนเฉลี่ย: 4.8/5.0</td>

                                </tr>


                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Main Content -->
    <div class="main-content">
        <!-- Left Column - Table -->
        <div class="content-left">
            <div class="table-card">
                <div class="table-header">
                    <h2 class="table-title">รายการประเมินล่าสุด</h2>
                    <div class="table-actions">
                        <button class="btn-filter">
                            <i class="fas fa-filter"></i>
                            ตัวกรอง
                        </button>
                        <button class="btn-export">
                            <i class="fas fa-download"></i>
                            ส่งออก
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
                            <tr>
                                <td>1</td>
                                <td class="item-name">ระบบบริหารงานบุคคล : ระบบบริหารงานบุคคลและงานเงิน</td>
                                <td>นายสมชาย ใจดี</td>
                                <td>15/06/2567</td>
                                <td>30/06/2567</td>
                                <td><span class="status pending">รอดำเนินการ</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" title="ดูรายละเอียด">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>
                                        <button class="btn-edit" title="แก้ไข">
                                            <i class="fa-solid fa-pen-to-square"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
                                <td class="item-name">ระบบบริหารงานบุคคล : ระบบบริหารงานบุคคลและงานเงิน</td>
                                <td>นางสาวมาลี สวยงาม</td>
                                <td>12/06/2567</td>
                                <td>27/06/2567</td>
                                <td><span class="status completed">ประเมินแล้ว</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-download" title="ดาวน์โหลด">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>3</td>
                                <td class="item-name">ระบบบริหารงานบุคคล : ระบบบริหารงานบุคคลและงานเงิน</td>
                                <td>นายวิชัย เก่งมาก</td>
                                <td>10/06/2567</td>
                                <td>25/06/2567</td>
                                <td><span class="status overdue">เกินกำหนด</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-edit" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>4</td>
                                <td class="item-name">ระบบบริหารงานบุคคล : ระบบบริหารงานบุคคลและงานเงิน</td>
                                <td>นางสุมาลี ประสิทธิ์</td>
                                <td>08/06/2567</td>
                                <td>23/06/2567</td>
                                <td><span class="status completed">ประเมินแล้ว</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-download" title="ดาวน์โหลด">
                                            <i class="fas fa-download"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <tr>
                                <td>5</td>
                                <td class="item-name">ระบบบริหารงานบุคคล : ระบบบริหารงานบุคคลและงานเงิน</td>
                                <td>นายประยุทธ ทำงาน</td>
                                <td>05/06/2567</td>
                                <td>20/06/2567</td>
                                <td><span class="status pending">รอดำเนินการ</span></td>
                                <td>
                                    <div class="action-buttons">
                                        <button class="btn-view" title="ดูรายละเอียด">
                                            <i class="fas fa-eye"></i>
                                        </button>
                                        <button class="btn-edit" title="แก้ไข">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="table-footer">
                    <div class="table-info">
                        แสดง 1-5 จาก 25 รายการ
                    </div>
                    <div class="pagination">
                        <button class="page-btn disabled">‹ ก่อนหน้า</button>
                        <button class="page-btn active">1</button>
                        <button class="page-btn">2</button>
                        <button class="page-btn">3</button>
                        <button class="page-btn">ถัดไป ›</button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column - Sidebar -->
        <div class="content-right">
            <!-- Quick Actions -->
            <div class="sidebar-card">
                <h3 class="sidebar-title">การดำเนินการ</h3>
                <div class="quick-actions">
                    <button class="action-btn secondary">
                        <i class="fas fa-plus"></i>
                        เริ่มประเมิน
                    </button>
                    <button class="action-btn secondary">
                        <i class="fas fa-file-export"></i>
                        แสดงรายการประเมิน
                    </button>
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


        /* Header */
        .dashboard-header {
            background: white;
            padding: 1rem 2rem;
            border-bottom: 1px solid #e9ecef;
            margin-bottom: 1rem;
        }

        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1400px;
            margin: 0 auto;
        }

        .dashboard-title {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
        }

        .header-date {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #718096;
            font-size: 0.95rem;
        }

        /* Evaluator Info */
        .evaluator-info {
            max-width: 1400px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
        }

        .evaluator-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 16px;
            padding: 2rem;
            color: white;
            display: flex;
            align-items: center;
            gap: 2rem;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            position: relative;
            overflow: hidden;
        }

        .evaluator-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 200px;
            height: 200px;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 50%;
            transform: translate(50%, -50%);
        }

        .evaluator-avatar {
            width: 80px;
            height: 80px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2rem;
            backdrop-filter: blur(10px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            flex-shrink: 0;
        }

        .evaluator-details {
            flex: 1;
        }

        .evaluator-name {
            font-size: 1.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .evaluator-position {
            opacity: 0.9;
            margin-bottom: 1rem;
            font-size: 1rem;
        }

        .evaluator-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1.5rem;
        }

        .meta-item {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            font-size: 0.875rem;
            opacity: 0.9;
        }

        .meta-item i {
            width: 16px;
        }

        .evaluator-status {
            flex-shrink: 0;
        }

        .status-badge {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 500;
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .status-badge.active i {
            color: #48bb78;
            animation: pulse 2s infinite;
        }

        @keyframes pulse {

            0%,
            100% {
                opacity: 1;
            }

            50% {
                opacity: 0.5;
            }
        }

        /* Stats Grid */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            max-width: 1400px;
            margin: 0 auto 2rem;
            padding: 0 2rem;
        }

        .stat-card {
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 1rem;
            transition: all 0.3s ease;
        }

        .stat-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(0, 0, 0, 0.1);
        }

        .stat-icon {
            width: 60px;
            height: 60px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
            color: white;
        }

        .stat-icon.total {
            background: #4a5568;
        }

        .stat-icon.pending {
            background: #d69e2e;
        }

        .stat-icon.completed {
            background: #38a169;
        }

        .stat-icon.overdue {
            background: #e53e3e;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #2d3748;
            line-height: 1;
        }

        .stat-label {
            color: #718096;
            font-size: 0.9rem;
            margin-top: 0.25rem;
        }

        /* Main Content */
        .main-content {
            display: grid;
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            max-width: 1400px;
            margin: auto;
            padding: 0 2rem;
        }

        .main-contente {
            grid-template-columns: 1fr 320px;
            gap: 2rem;
            max-width: 1400px;
            margin: 2ch auto;
            padding: 0 2rem;
        }

        .content-left {
            min-width: 0;
        }

        /* Table Card */
        .table-card {
            background: white;
            border-radius: 12px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
            overflow: hidden;
        }

        .table-header {
            padding: 1.5rem 2rem;
            border-bottom: 1px solid #e2e8f0;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .table-title {
            font-size: 1.25rem;
            font-weight: 600;
            color: #2d3748;
        }

        .table-actions {
            display: flex;
            gap: 0.75rem;
        }

        .btn-filter,
        .btn-export {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.5rem 1rem;
            border: 1px solid #cbd5e0;
            background: white;
            border-radius: 6px;
            color: #4a5568;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .btn-filter:hover,
        .btn-export:hover {
            background: #f7fafc;
            border-color: #a0aec0;
        }

        /* Table */
        .table-container {
            overflow-x: auto;
        }

        .evaluation-table {
            width: 100%;
            border-collapse: collapse;
        }

        .evaluation-table th {
            background: #f7fafc;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #4a5568;
            font-size: 0.875rem;
            border-bottom: 1px solid #e2e8f0;
            white-space: nowrap;
        }

        .evaluation-table td {
            padding: 1rem;
            border-bottom: 1px solid #e2e8f0;
            vertical-align: middle;
        }

        .evaluation-table tbody tr:hover {
            background: #f7fafc;
        }

        .item-name {
            max-width: 300px;
            font-weight: 500;
            color: #2d3748;
        }

        /* Status badges */
        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.75rem;
            font-weight: 500;
            text-align: center;
            white-space: nowrap;
        }

        .status.pending {
            background: #fef5e7;
            color: #d69e2e;
            border: 1px solid #f6e05e;
        }

        .status.completed {
            background: #f0fff4;
            color: #38a169;
            border: 1px solid #9ae6b4;
        }

        .status.overdue {
            background: #fed7d7;
            color: #e53e3e;
            border: 1px solid #feb2b2;
        }

        /* Action buttons */
        .action-buttons {
            display: flex;
            gap: 0.5rem;
        }

        .btn-view,
        .btn-edit,
        .btn-download {
            width: 32px;
            height: 32px;
            border: none;
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease;
            font-size: 0.875rem;
        }

        .btn-view {
            background: #ebf8ff;
            color: #3182ce;
        }

        .btn-view:hover {
            background: #bee3f8;
        }

        .btn-edit {
            background: #f0fff4;
            color: #38a169;
        }

        .btn-edit:hover {
            background: #c6f6d5;
        }

        .btn-download {
            background: #faf5ff;
            color: #805ad5;
        }

        .btn-download:hover {
            background: #e9d8fd;
        }

        /* Table Footer */
        .table-footer {
            padding: 1rem 2rem;
            background: #f7fafc;
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-top: 1px solid #e2e8f0;
        }

        .table-info {
            color: #718096;
            font-size: 0.875rem;
        }

        .pagination {
            display: flex;
            gap: 0.25rem;
        }

        .page-btn {
            padding: 0.5rem 0.75rem;
            border: 1px solid #cbd5e0;
            background: white;
            border-radius: 6px;
            color: #4a5568;
            font-size: 0.875rem;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .page-btn:hover:not(.disabled) {
            background: #f7fafc;
        }

        .page-btn.active {
            background: #4299e1;
            color: white;
            border-color: #4299e1;
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
            background: white;
            border-radius: 12px;
            padding: 1.5rem;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .sidebar-title {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
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
            border: none;
            border-radius: 8px;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.2s ease;
            width: 100%;
            text-align: left;
        }

        .action-btn.primary {
            background: #4299e1;
            color: white;
        }

        .action-btn.primary:hover {
            background: #3182ce;
            transform: translateY(-1px);
        }

        .action-btn.secondary {
            background: #f7fafc;
            color: #4a5568;
            border: 1px solid #e2e8f0;
        }

        .action-btn.secondary:hover {
            background: #edf2f7;
            border-color: #cbd5e0;
        }

        /* Activity List */
        .activity-list {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .activity-item {
            display: flex;
            align-items: flex-start;
            gap: 0.75rem;
            position: relative;
        }

        .activity-item:not(:last-child)::after {
            content: '';
            position: absolute;
            left: 6px;
            top: 20px;
            width: 1px;
            height: calc(100% + 0.5rem);
            background: #e2e8f0;
        }

        .activity-dot {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            margin-top: 4px;
            flex-shrink: 0;
        }

        .activity-dot.new {
            background: #4299e1;
        }

        .activity-dot.completed {
            background: #38a169;
        }

        .activity-dot.warning {
            background: #d69e2e;
        }

        .activity-title {
            font-weight: 500;
            color: #2d3748;
            font-size: 0.875rem;
        }

        .activity-desc {
            color: #718096;
            font-size: 0.8rem;
            margin: 0.25rem 0;
        }

        .activity-time {
            color: #a0aec0;
            font-size: 0.75rem;
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
            .evaluator-info,
            .stats-grid,
            .main-content {
                padding: 0 1rem;
            }

            .header-content {
                flex-direction: column;
                gap: 1rem;
                text-align: center;
            }

            .evaluator-card {
                flex-direction: column;
                text-align: center;
                gap: 1.5rem;
            }

            .evaluator-meta {
                justify-content: center;
                flex-direction: column;
                gap: 0.75rem;
            }

            .stats-grid {
                grid-template-columns: 1fr;
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
        }
    </style>
@endsection

@push('scripts')
    <script>
        $(document).ready(function() {
            // Add smooth animations
            $('.stat-card, .table-card, .sidebar-card').css('opacity', '0').animate({
                opacity: 1
            }, 300);

            // Enhanced hover effects
            $('.evaluation-table tbody tr').hover(
                function() {
                    $(this).css('transform', 'translateX(4px)');
                },
                function() {
                    $(this).css('transform', 'translateX(0)');
                }
            );

            // Auto refresh every 5 minutes
            setInterval(function() {
                console.log('Auto refreshing data...');
            }, 300000);

            // Add click animations
            $('.action-btn, .page-btn, .btn-filter, .btn-export').on('click', function() {
                $(this).css('transform', 'scale(0.95)');
                setTimeout(() => {
                    $(this).css('transform', 'scale(1)');
                }, 150);
            });
        });
    </script>
@endpush
