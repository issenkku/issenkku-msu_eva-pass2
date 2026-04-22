{{-- สไตล์เฉพาะหน้าประวัติการใช้งาน --}}
<style>
    :root {
        --primary-color: #2563eb;
        --secondary-color: #a855f7;
        --light-blue: #e3f2fd;
        --light-purple: #f3e5f5;
    }

    .bg-gradient-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
    }

    .bg-soft-primary {
        background-color: var(--light-blue);
    }

    .text-primary {
        color: var(--primary-color) !important;
    }

    .btn-primary {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border: none;
        transition: all 0.3s ease;
    }

    .btn-primary:hover {
        background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(102, 126, 234, 0.3);
    }

    .btn-outline-info {
        color: var(--primary-color);
        border-color: var(--primary-color);
    }

    .btn-outline-info:hover {
        background-color: var(--primary-color);
        border-color: var(--primary-color);
        color: #fff;
    }

    .card {
        transition: all 0.3s ease;
    }

    .card:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 25px rgba(0, 0, 0, 0.1);
    }

    .table-hover tbody tr:hover {
        background-color: var(--light-blue);
    }

    .avatar-sm {
        width: 40px;
        height: 40px;
    }

    .bg-gradient-info {
        background: linear-gradient(135deg, #af4cdd 0%, #1a58ca 100%);
    }

    .form-control:focus,
    .form-select:focus {
        border-color: var(--primary-color);
        box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
    }

    .input-group-text {
        background-color: transparent;
    }

    .badge {
        font-size: 0.75em;
        font-weight: 600;
    }

    .border-bottom {
        border-bottom: 1px solid rgba(0, 0, 0, 0.05) !important;
    }

    .modal-header.bg-gradient-primary {
        border-bottom: none;
    }

    .btn-close-white {
        filter: invert(1) grayscale(100%) brightness(200%);
    }

    .pagination .page-link {
        color: var(--primary-color);
        border-color: #dee2e6;
    }

    .pagination .page-item.active .page-link {
        background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
        border-color: var(--primary-color);
        color: #dee2e6;
    }

    .pagination .page-link:hover {
        color: var(--secondary-color);
        background-color: var(--light-blue);
        border-color: var(--primary-color);
    }

    pre {
        font-size: 0.85em;
        max-height: 300px;
        overflow-y: auto;
    }
</style>
