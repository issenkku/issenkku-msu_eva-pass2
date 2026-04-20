{{-- สไตล์ของหน้าตั้งค่า workload --}}
<style>
        .workload-page {
            padding: 20px 24px 40px;
            background: #f4f6f9;
            min-height: 80vh;
        }

        .page-header {
            margin-bottom: 12px;
        }

        .page-title {
            font-size: 1.6rem;
            font-weight: 600;
            color: #111827;
            margin: 0;
        }

        .page-subtitle {
            color: #6b7280;
            margin: 8px 0 0;
            font-size: 0.95rem;
        }

        .workload-grid {
            display: grid;
            grid-template-columns: 240px 1fr;
            gap: 24px;
            margin-top: 24px;
        }

        .workload-nav {
            background: #fff;
            border-radius: 16px;
            padding: 16px 14px;
            border: 1px solid #e5e7eb;
            height: fit-content;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.05);
        }

        .nav-title {
            font-weight: 600;
            color: #374151;
            font-size: 0.95rem;
        }

        .nav-list {
            display: grid;
            gap: 8px;
            margin-top: 16px;
        }

        .workload-toast {
            position: fixed;
            top: 20px;
            right: 20px;
            z-index: 1050;
            background: #16a34a;
            color: #ffffff;
            border-radius: 12px;
            padding: 12px 16px;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.12);
            transform: translateX(120%);
            transition: transform 0.3s ease;
            max-width: 360px;
            width: calc(100% - 40px);
        }

        .workload-toast.is-visible {
            transform: translateX(0);
        }

        .workload-toast.is-danger {
            background: #dc2626;
        }

        .workload-toast-content {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .workload-toast-icon {
            font-weight: 700;
        }

        .workload-toast-text {
            flex: 1;
            font-size: 0.95rem;
        }

        .workload-toast-close {
            background: transparent;
            border: none;
            color: inherit;
            font-size: 1.1rem;
            line-height: 1;
            cursor: pointer;
        }

        .nav-item2 {
            border: 1px solid transparent;
            background: #f3f4f6;
            padding: 10px 12px;
            border-radius: 10px;
            text-align: left;
            font-size: 0.9rem;
            color: #4b5563;
            transition: 0.2s ease;
            width: 100%;
        }

        .nav-item2:hover {
            background: #e5e7eb;
        }

        .nav-item2.is-active {
            border-color: #2563eb;
            background: #e0edff;
            color: #1d4ed8;
            font-weight: 600;
        }

        .workload-content {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .workload-content > .section-heading {
            order: 1;
        }

        .workload-content > .workload-card-list {
            order: 2;
        }

        .workload-content > .page-actions {
            order: 3;
        }

        .workload-card-list {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .section-heading {
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-badge {
            width: 32px;
            height: 32px;
            border-radius: 50%;
            background: #2563eb;
            color: #fff;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: 600;
        }

        .section-title {
            margin: 0;
            font-weight: 600;
            color: #111827;
        }

        .workload-card {
            border-radius: 18px;
            border: 1px solid #e5e7eb;
            box-shadow: 0 12px 24px rgba(15, 23, 42, 0.08);
            position: relative;
            overflow: hidden;
        }

        .workload-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            width: 4px;
            height: 100%;
            background: #2563eb;
        }

        .card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .card-title {
            font-size: 1rem;
            font-weight: 600;
            margin: 0;
        }

        .workload-main-summary {
            display: none;
            margin-top: 4px;
            font-size: 1.15rem;
            font-weight: 700;
            color: #111827;
        }

        .workload-card.is-collapsed .workload-main-summary {
            display: block;
        }

        .workload-card.is-collapsed .card-title {
            display: none;
        }

        .workload-card.is-collapsed .card-head,
        .workload-sub-card.is-collapsed .sub-card-head {
            cursor: pointer;
        }

        .card-actions {
            display: flex;
            gap: 8px;
        }

        .workload-drag-handle {
            cursor: grab;
        }

        .workload-drag-handle:active {
            cursor: grabbing;
        }

        .icon-btn.is-drag {
            color: #6b7280;
            border-color: transparent;
            background: transparent;
        }

        .workload-drag-handle-text {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 8px;
            border-radius: 10px;
            font-size: 0.95rem;
            font-weight: 600;
            color: #6b7280;
        }

        .workload-drag-handle-text:hover {
            background: #f3f4f6;
            color: #374151;
        }

        .workload-drag-icon {
            font-size: 0.95rem;
            letter-spacing: -1px;
            color: #9ca3af;
        }

        .workload-drag-label {
            white-space: nowrap;
        }

        .workload-card.is-dragging,
        .workload-sub-card.is-dragging,
        .workload-item-row.is-dragging {
            opacity: 0.55;
        }

        .workload-card.is-drag-over,
        .workload-sub-card.is-drag-over,
        .workload-item-row.is-drag-over {
            outline: 2px dashed #60a5fa;
            outline-offset: 3px;
        }

        .workload-card.is-collapsed .card-body > :not(.card-head):not(.sub-footer:last-child) {
            display: none;
        }

        .workload-sub-card.is-collapsed > :not(.sub-card-head) {
            display: none;
        }

        .icon-btn {
            border: 1px solid #e5e7eb;
            background: #fff;
            border-radius: 8px;
            padding: 4px 8px;
            font-size: 0.85rem;
            color: #6b7280;
            line-height: 1;
        }

        .workload-card .card-actions .icon-btn {
            border-color: transparent;
            background: transparent;
            padding: 2px 6px;
            font-size: 1rem;
            color: #94a3b8;
        }

        .workload-card .card-actions .workload-drag-handle-text {
            padding: 6px 8px;
            font-size: 0.95rem;
            color: #6b7280;
        }

        .workload-card .card-actions .icon-btn:hover {
            color: #2563eb;
        }

        .workload-card .card-actions .workload-drag-handle-text:hover {
            color: #374151;
        }

        .workload-card .card-actions .icon-btn.is-danger:hover {
            color: #dc2626;
        }

        .workload-card .card-actions .workload-collapse-toggle {
            color: #cbd5e1;
        }

        .workload-card .card-actions .workload-collapse-toggle:hover {
            color: #2563eb;
        }

        .icon-btn.is-danger {
            color: #dc2626;
            border-color: #fecaca;
            background: #fff5f5;
        }

        .delete_category_btn {
            border: 0;
            background: transparent;
            padding: 2px 6px;
            line-height: 1;
        }

        .workload-delete-icon {
            width: 1.25rem;
            height: 1.25rem;
            display: block;
        }

        .form-grid {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 12px 16px;
            align-items: center;
        }

        .form-label {
            font-size: 0.85rem;
            color: #6b7280;
            margin: 0;
        }

        .main-criteria-block {
            display: grid;
            gap: 8px;
        }

        .main-criteria-labels,
        .main-criteria-fields {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 16px;
            align-items: center;
        }

        .main-criteria-labels .form-label {
            color: #111827;
            font-weight: 600;
        }

        .sub-criteria-block {
            display: grid;
            gap: 8px;
            background: #f9fafb;
            border-radius: 12px;
            padding: 12px;
            border: 1px solid #e5e7eb;
        }

        .sub-criteria-labels,
        .sub-criteria-fields {
            display: grid;
            grid-template-columns: 120px 1fr;
            gap: 16px;
            align-items: center;
        }

        .sub-criteria-labels .form-label {
            color: #111827;
            font-weight: 600;
        }

        .main-criteria-fields .form-control {
            max-width: 100%;
        }

        .req {
            color: #ef4444;
        }

        .sub-block {
            background: #f8fafc;
            border-radius: 16px;
            padding: 16px;
            border: 1px solid #e5e7eb;
        }

        .sub-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .sub-title {
            display: flex;
            align-items: center;
            gap: 8px;
            font-weight: 600;
            color: #1f2937;
        }

        .sub-icon {
            font-size: 1.1rem;
        }

        .sub-card {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-top: 12px;
        }

        .sub-card-head {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .sub-card-title {
            font-weight: 600;
            color: #111827;
        }

        .workload-sub-summary {
            display: none;
            margin-top: 4px;
            font-size: 1rem;
            font-weight: 700;
            color: #111827;
        }

        .workload-sub-card.is-collapsed .workload-sub-summary {
            display: block;
        }

        .workload-sub-card.is-collapsed .sub-card-title {
            display: none;
        }

        .subitem-table {
            display: grid;
            gap: 10px;
        }

        .subitem-header {
            display: grid;
            grid-template-columns: 100px 1fr 140px 40px;
            gap: 12px;
            font-weight: 600;
            color: #6b7280;
            font-size: 0.85rem;
        }

        .subitem-row {
            display: grid;
            grid-template-columns: 100px 1fr 140px 40px;
            gap: 12px;
            align-items: center;
        }

        .subitem-label {
            background: #f3f4f6;
            border-radius: 8px;
            padding: 10px 12px;
            color: #6b7280;
            font-weight: 600;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .workload-item-drag-handle {
            padding: 2px 8px;
            font-size: 0.95rem;
            color: #9ca3af;
        }

        .subitem-actions {
            display: flex;
            justify-content: flex-start;
            margin-top: 8px;
        }

        .formula-block {
            margin-top: 16px;
        }

        .formula-panel {
            background: #fff;
            border-radius: 14px;
            border: 1px solid #e5e7eb;
            padding: 16px;
            margin-top: 12px;
        }

        .formula-row {
            display: grid;
            grid-template-columns: 1fr 1fr 1fr 1fr auto;
            gap: 12px;
            align-items: end;
        }

        .formula-action {
            display: flex;
            justify-content: flex-end;
            gap: 8px;
        }

        .formula-list {
            display: grid;
            gap: 10px;
            margin-top: 16px;
        }

        .formula-item {
            display: grid;
            grid-template-columns: 1fr 120px 40px 40px;
            gap: 10px;
            align-items: center;
            background: #f9fafb;
            border: 1px solid #e5e7eb;
            padding: 10px 12px;
            border-radius: 10px;
            color: #374151;
        }

        .formula-item-meta {
            display: flex;
            flex-direction: column;
            gap: 2px;
            min-width: 0;
        }

        .formula-item-label {
            font-weight: 600;
            color: #111827;
        }

        .formula-item-note {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .formula-item-default {
            font-size: 0.8rem;
            color: #0f766e;
        }

        .formula-value {
            color: #2563eb;
            font-weight: 600;
            text-align: right;
        }

        .formula-item.is-editing {
            border-color: #93c5fd;
            box-shadow: 0 0 0 1px #bfdbfe inset;
            background: #eff6ff;
        }

        .formula-text {
            border-radius: 12px;
        }

        .formula-builder-bar {
            display: grid;
            grid-template-columns: 1fr auto;
            gap: 12px;
            align-items: center;
            margin-bottom: 12px;
        }

        .formula-toolbar {
            margin-top: 16px;
            display: grid;
            gap: 12px;
        }

        .toolbar-group {
            display: grid;
            gap: 8px;
        }

        .toolbar-label {
            font-size: 0.8rem;
            color: #6b7280;
        }

        .toolbar-buttons {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .workload-variable-chips {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 8px;
        }

        .workload-variable-chip-row {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        .chip {
            border: 1px solid #dbeafe;
            background: #eff6ff;
            color: #2563eb;
            padding: 4px 12px;
            border-radius: 999px;
            font-size: 0.8rem;
            font-weight: 600;
        }

        .chip-muted {
            border-color: #e5e7eb;
            background: #f3f4f6;
            color: #9ca3af;
        }

        .sub-footer {
            margin-top: 12px;
        }

        .page-actions {
            display: flex;
            justify-content: center;
            gap: 12px;
            margin-top: 12px;
            padding-bottom: 8px;
        }

        @media (max-width: 992px) {
            .workload-grid {
                grid-template-columns: 1fr;
            }

            .subitem-header,
            .subitem-row {
                grid-template-columns: 1fr;
            }

            .formula-row {
                grid-template-columns: 1fr;
            }

            .formula-item {
                grid-template-columns: 1fr 1fr auto auto;
            }
        }
    </style>
