@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\subjects\index.blade.php --}}
@section('title', 'จัดการข้อมูลรายวิชา')
@section('content')
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
        }

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

        .btn-add {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #cccccc;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            margin-bottom: 16px;
            font-size: 0.9rem;
            transition: all 0.15s ease;
        }

        .btn-add:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        /* Modal Styles */
        .modal-content-custom {
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            box-shadow: 0 4px 16px rgba(0, 0, 0, 0.1);
        }

        .modal-header-custom {
            background-color: #f8f8f8;
            color: #2c2c2c;
            border-bottom: 1px solid #e0e0e0;
            border-radius: 4px 4px 0 0;
            padding: 16px 24px;
        }

        .modal-header-custom .modal-title {
            font-weight: 500;
            font-size: 1.1rem;
        }

        .modal-body-custom {
            padding: 24px;
            background-color: #ffffff;
        }

        .form-group-modal {
            margin-bottom: 16px;
        }

        .form-control {
            border: 1px solid #cccccc;
            border-radius: 3px;
            padding: 10px 12px;
            font-size: 0.9rem;
            transition: border-color 0.15s ease;
        }

        .form-control:focus {
            border-color: #666666;
            box-shadow: 0 0 0 0.15rem rgba(102, 102, 102, 0.1);
            outline: none;
        }

        .form-label {
            font-weight: 500;
            color: #2c2c2c;
            margin-bottom: 6px;
            font-size: 0.9rem;
        }

        .btn-modal-save {
            background-color: #ffffff;
            color: #333333;
            border: 1px solid #cccccc;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            font-size: 0.9rem;
        }

        .btn-modal-save:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        .btn-modal-cancel {
            background-color: #ffffff;
            color: #666666;
            border: 1px solid #cccccc;
            padding: 10px 20px;
            border-radius: 3px;
            font-weight: 400;
            font-size: 0.9rem;
        }

        .btn-modal-cancel:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #666666;
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

        /* Alert Styles */
        .alert {
            border: 1px solid;
            border-radius: 3px;
            padding: 12px 16px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }

        .alert-success {
            background-color: #f8f9fa;
            color: #2c2c2c;
            border-color: #e0e0e0;
        }

        .alert-danger {
            background-color: #f8f9fa;
            color: #2c2c2c;
            border-color: #e0e0e0;
        }

        /* Pagination */
        .pagination .page-link {
            color: #333333;
            border: 1px solid #cccccc;
            padding: 6px 10px;
            font-size: 0.85rem;
        }

        .pagination .page-link:hover {
            background-color: #f0f0f0;
            border-color: #999999;
            color: #333333;
        }

        .pagination .page-item.active .page-link {
            background-color: #333333;
            border-color: #333333;
            color: #ffffff;
        }

        /* Professional spacing and typography */
        .container-fluid {
            max-width: 1200px;
            margin: 0 auto;
            padding: 24px;
        }

        body {
            overflow: auto !important;
            padding-right: 0 !important;
        }

        /* Remove all shadows */
        * {
            box-shadow: none !important;
        }

        /* Minimal professional look */
        .btn-close {
            background: none;
            border: none;
            font-size: 1.2rem;
            color: #666666;
        }

        .btn-close:hover {
            color: #333333;
        }

        .btn i {
            color: inherit;
        }

        /* Table striped alternative */
        .table-custom tbody tr:nth-child(even) {
            background-color: #fafafa;
        }

        .table-custom tbody tr:nth-child(even):hover {
            background-color: #f0f0f0;
        }

        /* Professional delete modal */
        .delete-modal-header {
            background-color: #ffffff;
            border-bottom: 1px solid #e0e0e0;
            padding: 16px 24px;
        }

        .delete-modal-body {
            padding: 24px;
            background-color: #ffffff;
        }

        .delete-icon {
            font-size: 2rem;
            color: #dc3545;
            margin-bottom: 16px;
        }

        /* Text improvements */
        h5 {
            font-weight: 500;
        }

        .text-muted {
            color: #666666 !important;
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #dc3545;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.8rem;
            margin-top: 4px;
        }
    </style>

    {{-- บล็อกเนื้อหา --}}
    <div class="container-fluid">
        <!-- Page Header -->
        <x-header 
            title="จัดการข้อมูลรายวิชา" 
            text="ระบบจัดการข้อมูลรายวิชา" 
            icon="fas fa-book" />

        <!-- Add Button -->
        {{--  --}}
        <div class="d-flex justify-content-end mb-3">
            <x-button 
                type="primary" 
                buttonType="button"
                text="เพิ่มรายวิชา" 
                onclick="openCreateModal()" 
                icon="fas fa-plus" />
        </div>

        <x-list-toolbar
            searchPlaceholder="ค้นหารหัสหรือชื่อรายวิชา..."
            :sortOptions="[
                'code_asc' => 'รหัส A-Z',
                'code_desc' => 'รหัส Z-A',
                'name_asc' => 'ชื่อ A-Z',
                'name_desc' => 'ชื่อ Z-A',
                'latest' => 'ใหม่สุด',
                'oldest' => 'เก่าสุด',
            ]"
            :filters="[
                [
                    'name' => 'status',
                    'label' => 'สถานะ',
                    'options' => [
                        'active' => 'เปิดใช้งาน',
                        'inactive' => 'ปิดใช้งาน',
                    ],
                    'placeholder' => 'ทั้งหมด',
                ],
            ]"
        />

        <!-- Table Container -->
        {{--  --}}
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-table me-2"></i>ข้อมูลรายวิชา</h4>
            </div>

            <div class="table-responsive">
                @if (isset($subjects) && $subjects->count() > 0)
                    {{-- ตารางข้อมูล --}}
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width: 10%">ลำดับ</th>
                                <th style="width: 45%">ชื่อรายวิชา</th>
                                <th style="width: 15%">หน่วยกิต</th>
                                <th style="width: 20%">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($subjects as $index => $subject)
                                <tr>
                                    <td>{{ $subjects->firstItem() + $index }}</td>
                                    <td class="text-start">
                                        <strong>{{ $subject->code }}: {{ $subject->name_th }}</strong>
                                        @if (!empty($subject->name_en))
                                            <div class="text-muted text-sm">{{ $subject->name_en }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="fw-semibold">{{ $subject->credits }}</div>
                                        <div class="text-muted text-sm">
                                            ( {{ $subject->lecture_credits ?? 0 }} /  {{ $subject->lab_credits ?? 0 }} / {{ $subject->self_study_credits ?? 0 }}) 
                                        </div>
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2 align-items-center justify-content-center">
                                            <x-button 
                                                type="warning" 
                                                text="แก้ไข" 
                                                class="text-sm"
                                                icon="fas fa-edit"
                                                data-id="{{ $subject->id }}"
                                                data-code="{{ $subject->code }}"
                                                data-name-th="{{ $subject->name_th }}"
                                                data-name-en="{{ $subject->name_en ?? '' }}"
                                                data-credits="{{ $subject->credits }}"
                                                data-lecture-credits="{{ $subject->lecture_credits ?? 0 }}"
                                                data-lab-credits="{{ $subject->lab_credits ?? 0 }}"
                                                data-self-study-credits="{{ $subject->self_study_credits ?? 0 }}"
                                                data-role="subject-edit-trigger"
                                            />
                                            <x-button 
                                                type="danger" 
                                                text="ลบ" 
                                                class="text-sm"
                                                icon="fas fa-trash-alt"
                                                onclick="confirmDelete({{ $subject->id }})"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="p-3">
                        {{ $subjects->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-book"></i>
                        <h5>ยังไม่มีข้อมูล</h5>
                        <p>คลิกปุ่ม "เพิ่มรายวิชา" เพื่อเริ่มต้นเพิ่มข้อมูลรายวิชา</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal -->
    <x-subject-modal />

    <x-delete-warning-modal 
        text="รายวิชา" 
        formAction="{{ route('subjects.destroy', ':id') }}"
        entityUrl="/subjects" />

    @if(session('success'))
    {{--  --}}
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
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

    <script>
        // Form validation variables
        let isFormValid = false;

        function updateTotalCredits() {
            const lectureInput = document.getElementById('lecture_credits');
            const labInput = document.getElementById('lab_credits');
            const selfStudyInput = document.getElementById('self_study_credits');
            const totalInput = document.getElementById('credits');

            if (!lectureInput || !labInput || !selfStudyInput || !totalInput) {
                return 0;
            }

            const lecture = Number(lectureInput.value || 0);
            const lab = Number(labInput.value || 0);
            const selfStudy = Number(selfStudyInput.value || 0);
            const total = lecture + lab + selfStudy;

            totalInput.value = Number.isNaN(total) ? 0 : total;

            return totalInput.value;
        }

        // ฟังก์ชันตรวจสอบความถูกต้องของฟอร์ม
        function validateForm() {
            const codeInput = document.getElementById('code');
            const nameThInput = document.getElementById('name_th');
            const lectureCreditsInput = document.getElementById('lecture_credits');
            const labCreditsInput = document.getElementById('lab_credits');
            const selfStudyCreditsInput = document.getElementById('self_study_credits');
            const creditsInput = document.getElementById('credits');
            const codeError = document.getElementById('codeError');
            const nameThError = document.getElementById('nameThError');
            const lectureCreditsError = document.getElementById('lectureCreditsError');
            const labCreditsError = document.getElementById('labCreditsError');
            const selfStudyCreditsError = document.getElementById('selfStudyCreditsError');
            const creditsError = document.getElementById('creditsError');
            const submitBtn = document.getElementById('subjectSubmitBtn');

            if (!codeInput || !nameThInput || !lectureCreditsInput || !labCreditsInput || !selfStudyCreditsInput || !creditsInput || !codeError || !nameThError || !lectureCreditsError || !labCreditsError || !selfStudyCreditsError || !creditsError || !submitBtn) {
                return false;
            }

            const codeValue = codeInput.value.trim();
            const nameThValue = nameThInput.value.trim();
            const lectureCreditsValue = lectureCreditsInput.value.trim();
            const labCreditsValue = labCreditsInput.value.trim();
            const selfStudyCreditsValue = selfStudyCreditsInput.value.trim();
            const creditsValue = creditsInput.value.trim();
            let isValid = true;

            if (codeValue === '') {
                codeInput.classList.add('is-invalid');
                codeError.style.display = 'block';
                codeError.textContent = 'กรุณากรอกรหัสรายวิชา';
                isValid = false;
            } else {
                codeInput.classList.remove('is-invalid');
                codeError.style.display = 'none';
            }

            if (nameThValue === '') {
                nameThInput.classList.add('is-invalid');
                nameThError.style.display = 'block';
                nameThError.textContent = 'กรุณากรอกชื่อรายวิชา';
                isValid = false;
            } else {
                nameThInput.classList.remove('is-invalid');
                nameThError.style.display = 'none';
            }

            if (lectureCreditsValue === '' || Number.isNaN(Number(lectureCreditsValue))) {
                lectureCreditsInput.classList.add('is-invalid');
                lectureCreditsError.style.display = 'block';
                lectureCreditsError.textContent = 'กรุณากรอกหน่วยกิตบรรยาย';
                isValid = false;
            } else {
                lectureCreditsInput.classList.remove('is-invalid');
                lectureCreditsError.style.display = 'none';
            }

            if (labCreditsValue === '' || Number.isNaN(Number(labCreditsValue))) {
                labCreditsInput.classList.add('is-invalid');
                labCreditsError.style.display = 'block';
                labCreditsError.textContent = 'กรุณากรอกหน่วยกิตปฏิบัติ';
                isValid = false;
            } else {
                labCreditsInput.classList.remove('is-invalid');
                labCreditsError.style.display = 'none';
            }

            if (selfStudyCreditsValue === '' || Number.isNaN(Number(selfStudyCreditsValue))) {
                selfStudyCreditsInput.classList.add('is-invalid');
                selfStudyCreditsError.style.display = 'block';
                selfStudyCreditsError.textContent = 'กรุณากรอกหน่วยกิตศึกษาด้วยตนเอง';
                isValid = false;
            } else {
                selfStudyCreditsInput.classList.remove('is-invalid');
                selfStudyCreditsError.style.display = 'none';
            }

            if (creditsValue === '' || Number.isNaN(Number(creditsValue))) {
                creditsInput.classList.add('is-invalid');
                creditsError.style.display = 'block';
                isValid = false;
            } else {
                creditsInput.classList.remove('is-invalid');
                creditsError.style.display = 'none';
            }

            updateSubmitButton(isValid);
            isFormValid = isValid;

            return isValid;
        }

        // ฟังก์ชันอัพเดทสถานะปุ่มส่ง
        function updateSubmitButton(isValid) {
            const submitBtn = document.getElementById('subjectSubmitBtn');
            if (!submitBtn) return;

            if (isValid) {
                submitBtn.classList.remove('btn-disabled');
                submitBtn.disabled = false;
                submitBtn.style.pointerEvents = 'auto';
            } else {
                submitBtn.classList.add('btn-disabled');
                submitBtn.disabled = true;
                submitBtn.style.pointerEvents = 'none';
            }
        }

        // ฟังก์ชันเปิด modal สำหรับเพิ่มข้อมูล
        function openCreateModal() {
            clearModalBackdrop();

            const form = document.getElementById('subjectForm');
            const modalTitle = document.getElementById('subjectModalLabel');

            if (!form || !modalTitle) return;

            resetForm();

            form.action = "{{ route('subjects.store') }}";
            document.getElementById('form_method').value = 'POST';
            modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มรายวิชา';

            const modalEl = document.getElementById('subjectModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            modalEl.addEventListener('shown.bs.modal', function () {
                document.getElementById('code').focus();
            });
        }

        // ฟังก์ชันเปิด modal สำหรับแก้ไขข้อมูล
        function handleEdit(id, code, nameTh, nameEn, credits, lectureCredits, labCredits, selfStudyCredits) {
            clearModalBackdrop();

            const form = document.getElementById('subjectForm');
            const modalTitle = document.getElementById('subjectModalLabel');

            if (!form || !modalTitle) return;

            resetForm();

            form.action = `/subjects/${id}`;
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('subjectId').value = id;
            document.getElementById('code').value = code || '';
            document.getElementById('name_th').value = nameTh || '';
            document.getElementById('name_en').value = nameEn || '';
            document.getElementById('lecture_credits').value = lectureCredits ?? 0;
            document.getElementById('lab_credits').value = labCredits ?? 0;
            document.getElementById('self_study_credits').value = selfStudyCredits ?? 0;
            document.getElementById('credits').value = credits ?? '';
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขรายวิชา';

            setTimeout(() => {
                validateForm();
            }, 100);

            const modalEl = document.getElementById('subjectModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();

            modalEl.addEventListener('shown.bs.modal', function () {
                document.getElementById('code').focus();
            });
        }

        // ฟังก์ชันส่งฟอร์ม
        function submitForm() {
            if (!validateForm()) {
                return false;
            }

            const form = document.getElementById('subjectForm');
            const modalEl = document.getElementById('subjectModal');

            if (form && modalEl && isFormValid) {
                const submitBtn = document.getElementById('subjectSubmitBtn');
                if (submitBtn) {
                    const originalText = submitBtn.innerHTML;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>กำลังบันทึก...';
                    submitBtn.disabled = true;

                    setTimeout(() => {
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    }, 5000);
                }

                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                form.submit();
            }
        }

        // ฟังก์ชันรีเซ็ตฟอร์ม
        function resetForm() {
            const form = document.getElementById('subjectForm');
            if (form) {
                form.reset();
                document.getElementById('subjectId').value = '';
                document.getElementById('form_method').value = 'POST';

                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });

                const errors = ['codeError', 'nameThError', 'lectureCreditsError', 'labCreditsError', 'selfStudyCreditsError', 'creditsError'];
                errors.forEach((errorId) => {
                    const errorEl = document.getElementById(errorId);
                    if (errorEl) {
                        errorEl.style.display = 'none';
                    }
                });

                updateSubmitButton(false);
                isFormValid = false;
            }
        }

        // ฟังก์ชันเคลียร์ modal backdrop ที่ค้าง
        function clearModalBackdrop() {
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });

            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        // Event listeners
        document.addEventListener('DOMContentLoaded', function() {
            clearModalBackdrop();
            const creditsLabel = document.querySelector('#subjectModal label[for="credits"]');
            if (creditsLabel) {
                creditsLabel.innerHTML = 'หน่วยกิต <span class="text-danger">*</span>';
            }

            const creditsError = document.getElementById('creditsError');
            if (creditsError) {
                creditsError.textContent = 'กรุณากรอกหน่วยกิต';
            }

            document.querySelectorAll('[data-role="subject-edit-trigger"]').forEach((button) => {
                button.addEventListener('click', function() {
                    handleEdit(
                        this.dataset.id,
                        this.dataset.code || '',
                        this.dataset.nameTh || '',
                        this.dataset.nameEn || '',
                        this.dataset.credits || 0,
                        this.dataset.lectureCredits || 0,
                        this.dataset.labCredits || 0,
                        this.dataset.selfStudyCredits || 0
                    );
                });
            });

            const codeInput = document.getElementById('code');
            const nameThInput = document.getElementById('name_th');
            const lectureCreditsInput = document.getElementById('lecture_credits');
            const labCreditsInput = document.getElementById('lab_credits');
            const selfStudyCreditsInput = document.getElementById('self_study_credits');
            const creditsInput = document.getElementById('credits');


            if (codeInput) {
                codeInput.addEventListener('input', function() {
                    validateForm();
                });

                codeInput.addEventListener('blur', function() {
                    validateForm();
                });
            }

            if (nameThInput) {
                nameThInput.addEventListener('input', function() {
                    validateForm();
                });

                nameThInput.addEventListener('blur', function() {
                    validateForm();
                });
            }

            [lectureCreditsInput, labCreditsInput, selfStudyCreditsInput].forEach((input) => {
                if (!input) {
                    return;
                }

                input.addEventListener('input', function() {
                    validateForm();
                });

                input.addEventListener('blur', function() {
                    validateForm();
                });

                input.addEventListener('keypress', function(e) {
                    if (e.key === 'Enter') {
                        e.preventDefault();
                        if (validateForm()) {
                            submitForm();
                        }
                    }
                });
            });

            if (creditsInput) {
                creditsInput.addEventListener('input', function() {
                    validateForm();
                });

                creditsInput.addEventListener('blur', function() {
                    validateForm();
                });
            }

            const form = document.getElementById('subjectForm');
            if (form) {
                form.addEventListener('submit', function(e) {
                    e.preventDefault();
                    if (validateForm()) {
                        return true;
                    }
                    return false;
                });
            }
        });

        window.addEventListener('pageshow', function(event) {
            clearModalBackdrop();
        });

        window.addEventListener('load', function() {
            clearModalBackdrop();
        });

        // Auto-hide success/error messages after 5 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
            messages.forEach(function(message) {
                setTimeout(function() {
                    if (message.parentElement) {
                        message.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            if (message.parentElement) {
                                message.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });
        });
    </script>
@endsection
