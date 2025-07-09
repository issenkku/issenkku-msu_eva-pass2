@extends('layouts.app')
@section('content')
    @if (session('success'))
        <script>
            alert('{{ session('success') }}');
        </script>
    @endif

    <form action="{{ route('evaluator.evaluatee.update', $assignment->report_id) }}" method="POST">
        @csrf
        @method('PUT')
        <div class="evaluatee-page">
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
                                <span>รายงานการประเมินคุณภาพการปฏิบัติงานบุคลากรสายวิชาการ</span>
                            </div>
                            <div class="info-item">
                                <label>คำอธิบายเกณฑ์:</label>
                                <span>การประเมินคุณภาพการปฏิบัติงานของบุคลากรสายวิชาการ ประจำปีงบประมาณ 2568</span>
                            </div>
                            <div class="info-item">
                                <label>ประเภท:</label>
                                <span>วิชาการ</span>
                            </div>
                            <div class="info-item">
                                <label>หมายเหตุ:</label>
                                <span>ประเมินตามเกณฑ์มาตรฐานของมหาวิทยาลัย</span>
                            </div>
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
                                                                min="0" max="5" step="0.1"
                                                                class="score-input @error('scores.' . $criteria->id) is-invalid @enderror" />

                                                            @error('scores.' . $criteria->id)
                                                                <div class="invalid-feedback"
                                                                    style="color: red; font-size: 0.875rem;">
                                                                    {{ $message }}
                                                                </div>
                                                            @enderror

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
                <!-- Comment Box -->
                <div class="info-card">
                    <div class="card-header">
                        <h3>ความคิดเห็นเพิ่มเติม</h3>
                    </div>
                    <div class="card-body">
                        <textarea name="comment" rows="4" class="score-input" placeholder="ระบุความคิดเห็นเพิ่มเติมที่นี่..."
                            style="width: 100%; resize: vertical;">{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
                    </div>
                </div>
                <!-- ปุ่มส่งข้อมูล -->
                <div class="action-section">
                    <button type="button" class="btn-back"
                        onclick="window.location='{{ route('evaluator.index') }}'">ยกเลิก</button>

                    <button type="button" class="btn-back" onclick="confirmSubmit()">บันทึกข้อมูล</button>

                    <button type="button" class="btn-back" onclick="confirmReject()">ไม่อนุมัติ</button>
                </div>
            </div>
        </div>

    </form>

    <form id="reject-form" action="{{ route('evaluator.reject', $assignment->report_id) }}" method="POST"
        style="display:inline;">
        @csrf
        @method('PUT')
    </form>


    <script>
        function confirmSubmit() {
            // เก็บ input ที่เป็นคะแนนทั้งหมด
            const scoreInputs = document.querySelectorAll('.score-input[type="number"]');
            let emptyFound = false;

            // ตรวจสอบว่า input ตัวเลขช่องใดว่างหรือไม่
            scoreInputs.forEach(input => {
                if (input.value === '' || input.value === null) {
                    emptyFound = true;
                }
            });

            if (emptyFound) {
                alert("กรุณากรอกคะแนนให้ครบทุกช่องก่อนบันทึกข้อมูล");
                return; // ไม่ส่งฟอร์ม
            }

            if (confirm("คุณแน่ใจหรือไม่ว่าต้องการบันทึกคะแนนและส่งแบบประเมิน?")) {
                const form = document.querySelector('form');
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'change_status';
                input.value = '1';
                form.appendChild(input);
                form.submit();
            }
        }


        function confirmReject() {
            if (confirm("คุณแน่ใจหรือไม่ว่าต้องการไม่อนุมัติแบบประเมินนี้?")) {
                document.getElementById('reject-form').submit();
            }
        }
    </script>
@endsection
