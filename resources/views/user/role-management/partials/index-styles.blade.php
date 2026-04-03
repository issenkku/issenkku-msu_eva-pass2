{{-- style ของหน้าจัดการบทบาทและสิทธิ์ ถูกแยกออกจากหน้าแม่เพื่อลดความยาวไฟล์ --}}
<style>
    .role-page-title {
        font-size: 1.5rem;
        font-weight: 700;
        margin-bottom: 1rem;
    }

    .role-toolbar {
        display: flex;
        justify-content: space-between;
        margin-bottom: 1rem;
    }

    .role-add-button {
        background-color: #7c3aed;
        color: #fff;
        padding: 0.5rem 1rem;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
    }

    .role-table {
        width: 100%;
        background: #fff;
        border-radius: 0.5rem;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .role-table thead tr {
        background: #f3f4f6;
        text-align: left;
    }

    .role-table th,
    .role-table td {
        padding: 0.75rem;
        vertical-align: top;
    }

    .role-table tbody tr + tr {
        border-top: 1px solid #e5e7eb;
    }

    .permission-badge {
        display: inline-block;
        background: #dcfce7;
        color: #15803d;
        padding: 0.25rem 0.5rem;
        border-radius: 0.375rem;
        font-size: 0.875rem;
        margin: 0.125rem 0.25rem 0.125rem 0;
    }

    .role-action-link {
        display: inline-block;
        padding: 0.25rem 0.75rem;
        border-radius: 0.375rem;
        color: #fff;
        text-decoration: none;
        border: none;
        cursor: pointer;
    }

    .role-action-link.edit {
        background: #6366f1;
    }

    .role-action-link.delete {
        background: #ef4444;
    }

    .role-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(0, 0, 0, 0.5);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 50;
    }

    .role-modal-backdrop.show {
        display: flex;
    }

    .role-modal-card {
        background: #fff;
        padding: 1.5rem;
        border-radius: 0.5rem;
        width: 100%;
        max-width: 36rem;
    }

    .role-modal-title {
        font-size: 1.125rem;
        font-weight: 600;
        margin-bottom: 1rem;
    }

    .role-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        padding: 0.5rem 0.75rem;
        margin-bottom: 1rem;
    }

    .role-permission-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.5rem;
        margin-bottom: 1rem;
    }

    .role-modal-actions {
        text-align: right;
    }

    .role-modal-actions button {
        padding: 0.5rem 0.75rem;
        border-radius: 0.375rem;
        border: none;
        cursor: pointer;
        margin-left: 0.5rem;
    }

    .role-cancel-button {
        background: #d1d5db;
    }

    .role-save-button {
        background: #2563eb;
        color: #fff;
    }
</style>
