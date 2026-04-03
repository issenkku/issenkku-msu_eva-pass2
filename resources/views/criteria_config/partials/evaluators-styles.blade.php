<style>
        body {
            font-family: 'Sarabun', sans-serif;
        }

        .card-checkbox {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .card-checkbox:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .card-checkbox.selected {
            background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
            color: white;
            border-color: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(59, 130, 246, 0.3);
        }

        .card-checkbox.selected .card-avatar {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .check-icon {
            opacity: 0;
            transform: scale(0.5);
            transition: all 0.2s ease;
        }

        .card-checkbox.selected .check-icon {
            opacity: 1;
            transform: scale(1);
        }

        .selected-counter {
            background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        }

        .select2-results__option--select-all {
            font-weight: bold;
            background-color: #eef2ff !important;
            color: #1e40af !important;
            border-bottom: 1px solid #e5e7eb;
        }

        .select2-results__option--select-all:hover {
            background-color: #dbeafe !important;
        }

        .select2-selection__choice:not(:first-child) {
            display: none !important;
        }

        /* Checkbox ใน dropdown ไม่ควรถูกคลิก */
        .select2-results__option input[type="checkbox"] {
            pointer-events: none;
        }
    </style>