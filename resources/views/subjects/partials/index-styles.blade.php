{{-- style ของหน้าจัดการรายวิชา ถูกแยกออกจากหน้าแม่เพื่อลดความยาวไฟล์ --}}
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

    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    body {
        overflow: auto !important;
        padding-right: 0 !important;
    }

    * {
        box-shadow: none !important;
    }

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

    .table-custom tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    .table-custom tbody tr:nth-child(even):hover {
        background-color: #f0f0f0;
    }

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

    h5 {
        font-weight: 500;
    }

    .text-muted {
        color: #666666 !important;
    }

    .is-invalid {
        border-color: #dc3545;
    }

    .invalid-feedback {
        color: #dc3545;
        font-size: 0.8rem;
        margin-top: 4px;
    }
</style>
