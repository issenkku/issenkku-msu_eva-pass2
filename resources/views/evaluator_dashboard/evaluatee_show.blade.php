@extends('layouts.app')
@section('content')
    <div class="evaluatee-page">
        <div class="container ">
            <!-- Header -->
            <div class="page-header">
                <h1>แบบประเมินผลงาน</h1>
                <p class="version">เวอร์ชัน: {{ $assignment['version_name'] }}</p>
            </div>

            <!-- Report Information -->
            <div class="info-card">
                <div class="card-header">
                    <h3>ข้อมูลเกณฑ์ประเมิน</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>ชื่อเกณฑ์:</label>
                            <span>{{ $assignment['report_title'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>คำอธิบายเกณฑ์:</label>
                            <span>{{ $assignment['report_description'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>ประเภท:</label>
                            <span>{{ $assignment['assessment_type'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>หมายเหตุ:</label>
                            <span>{{ $assignment['comment'] ?? '-' }}</span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Personal Information -->
            <div class="info-card">
                <div class="card-header">
                    <h3>ข้อมูลผู้รับการประเมิน</h3>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>ชื่อ-นามสกุล:</label>
                            <span>{{ $assignment['evaluatee']['name'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>ตำแหน่ง:</label>
                            <span>{{ $assignment['evaluatee']['position'] ?? '-' }}</span>
                        </div>
                        <div class="info-item">
                            <label>หน่วยงาน:</label>
                            <span>{{ $assignment['evaluatee']['department'] ?? '-' }}</span>
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
                                        {{ number_format($category->evaluationLists->sum('sum_score'), 2) }}</span>
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
                                            @if (!empty($criteria->evidence_link))
                                                <li>
                                                    {{ $criteria->name }}:
                                                    <a href="{{ $criteria->evidence_link }}" target="_blank">ดูหลักฐาน</a>
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
                                            <th>คะแนนเต็ม</th>
                                            <th>คะแนนที่ให้</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($qualityLists as $list)
                                            @foreach ($list->qualitySubCriterias as $criteria)
                                                <tr>
                                                    <td class="text-left">{{ $criteria->name }}</td>
                                                    <td>
                                                        @if (!empty($criteria->evidence_link))
                                                            <a href="{{ $criteria->evidence_link }}"
                                                                target="_blank">ดูหลักฐาน</a>
                                                        @else
                                                            -
                                                        @endif
                                                    </td>
                                                    <td>{{ number_format($criteria->num_score, 2) }}</td>

                                                    <td>
                                                        {{ is_numeric($criteria->filled_score) ? number_format($criteria->filled_score, 2) : '-' }}
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
            <!-- Additional Comment Section -->
            <div class="info-card">
                <div class="card-header" style="background-color: #fef9c3;">
                    <h3 style="color: #92400e;">ความคิดเห็นเพิ่มเติมจากผู้ประเมิน</h3>
                </div>
                <div class="card-body">
                    <p style="white-space: pre-wrap; color: #374151;">
                        {{ $assignment['comment_report'] ?? '-' }}
                    </p>
                </div>
            </div>

            <!-- Back Button -->
            <div class="action-section">
                <button type="button" class="btn-back" onclick="window.location='{{ route('evaluator.index') }}'"> <i
                        class="fas fa-arrow-left"></i>
                    ย้อนกลับ</button>
            </div>
        </div>

    </div>
@endsection
