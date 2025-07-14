@extends('layouts.app')
@section('title', 'จัดการข้อมูลแผนก')
@section('content')
    <style>
        body {
            background-color: #ffffff;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #333333;
        }

        .page-header {
            background-color: #ffffff;
            border: 1px solid #e0e0e0;
            padding: 24px 32px;
            border-radius: 4px;
            margin-bottom: 24px;
            text-align: center;
        }

        .page-header h2 {
            color: #2c2c2c;
            margin-bottom: 6px;
            font-weight: 500;
            font-size: 1.75rem;
        }

        .page-header p {
            color: #666666;
            margin: 0;
            font-size: 0.95rem;
        }

        .table-container {
            background: #ffffff;
            border: 1px solid #e0e0e0;
            border-radius: 4px;
            overflow: hidden;
            margin-bottom: 24px;
        }

        .table-header {
            background-color: #f8f8f8;
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

        /* Delete Modal Specific */
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

        /* Icon styling */
        i {
            color: #666666;
        }

        .btn i {
            color: inherit;
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

    <div class="container-fluid">
        <!-- Page Header -->
        <div class="page-header">
            <h2><i class="fas fa-building me-2"></i>จัดการข้อมูลแผนก</h2>
            <p>ระบบจัดการข้อมูลแผนกและคณะ</p>
        </div>

        <!-- Add Button -->
        <div class="d-flex justify-content-end">
            <button type="button" class="btn btn-add" onclick="openCreateModal()">
                <i class="fas fa-plus me-2"></i>เพิ่มข้อมูล
            </button>
        </div>

        <!-- Flash Messages -->
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="fas fa-check-circle me-2"></i>{{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger" style="background-color: #f8d7da; color: #842029; border: 1px solid #f5c2c7;">
                <i class="fas fa-exclamation-circle me-2"></i>
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <!-- Table Container -->
        <div class="table-container">
            <div class="table-header">
                <h4><i class="fas fa-table me-2"></i>ข้อมูลแผนกและคณะ</h4>
            </div>

            <div class="table-responsive">
                @if (isset($departments) && $departments->count() > 0)
                    <table class="table table-custom">
                        <thead>
                            <tr>
                                <th style="width: 10%">ลำดับ</th>
                                <th style="width: 35%">ชื่อแผนก</th>
                                <th style="width: 35%">ชื่อคณะ</th>
                                <th style="width: 20%">การจัดการ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($departments as $index => $department)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td><strong>{{ $department->department_name }}</strong></td>
                                    <td>{{ $department->faculty }}</td>
                                    <td>
                                        <button class="btn btn-action btn-edit"
                                            onclick="handleEdit({{ $department->id }}, '{{ $department->department_name }}', '{{ $department->faculty }}')">
                                            <i class="fas fa-edit me-1"></i>แก้ไข
                                        </button>
                                        <button class="btn btn-action btn-delete"
                                            onclick="confirmDelete({{ $department->id }})">
                                            <i class="fas fa-trash me-1"></i>ลบ
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>

                    <!-- Pagination -->
                    <div class="p-3">
                        {{ $departments->links() }}
                    </div>
                @else
                    <div class="empty-state">
                        <i class="fas fa-building"></i>
                        <h5>ยังไม่มีข้อมูล</h5>
                        <p>คลิกปุ่ม "เพิ่มข้อมูล" เพื่อเริ่มต้นเพิ่มข้อมูลแผนก</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="departmentModalLabel">
                        <i class="fas fa-plus me-2"></i>เพิ่มข้อมูลแผนก
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <form id="departmentForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="form_method" value="POST">
                        <input type="hidden" id="departmentId" name="id">

                        <div class="mb-3">
                            <label for="department_name" class="form-label">ชื่อแผนก <span
                                    class="text-danger">*</span></label>
                            <input type="text" id="department_name" name="department_name" class="form-control" required
                                placeholder="กรุณาระบุชื่อแผนก">
                        </div>

                        <div class="mb-3">
                            <label for="faculty" class="form-label">ชื่อคณะ <span class="text-danger">*</span></label>
                            <input type="text" id="faculty" name="faculty" class="form-control" required
                                placeholder="กรุณาระบุชื่อคณะ">
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>ยกเลิก
                    </button>
                    <button type="button" class="btn btn-modal-save" onclick="submitForm()">
                        <i class="fas fa-save me-2"></i>บันทึก
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- Delete Confirmation Modal -->
    <div class="modal fade" id="deleteModal" tabindex="-1" aria-labelledby="deleteModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content modal-content-custom">
                <div class="modal-header delete-modal-header">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการลบ
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body delete-modal-body text-center">
                    <i class="fas fa-trash-alt delete-icon"></i>
                    <h5 style="color: #2c2c2c; margin-bottom: 8px;">คุณต้องการลบข้อมูลนี้หรือไม่?</h5>
                    <p class="text-muted">การลบข้อมูลนี้ไม่สามารถย้อนกลับได้</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-modal-cancel" data-bs-dismiss="modal">
                        <i class="fas fa-times me-2"></i>ยกเลิก
                    </button>
                    <form id="deleteForm" method="POST" style="display: inline;">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-delete">
                            <i class="fas fa-trash me-2"></i>ลบข้อมูล
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        // ฟังก์ชันเปิด modal สำหรับเพิ่มข้อมูล
        function openCreateModal() {
            clearModalBackdrop();

            const form = document.getElementById('departmentForm');
            const modalTitle = document.getElementById('departmentModalLabel');

            if (!form || !modalTitle) return;

            resetForm();

            form.action = "{{ route('departments.store') }}";
            document.getElementById('form_method').value = 'POST';
            modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มข้อมูลแผนก';

            const modalEl = document.getElementById('departmentModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ฟังก์ชันเปิด modal สำหรับแก้ไขข้อมูล
        function handleEdit(id, name, faculty) {
            clearModalBackdrop();

            const form = document.getElementById('departmentForm');
            const modalTitle = document.getElementById('departmentModalLabel');

            if (!form || !modalTitle) return;

            resetForm();

            form.action = `/departments/${id}`;
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('departmentId').value = id;
            document.getElementById('department_name').value = name;
            document.getElementById('faculty').value = faculty;
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขข้อมูลแผนก';

            const modalEl = document.getElementById('departmentModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ฟังก์ชันส่งฟอร์ม
        function submitForm() {
            const form = document.getElementById('departmentForm');
            const modalEl = document.getElementById('departmentModal');

            if (form && modalEl) {
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }
                form.submit();
            }
        }

        // ฟังก์ชันยืนยันการลบ
        function confirmDelete(id) {
            clearModalBackdrop();

            const deleteForm = document.getElementById('deleteForm');
            if (deleteForm) {
                deleteForm.action = "/departments/" + id;

                const modalEl = document.getElementById('deleteModal');
                const modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }

        // ฟังก์ชันรีเซ็ตฟอร์ม
        function resetForm() {
            const form = document.getElementById('departmentForm');
            if (form) {
                form.reset();
                document.getElementById('departmentId').value = '';
                document.getElementById('form_method').value = 'POST';

                const inputs = form.querySelectorAll('.form-control');
                inputs.forEach(input => {
                    input.classList.remove('is-invalid');
                });

                const errors = form.querySelectorAll('.invalid-feedback');
                errors.forEach(error => {
                    error.remove();
                });
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
        });

        window.addEventListener('pageshow', function(event) {
            clearModalBackdrop();
        });

        window.addEventListener('load', function() {
            clearModalBackdrop();
        });
    </script>

@endsection
