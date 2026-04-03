{{-- style ของหน้าตั้งค่าข้อมูลมหาวิทยาลัย ถูกแยกออกจากหน้าแม่เพื่อลดความยาวไฟล์ --}}
<style>
    .form-container {
        background: #ffffff;
        min-height: 100vh;
        padding: 40px 0;
    }

    .card-custom {
        background: #ffffff;
        border: 1px solid #dee2e6;
        border-radius: 8px;
        box-shadow: 0 2px 8px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .card-header-custom {
        background: #f3e8ff;
        color: #495057;
        padding: 30px;
        text-align: center;
        border-bottom: 1px solid #dee2e6;
    }

    .card-header-custom h1 {
        margin: 0;
        font-weight: 600;
        font-size: 1.75rem;
        color: #212529;
    }

    .card-header-custom p {
        margin: 10px 0 0 0;
        color: #6c757d;
        font-size: 0.95rem;
    }

    .form-group-custom {
        margin-bottom: 24px;
        position: relative;
    }

    .form-control-custom {
        border: 1px solid #ced4da;
        border-radius: 4px;
        padding: 12px 16px;
        font-size: 14px;
        transition: all 0.2s ease;
        background: #ffffff;
        color: #495057;
    }

    .form-control-custom:focus {
        border-color: #495057;
        box-shadow: 0 0 0 0.2rem rgba(73, 80, 87, 0.15);
        background: #ffffff;
        outline: none;
    }

    .form-label-custom {
        font-weight: 500;
        color: #495057;
        margin-bottom: 8px;
        font-size: 14px;
    }

    .alert-custom {
        border: 1px solid #f5c6cb;
        border-radius: 4px;
        padding: 12px 16px;
        margin-top: 8px;
        background: #f8d7da;
        color: #721c24;
    }

    .alert-success-custom {
        background: #d4edda;
        color: #155724;
        border: 1px solid #c3e6cb;
        border-radius: 4px;
        padding: 12px 16px;
        margin-bottom: 20px;
    }

    .form-icon {
        position: absolute;
        right: 12px;
        top: 50%;
        transform: translateY(-50%);
        color: #6c757d;
        font-size: 16px;
    }

    .input-group-custom {
        position: relative;
    }

    .info-box {
        background: #f8f9fa;
        border: 1px solid #dee2e6;
        border-radius: 4px;
        padding: 20px;
        border-left: 4px solid #495057;
    }

    .info-box h6 {
        color: #495057;
        margin-bottom: 12px;
        font-weight: 600;
        font-size: 14px;
    }

    .info-box p {
        margin: 8px 0;
        color: #495057;
        font-size: 14px;
    }

    .info-box small {
        color: #6c757d;
        font-size: 12px;
    }

    .text-center {
        text-align: center;
    }

    .mt-4 {
        margin-top: 1.5rem;
    }

    .mb-0 {
        margin-bottom: 0;
    }

    .me-1 {
        margin-right: 0.25rem;
    }

    .me-2 {
        margin-right: 0.5rem;
    }

    .p-5 {
        padding: 40px;
    }

    .form-label-custom::before {
        content: '';
        margin-right: 0;
    }
</style>
