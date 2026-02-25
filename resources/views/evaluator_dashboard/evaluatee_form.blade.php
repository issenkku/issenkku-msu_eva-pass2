@extends('layouts.app')
@section('content')
    {{-- @if (session('success'))
        <script>
            alert('{{ session('success') }}');
        </script>
    @endif --}}

    {{-- ฟอร์ม --}}
    <form action="{{ route('evaluator.evaluatee.update', $assignment->report_id) }}" method="POST">
        @csrf
        @method('PUT')

        {{-- บล็อกเนื้อหา --}}
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
                                    {{-- ตารางข้อมูล --}}
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
                                        @if (!empty($quantityLists[0]->quantitySubCriterias[0]->evidence_links[0]))
                                            <li>
                                                <a href="{{ $quantityLists[0]->quantitySubCriterias[0]->evidence_links[0] }}" target="_blank">ดูหลักฐาน</a>
                                            </li>
                                        @else
                                            <li>ไม่มีหลักฐาน</li>
                                        @endif
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
                            @forEach($qualityLists as $listcard)
                                <div class="criteria-section quality-section">
                                    <div class="criteria-header">
                                        <h5>{{$listcard->name}}</h5>
                                        <span class="criteria-subtitle">{{$listcard->annotation}}</span>
                                    </div>
                                    <div class="table-container">
                                        {{-- ตารางข้อมูล --}}
                                        <table class="evaluation-table">
                                            <thead>
                                                <tr>
                                                    <th>ชื่อเกณฑ์ย่อย</th>
                                                    <th>คะแนนที่ให้</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @php
                                                    $current_main_id = "";
                                                @endphp
                                                @foreach($listcard->qualitySubCriterias as $list_eva)
                                                    @php
                                                        $main_id = $list_eva->mainCriteria->id;
                                                    @endphp
                                                    @if ($list_eva->quality_main_criteria_id === $current_main_id)
                                                        <tr>
                                                            <td class="text-left">{{ $list_eva->name }}</td>
                                                            {{-- <td>
                                                                @if (!empty($list_eva->evidence_link[0]))
                                                                    <a href="{{ $list_eva->evidence_link[0] }}"
                                                                        target="_blank">ดูหลักฐาน</a>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td> --}}
                                                            <td>
                                                                <input type="number" name="scores[{{ $list_eva->id }}]"
                                                                    value=""
                                                                    min="0" max="{{$list_eva->num_score}}" step="0.1"
                                                                    class="score-input @error('scores.' . $list_eva->id) is-invalid @enderror" />

                                                                @error('scores.' . $list_eva->id)
                                                                    <div class="invalid-feedback"
                                                                        style="color: red; font-size: 0.875rem;">
                                                                        {{ $message }}
                                                                    </div>
                                                                @enderror

                                                            </td>
                                                        </tr>
                                                    @else
                                                    @php
                                                        $current_main_id = $main_id;
                                                    @endphp
                                                        <tr class="bg-lime-100">
                                                            <td class="text-left">{{$list_eva->mainCriteria->name}}</td>
                                                            <td></td>
                                                        </tr>
                                                        <tr>
                                                            <td class="text-left">{{ $list_eva->name }}</td>
                                                            {{-- <td>
                                                                @if (!empty($list_eva->evidence_links[0]))
                                                                    <a href="{{ $list_eva->evidence_links[0] }}" target="_blank">
                                                                        ดูหลักฐาน
                                                                    </a><br>
                                                                @else
                                                                    -
                                                                @endif
                                                            </td> --}}
                                                            <td>
                                                                <input type="number" name="scores[{{ $list_eva->id }}]"
                                                                    value=""
                                                                    min="0" max="{{$list_eva->num_score}}" step="0.1"
                                                                    class="score-input @error('scores.' . $list_eva->id) is-invalid @enderror" />

                                                                @error('scores.' . $list_eva->id)
                                                                    <div class="invalid-feedback"
                                                                        style="color: red; font-size: 0.875rem;">
                                                                        {{ $message }}
                                                                    </div>
                                                                @enderror
                                                            </td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <div class="evidence-box">
                                        <h6>ลิงก์หลักฐาน:</h6>
                                        <ul>
                                            @if (!empty($listcard->qualitySubCriterias[0]->evidence_links[0]))
                                                <li>
                                                    <a href="{{ $listcard->qualitySubCriterias[0]->evidence_links[0] }}" target="_blank">ดูหลักฐาน</a>
                                                </li>
                                            @else
                                                <li>ไม่มีหลักฐาน</li>
                                            @endif
                                        </ul>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                @endforeach
            </div>
            <!-- Comment Box -->
            <div class="info-card">
                <div class="card-header">
                    <h3>ความคิดเห็นเพิ่มเติม</h3>
                </div>
                <div class="card-body">
                    <textarea id="summernote" name="comment" style="min-height: 150px;">{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
                </div>
            </div>

            <!-- ปุ่มส่งข้อมูล -->
            <div class="action-section">
                <x-button 
                    type= defualt 
                    text="ย้อนกลับ" 
                    icon="fas fa-arrow-left"
                    href="{{ route('evaluator.index') }}" />
                <x-button 
                    type="primary"
                    buttonType="button" 
                    text="รับรองผล"
                    icon="fas fa-check-circle"
                    onclick="confirmSubmit()" />
            </div>
    </form>

    {{-- ฟอร์ม --}}
    <form id="reject-form" action="{{ route('evaluator.reject', $assignment->report_id) }}" method="POST"
        style="display:inline;">
        @csrf
        @method('PUT')
    </form>

    {{-- บล็อกเนื้อหา --}}
    <div id="submitConfirmationModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
        {{-- บล็อกเนื้อหา --}}
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">ยืนยันการบันทึกข้อมูล</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-600">
                        คุณแน่ใจหรือไม่ว่าต้องการบันทึกคะแนนและส่งแบบประเมิน?
                    </p>
                </div>
                <div class="items-center px-4 py-3 space-x-4">
                    <button id="cancelSubmitModalBtn" class="btn btn-secondary w-28">
                        ยกเลิก
                    </button>
                    <button id="confirmSubmitModalBtn" class="btn btn-primary w-28">
                        ยืนยัน
                    </button>
                </div>
            </div>
        </div>
    </div>

    {{-- บล็อกเนื้อหา --}}
    <div id="rejectConfirmationModal" class="fixed inset-0 bg-gray-800 bg-opacity-60 overflow-y-auto h-full w-full hidden z-50 flex items-center justify-center">
        {{-- บล็อกเนื้อหา --}}
        <div class="relative p-5 border w-full max-w-md shadow-lg rounded-xl bg-white">
            <div class="mt-3 text-center">
                <div class="mx-auto flex items-center justify-center h-12 w-12 rounded-full bg-blue-100">
                    <svg class="h-6 w-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                </div>
                <h3 class="text-lg leading-6 font-medium text-gray-900 mt-4">ยืนยันการบันทึกข้อมูล</h3>
                <div class="mt-2 px-7 py-3">
                    <p class="text-sm text-gray-600">
                        คุณแน่ใจหรือไม่ว่าต้องการบันทึกคะแนนและส่งแบบประเมิน?
                    </p>
                </div>
                <div class="items-center px-4 py-3 space-x-4">
                    <button id="cancelSubmitModalBtn" class="btn btn-secondary w-28">
                        ยกเลิก
                    </button>
                    <button id="confirmSubmitModalBtn" class="btn btn-primary w-28">
                        ยืนยัน
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Overlay -->
    {{-- บล็อกเนื้อหา --}}
    <div id="loading_overlay" class="fixed inset-0 bg-gray-900 bg-opacity-50 backdrop-blur-md flex items-center justify-center z-50 hidden">
        {{-- บล็อกเนื้อหา --}}
        <div class="bg-white p-8 rounded-lg shadow-xl text-center max-w-sm mx-4">
            <div class="animate-spin rounded-full h-16 w-16 border-b-4 border-blue-600 mx-auto mb-6"></div>
            <h3 class="text-xl font-semibold text-gray-800 mb-2">กำลังส่งข้อมูล</h3>
            <p class="text-gray-600">กรุณารอสักครู่...</p>
        </div>
    </div>

    <script>
        // Show loading overlay
        function showLoading() {
            const loadingOverlay = document.getElementById('loading_overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.remove('hidden');
            }
        }

        // Hide loading overlay
        function hideLoading() {
            const loadingOverlay = document.getElementById('loading_overlay');
            if (loadingOverlay) {
                loadingOverlay.classList.add('hidden');
            }
        }

        function confirmSubmit() {
            // เก็บ input ที่เป็นคะแนนทั้งหมด
            const scoreInputs = document.querySelectorAll('.score-input[type="number"]');
            let emptyFound = false;

            // ตรวจสอบว่า input ตัวเลขช่องใดว่างหรือไม่
            scoreInputs.forEach(input => {
                // ตรวจเฉพาะช่องที่ไม่ได้ disabled และไม่ได้ hidden
                if (!input.disabled && input.offsetParent !== null && (input.value === '' || input.value ===
                        null)) {
                    emptyFound = true;
                }
            });

            if (emptyFound) {
                alert("กรุณากรอกคะแนนให้ครบทุกช่องก่อนบันทึกข้อมูล");
                return; 
            }

            // 2. เปิด Modal แทนการใช้ confirm()
            const modal = document.getElementById('submitConfirmationModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        function confirmReject() {
            const modal = document.getElementById('rejectConfirmationModal');
            if (modal) {
                modal.classList.remove('hidden');
            }
        }

        document.addEventListener('DOMContentLoaded', function() {
            // Submit Modal Elements
            const submitModal = document.getElementById('submitConfirmationModal');
            const cancelSubmitBtn = document.getElementById('cancelSubmitModalBtn');
            const confirmSubmitBtn = document.getElementById('confirmSubmitModalBtn');

            // Reject Modal Elements
            const rejectModal = document.getElementById('rejectConfirmationModal');
            const cancelRejectBtn = document.getElementById('cancelRejectModalBtn');
            const confirmRejectBtn = document.getElementById('confirmRejectModalBtn');

            // Submit Modal Event Listeners
            if (submitModal && cancelSubmitBtn && confirmSubmitBtn) {
                // ปุ่มยกเลิกการส่ง
                cancelSubmitBtn.addEventListener('click', function() {
                    submitModal.classList.add('hidden');
                });

                // ปุ่มยืนยันการส่ง
                confirmSubmitBtn.addEventListener('click', function() {
                    const form = document.querySelector('#approve_eva');
                    if (form) {
                        // แสดง loading overlay
                        showLoading();

                        // สร้าง hidden input เพื่อบอกว่าเป็นการส่งแบบอนุมัติ
                        const input = document.createElement('input');
                        input.type = 'hidden';
                        input.name = 'change_status';
                        input.value = '1'; 
                        form.appendChild(input);
                        
                        // ปิด Modal แล้วส่งฟอร์ม
                        submitModal.classList.add('hidden');
                        
                        // เพิ่มการจัดการ error สำหรับกรณีที่ส่งข้อมูลไม่สำเร็จ
                        form.addEventListener('submit', function() {
                            // ถ้า form ถูก submit แล้วแต่ยังอยู่ในหน้าเดิม (เกิด error) ให้ซ่อน loading
                            setTimeout(function() {
                                hideLoading();
                            }, 5000); // ซ่อน loading หลังจาก 5 วินาที
                        });
                        
                        form.submit();
                        //window.location.href = "/evaluator-dashboard"; // Redirect ไปยังหน้า index
                    }
                });

                // ปิด Modal เมื่อคลิกพื้นหลัง
                submitModal.addEventListener('click', function(event) {
                    if (event.target === submitModal) {
                        submitModal.classList.add('hidden');
                    }
                });
            }

            // Reject Modal Event Listeners
            if (rejectModal && cancelRejectBtn && confirmRejectBtn) {
                // ปุ่มยกเลิกการปฏิเสธ
                cancelRejectBtn.addEventListener('click', function() {
                    rejectModal.classList.add('hidden');
                });

                // ปุ่มยืนยันการปฏิเสธ
                confirmRejectBtn.addEventListener('click', function() {
                    const rejectForm = document.getElementById('reject-form');
                    if (rejectForm) {
                        // แสดง loading overlay
                        showLoading();

                        // ปิด Modal แล้วส่งฟอร์ม
                        rejectModal.classList.add('hidden');
                        
                        // เพิ่มการจัดการ error สำหรับกรณีที่ส่งข้อมูลไม่สำเร็จ
                        rejectForm.addEventListener('submit', function() {
                            setTimeout(function() {
                                hideLoading();
                            }, 5000);
                        });
                        
                        rejectForm.submit();
                    }
                });

                // ปิด Modal เมื่อคลิกพื้นหลัง
                rejectModal.addEventListener('click', function(event) {
                    if (event.target === rejectModal) {
                        rejectModal.classList.add('hidden');
                    }
                });
            }

            // ซ่อน loading overlay เมื่อหน้าโหลดเสร็จ (กรณีที่ redirect กลับมา)
            window.addEventListener('load', function() {
                hideLoading();
            });

            // ซ่อน loading overlay เมื่อกลับมาที่หน้านี้
            window.addEventListener('pageshow', function() {
                hideLoading();
            });
        });
    </script>
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

        .action-section {
            display: flex;
            justify-content: center;
            gap: 16px;
            margin-top: 40px;
            padding-top: 32px;
            border-top: 1px solid #e5e7eb;
            flex-wrap: wrap;
            /* เพิ่มเติมเพื่อรองรับมือถือ */
        }

        .btn {
            padding: 12px 24px;
            font-weight: 600;
            border-radius: 8px;
            border: none;
            cursor: pointer;
            transition: all 0.2s;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.05);
        }

        .btn-secondary {
            background-color: #6b7280;
            color: #fff;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .btn-primary {
            background-color: #2563eb;
            color: #fff;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-warning {
            background-color: #facc15;
            color: #111827;
        }

        .btn-warning:hover {
            background-color: #fbbf24;
        }

        .score-input {
            width: 50%;
            padding: 4px 8px;
            border: 1px solid #ccc;
            border-radius: 4px;
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.2s ease-in-out, box-shadow 0.2s ease-in-out;
        }

        .score-input:focus {
            border-color: #3b82f6;
            /* สีฟ้า */
            box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
            outline: none;
        }

        .is-invalid {
            border-color: #dc3545;
        }

        .secondary {
            background-color: #6b7280;
            color: #111827;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .primary {
            background-color: #2563eb;
            color: #111827;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .warning {
            background-color: #facc15;
            color: #111827;
        }

        .btn-warning:hover {
            background-color: #fbbf24;
        }
    </style>
@endsection
