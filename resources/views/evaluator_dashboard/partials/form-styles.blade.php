<style>
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
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.3);
        outline: none;
    }

    .is-invalid {
        border-color: #dc3545;
    }

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
</style>
