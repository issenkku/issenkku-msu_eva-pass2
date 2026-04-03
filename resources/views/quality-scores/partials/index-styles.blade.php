{{-- style ของหน้าจัดการคะแนนคุณภาพ ถูกแยกออกจากหน้าแม่เพื่อลดความยาวไฟล์ --}}
<style>
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

    .table-custom tbody tr:nth-child(even) {
        background-color: #fafafa;
    }

    .table-custom tbody tr:nth-child(even):hover {
        background-color: #f0f0f0;
    }

    .btn-primary {
        background-color: #ffffff;
        color: #333333;
        border: 1px solid #cccccc;
        padding: 10px 20px;
        border-radius: 3px;
        font-weight: 400;
        margin-bottom: 16px;
        font-size: 0.9rem;
        transition: all 0.15s ease;
        text-decoration: none;
    }

    .btn-primary:hover {
        background-color: #f0f0f0;
        border-color: #999999;
        color: #333333;
        text-decoration: none;
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

    .container-fluid {
        max-width: 1200px;
        margin: 0 auto;
        padding: 24px;
    }

    .badge {
        padding: 6px 12px;
        border-radius: 4px;
        font-size: 0.8rem;
        font-weight: 500;
    }

    .badge-success {
        background-color: #d4edda;
        color: #155724;
    }

    .badge-warning {
        background-color: #fff3cd;
        color: #856404;
    }

    .badge-danger {
        background-color: #f8d7da;
        color: #721c24;
    }

    .card {
        border: 1px solid #e0e0e0;
        border-radius: 4px;
        margin-bottom: 24px;
    }

    .card-header {
        background-color: #f8f9fa;
        border-bottom: 1px solid #e0e0e0;
        padding: 16px 24px;
    }

    .alert {
        padding: 12px 16px;
        border-radius: 4px;
        margin-bottom: 16px;
        border: 1px solid;
    }

    .alert-success {
        background-color: #d4edda;
        border-color: #c3e6cb;
        color: #155724;
    }

    .alert-danger {
        background-color: #f8d7da;
        border-color: #f5c6cb;
        color: #721c24;
    }

    .alert-warning {
        background-color: #fff3cd;
        border-color: #ffeaa7;
        color: #856404;
    }

    .me-2 {
        margin-right: 0.5rem !important;
    }

    .mb-4 {
        margin-bottom: 1.5rem !important;
    }

    .version-header {
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        color: white;
        padding: 12px 18px;
        margin-bottom: 16px;
        position: relative;
        overflow: hidden;
    }

    .version-header::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        background: rgba(255, 255, 255, 0.1);
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .version-header:hover::before {
        opacity: 1;
    }

    .version-header h5 {
        margin: 0;
        font-size: 1.1rem;
        font-weight: 500;
        text-shadow: 0 1px 2px rgba(0, 0, 0, 0.3);
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .version-header i {
        background: rgba(255, 255, 255, 0.2);
        padding: 6px;
        border-radius: 6px;
        font-size: 0.9rem;
    }

    .version-divider {
        border: none;
        height: 3px;
        background: #000;
        margin: 30px 0;
        border-radius: 2px;
    }
</style>
