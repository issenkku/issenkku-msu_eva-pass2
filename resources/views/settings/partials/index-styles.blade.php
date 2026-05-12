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

    .field-help {
        display: block;
        margin-top: 6px;
        color: #6c757d;
        font-size: 12px;
        line-height: 1.5;
    }

    .logo-upload-row {
        display: grid;
        grid-template-columns: 76px 1fr;
        gap: 14px;
        align-items: center;
    }

    .logo-preview {
        width: 76px;
        height: 76px;
        border: 1px solid #eadcff;
        border-radius: 12px;
        background: #fbf8ff;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
    }

    .logo-preview img {
        width: 58px;
        height: 58px;
        object-fit: contain;
    }

    .logo-upload-control {
        min-width: 0;
    }

    .remove-logo-option {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        margin-top: 10px;
        color: #581c87;
        font-size: 13px;
        cursor: pointer;
    }

    .remove-logo-option input {
        width: 16px;
        height: 16px;
        accent-color: #9333ea;
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
        background: #fbf8ff;
        border: 1px solid #eadcff;
        border-radius: 4px;
        padding: 20px;
        border-left: 4px solid #9333ea;
    }

    .info-box h6 {
        color: #581c87;
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

    .settings-preview {
        background: #ffffff;
        border: 1px solid #eadcff;
        border-radius: 8px;
        padding: 14px 16px;
        margin-bottom: 14px;
        display: grid;
        gap: 4px;
    }

    .settings-preview-label {
        color: #7e22ce;
        font-size: 12px;
        font-weight: 600;
    }

    .settings-preview strong {
        color: #212529;
        font-size: 16px;
    }

    .settings-preview-identity {
        display: flex;
        align-items: center;
        gap: 10px;
    }

    .settings-preview-identity img {
        width: 36px;
        height: 36px;
        object-fit: contain;
        border-radius: 8px;
        background: #f3e8ff;
        padding: 4px;
    }

    .settings-preview span:last-child {
        color: #6c757d;
        font-size: 13px;
    }

    .settings-impact-grid {
        display: grid;
        gap: 10px;
        margin-bottom: 12px;
    }

    .settings-impact-card {
        display: grid;
        grid-template-columns: 34px 1fr;
        gap: 10px;
        align-items: start;
        background: #ffffff;
        border: 1px solid #eadcff;
        border-radius: 8px;
        padding: 12px;
    }

    .settings-impact-card i {
        width: 34px;
        height: 34px;
        border-radius: 8px;
        background: #f3e8ff;
        color: #9333ea;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .settings-impact-card strong {
        color: #212529;
        font-size: 14px;
    }

    .settings-impact-card p {
        margin: 2px 0 0 0;
        color: #6c757d;
        font-size: 13px;
        line-height: 1.5;
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
