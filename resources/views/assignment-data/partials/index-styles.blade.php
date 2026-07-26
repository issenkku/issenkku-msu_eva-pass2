{{-- style เฉพาะของหน้า list เช่น pagination, modal และ interaction ของ table --}}
<style>
    body {
        font-family: 'Sarabun', sans-serif;
    }

    .pagination {
        display: flex;
        justify-content: center;
        align-items: center;
        gap: 0.5rem;
    }

    .pagination .page-link {
        padding: 0.5rem 0.75rem;
        border: 1px solid #d1d5db;
        border-radius: 0.375rem;
        color: #374151;
        text-decoration: none;
        transition: all 0.3s ease;
    }

    .pagination .page-link:hover {
        background-color: #f3f4f6;
        border-color: #9ca3af;
    }

    .pagination .page-item.active .page-link {
        background-color: #3b82f6;
        border-color: #3b82f6;
        color: white;
    }

    .pagination .page-item.disabled .page-link {
        color: #9ca3af;
        cursor: not-allowed;
        opacity: 0.5;
    }

    tbody tr {
        transition: all 0.2s ease;
    }

    tbody tr:hover {
        transform: translateY(-1px);
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
    }

    .assignment-data-row:has(.dropdown-menu.show) {
        position: relative;
        z-index: 1;
    }

    #evaluateesModal {
        animation: fadeIn 0.2s ease-out;
    }

    @keyframes fadeIn {
        from {
            opacity: 0;
        }

        to {
            opacity: 1;
        }
    }

    #evaluateesModal>div {
        animation: slideUp 0.3s ease-out;
    }

    @keyframes slideUp {
        from {
            transform: translateY(20px);
            opacity: 0;
        }

        to {
            transform: translateY(0);
            opacity: 1;
        }
    }

    #evaluateesContent::-webkit-scrollbar {
        width: 8px;
    }

    #evaluateesContent::-webkit-scrollbar-track {
        background: #f1f1f1;
        border-radius: 4px;
    }

    #evaluateesContent::-webkit-scrollbar-thumb {
        background: #888;
        border-radius: 4px;
    }

    #evaluateesContent::-webkit-scrollbar-thumb:hover {
        background: #555;
    }

    .inline-flex {
        transition: all 0.3s ease;
    }

    .inline-flex:hover {
        transform: scale(1.05);
    }

    .transition-colors {
        transition: color 0.3s ease, background-color 0.3s ease;
    }

    @media (max-width: 768px) {
        .overflow-x-auto {
            -webkit-overflow-scrolling: touch;
        }

        table {
            min-width: 800px;
        }
    }
</style>
