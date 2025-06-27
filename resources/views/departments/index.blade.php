@extends('layout')
@section('title', 'จัดการข้อมูลแผนก')
@section('content')
    <style>
        .page-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px 0;
            border-radius: 15px;
            margin-bottom: 30px;
            text-align: center;
        }

        .table-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
            margin-bottom: 30px;
        }

        .table-header {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            color: white;
            padding: 20px;
            text-align: center;
        }

        .table-custom {
            margin: 0;
        }

        .table-custom thead th {
            background: #f8f9fa;
            border: none;
            padding: 20px;
            font-weight: 600;
            color: #495057;
            text-align: center;
        }

        .table-custom tbody td {
            padding: 20px;
            vertical-align: middle;
            text-align: center;
            border: none;
            border-bottom: 1px solid #e9ecef;
        }

        .table-custom tbody tr:hover {
            background: #f8f9fa;
            transform: translateX(5px);
            transition: all 0.3s ease;
        }

        .btn-action {
            padding: 8px 15px;
            border-radius: 20px;
            font-weight: 500;
            margin: 0 3px;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(135deg, #3498db, #2980b9);
            color: white;
            border: none;
        }

        .btn-edit:hover {
            background: linear-gradient(135deg, #2980b9, #21618c);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52, 152, 219, 0.4);
        }

        .btn-delete {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
            border: none;
        }

        .btn-delete:hover {
            background: linear-gradient(135deg, #c0392b, #a93226);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        .btn-add {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin-bottom: 20px;
        }

        .btn-add:hover {
            background: linear-gradient(135deg, #27ae60, #229954);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }

        /* Modal Styles */
        .modal-content-custom {
            border: none;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
        }

        .modal-header-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            border: none;
            border-radius: 20px 20px 0 0;
            padding: 25px;
        }

        .modal-body-custom {
            padding: 30px;
        }

        .form-group-modal {
            margin-bottom: 20px;
        }

        .form-control-modal {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .form-control-modal:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
            outline: none;
        }

        .form-label-modal {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .btn-modal-save {
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
        }

        .btn-modal-save:hover {
            background: linear-gradient(135deg, #27ae60, #229954);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        }

        .btn-modal-cancel {
            background: linear-gradient(135deg, #95a5a6, #7f8c8d);
            color: white;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
        }

        .btn-modal-cancel:hover {
            background: linear-gradient(135deg, #7f8c8d, #5d6d7e);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(149, 165, 166, 0.4);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 4rem;
            margin-bottom: 20px;
            opacity: 0.5;
        }

        /* Force hide modal backdrop */
        .modal-backdrop {
            display: none !important;
        }

        /* Ensure body is not locked */
        body {
            overflow: auto !important;
            padding-right: 0 !important;
        }
    </style>

    <!-- Page Header -->
    <div class="page-header">
        <h2><i class="fas fa-building me-2"></i>จัดการข้อมูลแผนก</h2>
        <p class="mb-0">ระบบจัดการข้อมูลแผนกและคณะ</p>
    </div>

    <!-- Add Button -->
    <div class="d-flex justify-content-end">
        <button type="button" class="btn btn-add" onclick="openCreateModal()">
            <i class="fas fa-plus me-2"></i>เพิ่มข้อมูล
        </button>
    </div>

    <!-- Table Container -->
    <div class="table-container">
        <div class="table-header">
            <h4><i class="fas fa-table me-2"></i>แสดงข้อมูลแผนก</h4>
        </div>

        <div class="table-responsive">
            @if (isset($departments) && $departments->count() > 0)
                <table class="table table-custom">
                    <thead>
                        <tr>
                            <th>ลำดับ</th>
                            <th>ชื่อแผนก</th>
                            <th>ชื่อคณะ</th>
                            <th>การจัดการ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($departments as $index => $department)
                            <tr>
                                <td>{{ $index + 1 }}</td>
                                <td>{{ $department->department_name }}</td>
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
                <!-- ลิงก์แบ่งหน้า -->
                {{ $departments->links() }}
            @else
                <div class="empty-state">
                    <i class="fas fa-inbox"></i>
                    <h5>ยังไม่มีข้อมูล</h5>
                    <p>คลิกปุ่ม "เพิ่มข้อมูล" เพื่อเริ่มต้นเพิ่มข้อมูลแผนก</p>
                </div>
            @endif
        </div>
    </div>
    <!-- Flash Messages -->
    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <!-- Modal -->
    <div class="modal fade" id="departmentModal" tabindex="-1" aria-labelledby="departmentModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-lg">
            <div class="modal-content modal-content-custom">
                <div class="modal-header modal-header-custom">
                    <h5 class="modal-title" id="departmentModalLabel">
                        <i class="fas fa-plus me-2"></i>เพิ่มข้อมูลแผนก
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body modal-body-custom">
                    <form id="departmentForm" method="POST">
                        @csrf
                        <input type="hidden" name="_method" id="form_method" value="POST">
                        <input type="hidden" id="departmentId" name="id">

                        <div class="form-group mb-3">
                            <label for="department_name" class="form-label">ชื่อแผนก</label>
                            <input type="text" id="department_name" name="department_name" class="form-control" required>
                        </div>

                        <div class="form-group mb-3">
                            <label for="faculty" class="form-label">ชื่อคณะ</label>
                            <input type="text" id="faculty" name="faculty" class="form-control" required>
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
                <div class="modal-header" style="background: linear-gradient(135deg, #e74c3c, #c0392b); color: white;">
                    <h5 class="modal-title" id="deleteModalLabel">
                        <i class="fas fa-exclamation-triangle me-2"></i>ยืนยันการลบ
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Close"></button>
                </div>
                <div class="modal-body text-center">
                    <i class="fas fa-trash-alt" style="font-size: 3rem; color: #e74c3c; margin-bottom: 20px;"></i>
                    <h5>คุณต้องการลบข้อมูลนี้หรือไม่?</h5>
                    <p class="text-muted">การลบข้อมูลนี้ไม่สามารย้อนกลับได้</p>
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
            // เคลียร์ backdrop ที่ค้างก่อน
            clearModalBackdrop();

            const form = document.getElementById('departmentForm');
            const modalTitle = document.getElementById('departmentModalLabel');

            if (!form || !modalTitle) return;

            // รีเซ็ตฟอร์ม
            resetForm();

            // ตั้งค่าฟอร์มสำหรับเพิ่มข้อมูล
            form.action = "{{ route('departments.store') }}";
            document.getElementById('form_method').value = 'POST';
            modalTitle.innerHTML = '<i class="fas fa-plus me-2"></i>เพิ่มข้อมูลแผนก';

            // เปิด modal ด้วย Bootstrap API
            const modalEl = document.getElementById('departmentModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ฟังก์ชันเปิด modal สำหรับแก้ไขข้อมูล
        function handleEdit(id, name, faculty) {
            // เคลียร์ backdrop ที่ค้างก่อน
            clearModalBackdrop();

            const form = document.getElementById('departmentForm');
            const modalTitle = document.getElementById('departmentModalLabel');

            if (!form || !modalTitle) return;

            // รีเซ็ตฟอร์มก่อน
            resetForm();

            // ตั้งค่าฟอร์มสำหรับแก้ไข
            form.action = `/departments/${id}`;
            document.getElementById('form_method').value = 'PUT';
            document.getElementById('departmentId').value = id;
            document.getElementById('department_name').value = name;
            document.getElementById('faculty').value = faculty;
            modalTitle.innerHTML = '<i class="fas fa-edit me-2"></i>แก้ไขข้อมูลแผนก';

            // เปิด modal ด้วย Bootstrap API
            const modalEl = document.getElementById('departmentModal');
            const modal = new bootstrap.Modal(modalEl);
            modal.show();
        }

        // ฟังก์ชันส่งฟอร์ม
        function submitForm() {
            const form = document.getElementById('departmentForm');
            const modalEl = document.getElementById('departmentModal');

            if (form && modalEl) {
                // ปิด modal
                const modal = bootstrap.Modal.getInstance(modalEl);
                if (modal) {
                    modal.hide();
                }

                // ส่งฟอร์มทันที
                form.submit();
            }
        }

        // ฟังก์ชันยืนยันการลบ
        function confirmDelete(id) {
            // เคลียร์ backdrop ที่ค้างก่อน
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

                // เคลียร์ error states
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
            // ปิด modal ทั้งหมดที่เปิดอยู่
            const openModals = document.querySelectorAll('.modal.show');
            openModals.forEach(modal => {
                const modalInstance = bootstrap.Modal.getInstance(modal);
                if (modalInstance) {
                    modalInstance.hide();
                }
            });

            // ลบ backdrop ทั้งหมด
            const backdrops = document.querySelectorAll('.modal-backdrop');
            backdrops.forEach(backdrop => {
                backdrop.remove();
            });

            // เคลียร์ body classes และ styles
            document.body.classList.remove('modal-open');
            document.body.style.removeProperty('overflow');
            document.body.style.removeProperty('padding-right');
        }

        // เมื่อโหลดหน้าเสร็จ เคลียร์ backdrop ที่อาจค้าง
        document.addEventListener('DOMContentLoaded', function() {
            clearModalBackdrop();
        });

        // เมื่อกลับมาที่หน้านี้ เคลียร์ backdrop
        window.addEventListener('pageshow', function(event) {
            clearModalBackdrop();
        });

        // เมื่อหน้าเว็บโหลดใหม่หรือ refresh
        window.addEventListener('load', function() {
            clearModalBackdrop();
        });
    </script>

@endsection
