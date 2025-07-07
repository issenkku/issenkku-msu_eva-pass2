@extends('layouts.app')
@section('content')
    @if (session('success'))
        <script>
            alert('{{ session('success') }}');
        </script>
    @endif

    <form action="{{ route('evaluator.evaluatee.update', $assignment->assignment_data_id) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="container">
            <!-- Header -->
            <div class="page-header">
                <h1>แบบประเมินผลงาน</h1>
                <p class="version">เวอร์ชัน:
                    {{ optional($assignment->report->reportData->criteriaVersion)->version_name ?? '-' }}</p>
            </div>

            <div class="info-card">
                <div class="card-header">
                    <h3>ข้อมูลเกณฑ์ประเมิน</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>ชื่อเกณฑ์:</label>
                            <span>{{ $assignment->report->reportData->report_title ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>คำอธิบายเกณฑ์:</label>
                            <span>{{ $assignment->report->reportData->report_description ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>ประเภท:</label>
                            <span>{{ $assignment->report->reportData->assessment_type ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>หมายเหตุ:</label>
                            <span>{{ $assignment->report->reportData->comment ?? '-' }}</span>
                        </div>
                    </div>
                </div>


                <!-- ข้อมูลผู้รับการประเมิน -->

                <!-- ข้อมูลผู้รับการประเมิน -->
                <div class="info-card">
                    <div class="card-header">
                        <h3>ข้อมูลผู้รับการประเมิน</h3>
                    </div>
                    <div class="card-body">
                        <div class="info-grid">
                            <div class="info-item">
                                <label>ชื่อ-นามสกุล:</label>
                                <span>{{ $assignment->evaluateeUser->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <label>ตำแหน่ง:</label>
                                <span>{{ $assignment->evaluateeUser->position->name ?? '-' }}</span>
                            </div>
                            <div class="info-item">
                                <label>หน่วยงาน:</label>
                                <span>{{ $assignment->evaluateeUser->department->department_name ?? '-' }}</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Categories Title -->
                <div class="section-divider">
                    <h2>หมวดหมู่เกณฑ์ประเมิน</h2>
                </div>

                <!-- Categories -->
                @foreach ($categories as $category)
                    <div class="category-card">
                        <!-- Category Header -->
                        <div class="category-header">
                            <h4>{{ $category->main_categories }}</h4>
                            <div class="category-info">
                                <div class="category-detail">
                                    <span class="detail-label">ชื่อรายการ:</span>
                                    <span>{{ $category->evaluationLists->first()->name ?? '-' }}</span>
                                    @if ($category->evaluationLists->sum('sum_score'))
                                        <span class="score-badge">คะแนนรวม:
                                            {{ number_format($category->evaluationLists->sum('sum_score')) }}</span>
                                    @endif
                                </div>
                                <div class="category-detail">
                                    <span class="detail-label">หมายเหตุ:</span>
                                    <span>{{ $category->evaluationLists->first()->annotation ?? '-' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Quantity Criteria -->
                        @php
                            $quantityLists = $category->evaluationLists->filter(function ($list) {
                                return $list->quantitySubCriterias->isNotEmpty();
                            });
                        @endphp

                        @if ($quantityLists->isNotEmpty())
                            <div class="criteria-section quantity-section">
                                <div class="criteria-header">
                                    <h5>จำนวนชั่วโมงการสอน</h5>
                                    <span class="criteria-subtitle">ข้อมูลจากจำนวนชั่วโมงการสอนจริงในแต่ละภาคการศึกษา</span>
                                </div>
                                <div class="table-container">
                                    <table class="evaluation-table">
                                        <thead>
                                            <tr>
                                                <th>ชื่อเกณฑ์ย่อย</th>
                                                <th>ค่าน้ำหนัก (A)</th>
                                                <th>ภาระงานมาตรฐาน (B)</th>
                                                <th>ภาระงานที่ทำได้ (C)</th>
                                                <th>คำนวณ (D = A × C / B)</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($quantityLists as $list)
                                                @foreach ($list->quantitySubCriterias as $criteria)
                                                    @php
                                                        $scoreA = $criteria->score_a;
                                                        $scoreB = $criteria->score_b;
                                                        $scoreC = $criteria->score_c ?? null;
                                                        $scoreD = $criteria->score_d ?? null;
                                                    @endphp
                                                    <tr>
                                                        <td class="text-left">{{ $criteria->name }}</td>
                                                        <td>{{ number_format($scoreA) }}</td>
                                                        <td>{{ number_format($scoreB) }}</td>
                                                        <td>{{ $scoreC !== null ? number_format($scoreC) : '-' }}</td>
                                                        <td>{{ $scoreD !== null ? number_format($scoreD) : '-' }}</td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                                <div class="evidence-box">
                                    <h6>ลิงก์หลักฐาน:</h6>
                                    <ul>
                                        @foreach ($quantityLists as $list)
                                            @foreach ($list->quantitySubCriterias as $criteria)
                                                @if (!empty($criteria->evidence_links))
                                                    <li>
                                                        {{ $criteria->name }}:
                                                        @foreach ($criteria->evidence_links as $index => $link)
                                                            <a href="{{ $link }}" target="_blank">ดูหลักฐาน
                                                            </a>
                                                            @if (!$loop->last)
                                                                ,
                                                            @endif
                                                        @endforeach
                                                    </li>
                                                @endif
                                            @endforeach
                                        @endforeach
                                    </ul>
                                </div>

                            </div>
                        @endif
                        <!-- Quality Criteria -->
                        @php
                            $qualityLists = $category->evaluationLists->filter(function ($list) {
                                return $list->qualitySubCriterias->isNotEmpty();
                            });
                        @endphp

                        @if ($qualityLists->isNotEmpty())
                            <div class="criteria-section quality-section">
                                <div class="criteria-header">
                                    <h5>คุณภาพการสอน</h5>
                                    <span class="criteria-subtitle">ประเมินจากนักศึกษาและการสังเกตการสอน</span>
                                </div>
                                <div class="table-container">
                                    <table class="evaluation-table">
                                        <thead>
                                            <tr>
                                                <th>ชื่อเกณฑ์ย่อย</th>
                                                <th>ลิงก์หลักฐาน</th>
                                                <th>คะแนนที่ให้</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @foreach ($qualityLists as $list)
                                                @foreach ($list->qualitySubCriterias as $criteria)
                                              
                                                    <tr>
                                                        <td class="text-left">{{ $criteria->name }}</td>
                                                        <td>
                                                            @if ($criteria->evidenceAnswers && $criteria->evidenceAnswers->count() > 0)
                                                                @foreach ($criteria->evidenceAnswers as $evidence)
                                                                    <a href="{{ $evidence->link }}" target="_blank">
                                                                        ดูหลักฐาน
                                                                    </a><br>
                                                                @endforeach
                                                            @else
                                                                -
                                                            @endif
                                                        </td>

                                                        <td>
                                                            <input type="number" name="scores[{{ $criteria->id }}]"
                                                                value="{{ old('scores.' . $criteria->id, $criteria->filled_score ?? '') }}"
                                                                min="0" max="5" step="0.1" />

                                                        </td>
                                                    </tr>
                                                @endforeach
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>

            <!-- ปุ่มส่งข้อมูล -->
            <div class="action-section">
                <button type="button" class="btn"
                    onclick="window.location='{{ route('evaluator.index') }}'">ยกเลิก</button>
                <button type="button" class="btn"
                    onclick="window.location='{{ route('evaluator.evaluatee.show', $assignment->assignment_data_id) }}'">
                    ดูตัวอย่าง
                </button>
                <button type="submit" class="btn btn-primary">บันทึกข้อมูล</button>
            </div>
            <style>
                /* Reset and Base Styles */
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }

                body {
                    font-family: 'Sarabun', Arial, sans-serif;
                    background-color: #ffffff;
                    color: #374151;
                    line-height: 1.6;
                }

                .container {
                    max-width: 1200px;
                    margin: 0 auto;
                    padding: 24px;
                }

                /* Header Styles */
                .page-header {
                    text-align: center;
                    margin-bottom: 40px;
                    padding-bottom: 24px;
                    border-bottom: 3px solid #f3f4f6;
                }

                .page-header h1 {
                    font-size: 28px;
                    font-weight: 700;
                    color: #1f2937;
                    margin-bottom: 8px;
                }

                .version {
                    font-size: 16px;
                    color: #6b7280;
                    font-weight: 500;
                }

                /* Card Styles */
                .info-card {
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    margin-bottom: 24px;
                    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);
                }

                .card-header {
                    background: #f8fafc;
                    padding: 16px 24px;
                    border-bottom: 1px solid #e5e7eb;
                    border-radius: 12px 12px 0 0;
                }

                .card-header h3 {
                    font-size: 18px;
                    font-weight: 600;
                    color: #1f2937;
                }

                .card-body {
                    padding: 24px;
                }

                /* Info Grid */
                .info-grid {
                    display: grid;
                    gap: 16px;
                }

                .info-item {
                    display: flex;
                    align-items: flex-start;
                    gap: 16px;
                }

                .info-item label {
                    flex: 0 0 160px;
                    font-weight: 600;
                    color: #4b5563;
                }

                .info-item span {
                    flex: 1;
                    color: #1f2937;
                }

                /* Section Divider */
                .section-divider {
                    text-align: center;
                    margin: 40px 0 32px;
                    padding: 24px;
                    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
                    border-radius: 12px;
                }

                .section-divider h2 {
                    font-size: 24px;
                    font-weight: 700;
                    color: #1e40af;
                }

                /* Category Card */
                .category-card {
                    background: #ffffff;
                    border: 1px solid #e5e7eb;
                    border-radius: 12px;
                    margin-bottom: 32px;
                    overflow: hidden;
                    box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
                }

                .category-header {
                    background: #f1f5f9;
                    padding: 20px 24px;
                    border-bottom: 1px solid #e2e8f0;
                }

                .category-header h4 {
                    font-size: 20px;
                    font-weight: 700;
                    color: #1e40af;
                    margin-bottom: 12px;
                }

                .category-info {
                    display: grid;
                    gap: 8px;
                }

                .category-detail {
                    display: flex;
                    align-items: center;
                    gap: 8px;
                    flex-wrap: wrap;
                }

                .detail-label {
                    font-weight: 600;
                    color: #6b7280;
                }

                .score-badge {
                    background: #dbeafe;
                    color: #1e40af;
                    padding: 4px 8px;
                    border-radius: 6px;
                    font-size: 12px;
                    font-weight: 600;
                }

                /* Criteria Section */
                .criteria-section {
                    padding: 24px;
                    border-top: 1px solid #e5e7eb;
                }

                .criteria-section:first-child {
                    border-top: none;
                }

                .quantity-section {
                    background: #f0fdf4;
                }

                .quality-section {
                    background: #fdf4ff;
                }

                .criteria-header {
                    margin-bottom: 16px;
                }

                .criteria-header h5 {
                    font-size: 16px;
                    font-weight: 600;
                    margin-bottom: 4px;
                }

                .quantity-section .criteria-header h5 {
                    color: #166534;
                }

                .quality-section .criteria-header h5 {
                    color: #7c2d12;
                }

                .criteria-subtitle {
                    font-size: 12px;
                    color: #6b7280;
                    font-style: italic;
                }

                /* Table Styles */
                .table-container {
                    overflow-x: auto;
                    border-radius: 8px;
                    border: 1px solid #e5e7eb;
                }

                .evaluation-table {
                    width: 100%;
                    border-collapse: collapse;
                    background: #ffffff;
                }

                .evaluation-table th {
                    background: #f8fafc;
                    padding: 12px 16px;
                    font-weight: 600;
                    color: #374151;
                    text-align: center;
                    border-bottom: 1px solid #e5e7eb;
                    white-space: nowrap;
                }

                .evaluation-table td {
                    padding: 12px 16px;
                    border-bottom: 1px solid #f3f4f6;
                    text-align: center;
                }

                .evaluation-table tbody tr:hover {
                    background: #f9fafb;
                }

                .evaluation-table tbody tr:last-child td {
                    border-bottom: none;
                }

                .text-left {
                    text-align: left !important;
                }

                /* Action Section */
                .action-section {
                    text-align: center;
                    margin-top: 40px;
                    padding-top: 32px;
                    border-top: 1px solid #e5e7eb;
                }

                .btn-back {
                    display: inline-flex;
                    align-items: center;
                    gap: 8px;
                    padding: 12px 24px;
                    background: #ffffff;
                    border: 1px solid #d1d5db;
                    border-radius: 8px;
                    color: #374151;
                    font-weight: 500;
                    text-decoration: none;
                    cursor: pointer;
                    transition: all 0.2s;
                    box-shadow: 0 1px 2px rgba(0, 0, 0, 0.05);
                }

                .btn-back:hover {
                    background: #f9fafb;
                    border-color: #9ca3af;
                }

                .btn-back i {
                    font-size: 14px;
                }

                /* Responsive Design */
                @media (max-width: 768px) {
                    .container {
                        padding: 16px;
                    }

                    .page-header h1 {
                        font-size: 24px;
                    }

                    .info-item {
                        flex-direction: column;
                        gap: 4px;
                    }

                    .info-item label {
                        flex: none;
                    }

                    .category-detail {
                        flex-direction: column;
                        align-items: flex-start;
                    }

                    .evaluation-table {
                        font-size: 14px;
                    }

                    .evaluation-table th,
                    .evaluation-table td {
                        padding: 8px;
                    }
                }

                .evidence-box {
                    background: #fef9c3;
                    border: 1px solid #fde047;
                    padding: 16px;
                    margin-top: 16px;
                    border-radius: 8px;
                }

                .evidence-box h6 {
                    font-weight: 600;
                    color: #92400e;
                    margin-bottom: 8px;
                }

                .evidence-box ul {
                    padding-left: 20px;
                }

                .evidence-box li {
                    margin-bottom: 6px;
                    color: #374151;
                }
            </style>
    </form>
@endsection
