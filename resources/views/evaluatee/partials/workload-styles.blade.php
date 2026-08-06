<style>
    /* สไตล์หลักของหน้า workload และ modal เพิ่มข้อมูลภาระงาน */
    .workload-background-page {
        min-height: calc(100vh - 64px);
        margin: -1.5rem;
        padding: 3.5rem 1.5rem;
        background-image:
            linear-gradient(180deg, #ffffff 0%, #ffffff 42%, rgba(255, 255, 255, 0.78) 55%, rgba(255, 255, 255, 0.08) 70%, rgba(255, 255, 255, 0) 100%),
            var(--app-background-image, url("{{ asset('images/workload-background.jpg') }}"));
        background-position: center top, center bottom;
        background-repeat: no-repeat;
        background-size: 100% 100%, 100% auto;
        background-attachment: fixed;
    }

    .workload-readonly-banner {
        background: rgba(239, 246, 255, 0.9);
        border: 1px solid rgba(191, 219, 254, 0.9);
        color: #1d4ed8;
        border-radius: 14px;
        padding: 14px 18px;
        font-weight: 600;
        backdrop-filter: blur(12px);
    }

    .workload-page-header {
        background: rgba(255, 255, 255, 0.86);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        padding: 24px 28px;
        box-shadow: 0 6px 18px rgba(15, 23, 42, 0.08);
        text-align: center;
        backdrop-filter: blur(14px);
    }

    .workload-page-title {
        font-size: 26px;
        font-weight: 700;
        color: #111827;
        margin-bottom: 6px;
    }

    .workload-page-subtitle {
        color: #6b7280;
        font-size: 15px;
    }

    .workload-panel {
        background: rgba(255, 255, 255, 0.9);
        border: 1px solid rgba(226, 232, 240, 0.9);
        border-radius: 18px;
        box-shadow: 0 8px 18px rgba(15, 23, 42, 0.08);
        overflow: hidden;
        backdrop-filter: blur(16px);
    }

    .workload-panel-header {
        background: rgba(248, 250, 252, 0.72);
        padding: 16px 22px;
        border-bottom: 1px solid rgba(226, 232, 240, 0.9);
    }

    .workload-panel-header h2 {
        font-size: 20px;
        font-weight: 700;
        color: #111827;
    }

    .workload-panel-body {
        padding: 20px;
        background: rgba(255, 255, 255, 0.78);
    }

    .workload-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 10px 14px;
        margin-bottom: 16px;
        list-style: none;
        cursor: pointer;
        width: 100%;
    }

    .workload-toolbar-columns {
        display: flex;
        gap: 32px;
        color: #6b7280;
        font-weight: 600;
        font-size: 13px;
        flex: 1;
        justify-content: center;
    }

    .workload-select {
        display: inline-flex;
        align-items: center;
        gap: 10px;
        background: #ffffff;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        padding: 8px 12px;
        font-weight: 600;
        color: #111827;
        font-size: 14px;
        cursor: pointer;
        list-style: none;
        transition: border-color 0.2s ease, box-shadow 0.2s ease, background-color 0.2s ease;
    }

    .workload-select::-webkit-details-marker {
        display: none;
    }

    .workload-select-hint {
        font-size: 12px;
        font-weight: 500;
        color: #64748b;
    }

    .workload-select-icon {
        width: 9px;
        height: 9px;
        border-right: 2px solid #7c3aed;
        border-bottom: 2px solid #7c3aed;
        transform: rotate(45deg) translateY(-1px);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .workload-dropdown > summary {
        display: flex;
        align-items: center;
        gap: 12px;
        width: 100%;
        margin-bottom: 16px;
    }

    .workload-dropdown > summary::-webkit-details-marker {
        display: none;
    }

    .workload-dropdown {
        border: none;
    }

    .workload-dropdown[open] > summary {
        margin-bottom: 16px;
    }

    .workload-dropdown[open] .workload-select {
        border-color: #c4b5fd;
        background: #faf5ff;
        box-shadow: 0 0 0 3px rgba(124, 58, 237, 0.08);
    }

    .workload-dropdown[open] .workload-select-icon {
        transform: rotate(-135deg) translateY(-1px);
    }

    .workload-dropdown:not([open]) > summary {
        margin-bottom: 0;
    }

    .workload-add-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: #7c3aed;
        color: #ffffff;
        border-radius: 10px;
        padding: 8px 14px;
        font-weight: 600;
        border: none;
        font-size: 14px;
    }

    .workload-table-wrap {
        border-radius: 14px;
        border: 1px solid #e5e7eb;
        overflow-x: auto;
        overflow-y: hidden;
    }

    
    .workload-subtable + .workload-subtable {
        margin-top: 18px;
    }

    .workload-item-dropdown {
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        background: #f8fafc;
        margin-bottom: 16px;
        overflow: hidden;
        box-shadow: 0 6px 12px rgba(15, 23, 42, 0.06);
    }

    .workload-item-dropdown:last-child {
        margin-bottom: 0;
    }

    .workload-item-summary {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        padding: 16px 18px;
        cursor: pointer;
        list-style: none;
        background: #eff6ff;
    }

    .workload-item-summary::-webkit-details-marker {
        display: none;
    }

    .workload-item-summary-title {
        font-size: 16px;
        font-weight: 700;
        color: #0f172a;
    }

    .workload-item-summary-right {
        display: inline-flex;
        align-items: center;
        gap: 10px;
    }

    .workload-item-summary-hint {
        font-size: 12px;
        font-weight: 600;
        color: #64748b;
    }

    .workload-item-summary-score {
        font-size: 12px;
        font-weight: 700;
        color: #0f766e;
        background: #d1fae5;
        padding: 4px 10px;
        border-radius: 999px;
    }

    .workload-item-summary-icon {
        width: 10px;
        height: 10px;
        border-right: 2px solid #475569;
        border-bottom: 2px solid #475569;
        transform: rotate(45deg);
        transition: transform 0.2s ease;
        flex-shrink: 0;
    }

    .workload-item-dropdown[open] .workload-item-summary-icon {
        transform: rotate(-135deg);
    }

    .workload-item-dropdown .workload-subtable {
        background: #ffffff;
        border-top: 1px solid #e5e7eb;
        border-radius: 0;
        box-shadow: none;
        margin: 0;
    }

    .workload-subtable-title {
        font-size: 15px;
        font-weight: 700;
        color: #1f2937;
        margin: 0 0 10px;
        padding-left: 4px;
    }

    .workload-table {
        width: 100%;
        min-width: 1100px;
        border-collapse: collapse;
        table-layout: fixed;
    }

    .workload-table thead {
        background: #94a3b8;
        color: #ffffff;
    }

    .workload-table th,
    .workload-table td {
        padding: 12px 10px;
        font-size: 13px;
        text-align: center;
    }

    .workload-table tbody tr:nth-child(even) {
        background: #f8fafc;
    }

    .workload-table td:first-child,
    .workload-table th:first-child {
        text-align: left;
        padding-left: 16px;
        font-weight: 600;
        color: #1f2937;
    }

    .workload-table th:nth-last-child(2),
    .workload-table td:nth-last-child(2) {
        width: 18rem;
    }

    .workload-table td:nth-last-child(2) {
        vertical-align: top;
    }

    .workload-table td:nth-last-child(2) a {
        display: block;
        max-width: 100%;
        overflow-wrap: anywhere;
        word-break: break-word;
    }

    .workload-table th:last-child,
    .workload-table td:last-child {
        width: 9rem;
        white-space: nowrap;
    }

    .workload-mini-btn {
        background: #8b5cf6;
        color: #ffffff;
        border: none;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
        padding: 6px 12px !important;
        min-height: 30px;
        min-width: 64px;
        border-radius: 8px;
        box-shadow: 0 2px 6px rgba(15, 23, 42, 0.15);
        font-size: 12px;
        font-weight: 600;
        line-height: 1;
        transition: transform 0.12s ease, box-shadow 0.12s ease, filter 0.12s ease;
        box-sizing: border-box;
    }

    .workload-mini-btn:hover {
        box-shadow: 0 4px 10px rgba(15, 23, 42, 0.2);
        transform: translateY(-1px);
        filter: brightness(1.03);
    }

    .workload-mini-btn:active {
        box-shadow: 0 1px 3px rgba(15, 23, 42, 0.2);
        transform: translateY(0);
    }

    .workload-item-score {
        color: #6b7280;
        font-weight: 600;
        margin-left: 6px;
    }

    .workload-row-actions {
        display: flex;
        align-items: center;
        gap: 8px;
        justify-content: center;
    }

    .workload-delete-form {
        margin: 0;
    }

    .workload-delete-btn {
        background: #e29d9d80;
        color: #b91c1c;
    }

    .workload-total-row {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 14px;
        font-weight: 600;
        color: #111827;
    }

    .workload-total-value {
        font-size: 16px;
        color: #111827;
    }

    .workload-pagination {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        margin-top: 16px;
        flex-wrap: wrap;
    }

    .page-btn {
        border: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 6px 12px;
        border-radius: 8px;
        font-size: 13px;
        color: #374151;
    }

    .page-btn.is-active {
        background: #3b82f6;
        color: #ffffff;
        border-color: #3b82f6;
    }

    .page-ellipsis {
        color: #6b7280;
    }

    .workload-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }

    .workload-list-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        background: #f3f4f6;
        border-radius: 12px;
        padding: 10px 14px;
        border: 1px solid #e5e7eb;
    }

    .workload-list-columns {
        display: flex;
        align-items: center;
        gap: 24px;
        font-size: 13px;
        color: #6b7280;
        flex-wrap: wrap;
        justify-content: flex-start;
    }

    .workload-summary-card {
        background: linear-gradient(90deg, #bbf7d0 0%, #22d3ee 100%);
        color: #0f172a;
        padding: 24px;
        border-radius: 14px;
        display: flex;
        justify-content: center;
        text-align: center;
        font-weight: 700;
    }

    .workload-summary-title {
        font-size: 18px;
        margin-bottom: 6px;
    }

    .workload-summary-value {
        font-size: 28px;
    }

    .workload-actions {
        display: flex;
        justify-content: center;
        gap: 16px;
        position: sticky;
        bottom: 16px;
        z-index: 40;
        width: fit-content;
        margin: 0 auto;
        padding: 12px 14px;
        background: rgba(255, 255, 255, 0.92);
        border: 1px solid rgba(226, 232, 240, 0.9);
        backdrop-filter: blur(10px);
        border-radius: 16px;
        box-shadow: 0 16px 36px rgba(15, 23, 42, 0.12);
    }

    .workload-save-reminder {
        margin: 0 auto 14px;
        max-width: 760px;
        background: #fff7ed;
        border: 1px solid #fdba74;
        color: #9a3412;
        border-radius: 14px;
        padding: 12px 16px;
        box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
    }

    .workload-save-reminder-title {
        font-size: 14px;
        font-weight: 700;
        margin-bottom: 4px;
    }

    .workload-save-reminder-text {
        font-size: 13px;
        line-height: 1.5;
    }

    .workload-back-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #e5e7eb;
        color: #111827;
        text-decoration: none;
        font-weight: 600;
    }

    .workload-save-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #7c3aed;
        color: #ffffff;
        font-weight: 600;
        border: none;
    }

    .workload-import-btn {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 10px 18px;
        border-radius: 10px;
        background: #eff6ff;
        color: #1d4ed8;
        font-weight: 600;
        border: 1px solid #bfdbfe;
    }

    .workload-import-btn:hover {
        background: #dbeafe;
        color: #1e40af;
    }

    .workload-unsaved-confirm[hidden] {
        display: none;
    }

    .workload-unsaved-confirm {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .workload-unsaved-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(6px);
    }

    .workload-unsaved-confirm-dialog {
        position: relative;
        width: min(430px, 100%);
        border-radius: 18px;
        border: 1px solid rgba(253, 186, 116, 0.9);
        background: #ffffff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        padding: 22px;
    }

    .workload-unsaved-confirm-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #ffedd5 0%, #fef3c7 100%);
        color: #ea580c;
        font-size: 20px;
        margin-bottom: 14px;
    }

    .workload-unsaved-confirm-content h2 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 18px;
        font-weight: 800;
    }

    .workload-unsaved-confirm-content p {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.65;
    }

    .workload-unsaved-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    .workload-unsaved-confirm-cancel,
    .workload-unsaved-confirm-submit {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 10px;
        padding: 9px 16px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .workload-unsaved-confirm-cancel {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #334155;
    }

    .workload-unsaved-confirm-submit {
        background: #ea580c;
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(234, 88, 12, 0.22);
    }

    .workload-unsaved-confirm-submit:hover {
        background: #c2410c;
    }

    body.workload-unsaved-confirm-open {
        overflow: hidden;
    }

    .workload-import-confirm[hidden] {
        display: none;
    }

    .workload-import-confirm {
        position: fixed;
        top: 0;
        right: 0;
        bottom: 0;
        left: 0;
        z-index: 9990;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 20px;
    }

    .workload-import-confirm-backdrop {
        position: absolute;
        inset: 0;
        background: rgba(15, 23, 42, 0.42);
        backdrop-filter: blur(6px);
    }

    .workload-import-confirm-dialog {
        position: relative;
        width: min(440px, 100%);
        border-radius: 18px;
        border: 1px solid rgba(191, 219, 254, 0.85);
        background: #ffffff;
        box-shadow: 0 24px 60px rgba(15, 23, 42, 0.24);
        padding: 22px;
    }

    .workload-import-confirm-icon {
        width: 46px;
        height: 46px;
        border-radius: 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        background: linear-gradient(135deg, #dbeafe 0%, #ede9fe 100%);
        color: #2563eb;
        font-size: 20px;
        margin-bottom: 14px;
    }

    .workload-import-confirm-content h2 {
        margin: 0 0 8px;
        color: #111827;
        font-size: 18px;
        font-weight: 800;
    }

    .workload-import-confirm-content p {
        margin: 0;
        color: #475569;
        font-size: 14px;
        line-height: 1.65;
    }

    .workload-import-confirm-label {
        display: block;
        margin-top: 16px;
        margin-bottom: 7px;
        color: #334155;
        font-size: 13px;
        font-weight: 700;
    }

    .workload-import-confirm-select {
        width: 100%;
        min-height: 42px;
        border-radius: 10px;
        border: 1px solid #bfdbfe;
        background: #f8fafc;
        color: #0f172a;
        font-size: 14px;
        padding: 8px 12px;
        outline: none;
    }

    .workload-import-confirm-select:focus {
        border-color: #2563eb;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.14);
    }

    .workload-import-confirm-empty {
        margin-top: 16px;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        background: #f8fafc;
        color: #64748b;
        font-size: 13px;
        padding: 11px 12px;
    }

    .workload-import-confirm-actions {
        display: flex;
        justify-content: flex-end;
        gap: 10px;
        margin-top: 22px;
    }

    .workload-import-confirm-cancel,
    .workload-import-confirm-submit {
        display: inline-flex;
        min-height: 40px;
        align-items: center;
        justify-content: center;
        gap: 8px;
        border-radius: 10px;
        padding: 9px 16px;
        font-weight: 700;
        border: 1px solid transparent;
    }

    .workload-import-confirm-cancel {
        background: #f8fafc;
        border-color: #e2e8f0;
        color: #334155;
    }

    .workload-import-confirm-submit {
        background: #2563eb;
        color: #ffffff;
        box-shadow: 0 10px 20px rgba(37, 99, 235, 0.22);
    }

    .workload-import-confirm-submit:hover {
        background: #1d4ed8;
    }

    .workload-import-confirm-submit.is-loading {
        opacity: 0.75;
        cursor: wait;
    }

    body.workload-import-confirm-open {
        overflow: hidden;
    }

    .workload-modal-content {
        border-radius: 18px;
        border: 1px solid #e5e7eb;
        overflow: hidden;
        box-shadow: 0 18px 40px rgba(15, 23, 42, 0.2);
    }

    .workload-modal-header {
        background: linear-gradient(90deg, #ede9fe 0%, #e0f2fe 100%);
        border-bottom: 1px solid #e5e7eb;
        padding: 14px 20px;
    }

    .workload-modal-header .modal-title {
        font-size: 18px;
        font-weight: 700;
        color: #111827;
    }

    .workload-modal-body {
        padding: 18px 22px 10px;
        background: #ffffff;
    }

    .workload-modal-section {
        background: #f5f3ff;
        border-radius: 12px;
        padding: 12px;
        margin-bottom: 12px;
    }

    .workload-modal-label {
        display: block;
        font-size: 13px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 6px;
    }

    .workload-modal-hint {
        display: block;
        margin-top: 6px;
        font-size: 12px;
        color: #6b7280;
    }

    .workload-modal-select,
    .workload-modal-input {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 8px 10px;
        font-size: 13px;
        background: #ffffff;
    }

    .workload-subject-picker {
        position: relative;
    }

    .workload-subject-trigger {
        width: 100%;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        padding: 10px 12px;
        font-size: 13px;
        background: #ffffff;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        text-align: left;
    }

    .workload-subject-trigger:disabled {
        background: #f3f4f6;
        color: #9ca3af;
        cursor: not-allowed;
    }

    .workload-subject-trigger-text {
        flex: 1;
        min-width: 0;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    .workload-subject-trigger-icon {
        width: 10px;
        height: 10px;
        border-right: 2px solid #64748b;
        border-bottom: 2px solid #64748b;
        transform: rotate(45deg);
        flex-shrink: 0;
        margin-top: -4px;
    }

    .workload-subject-dropdown {
        position: absolute;
        top: calc(100% + 8px);
        left: 0;
        right: 0;
        z-index: 30;
        background: #ffffff;
        border: 1px solid #cbd5e1;
        border-radius: 12px;
        box-shadow: 0 16px 30px rgba(15, 23, 42, 0.14);
        padding: 12px;
    }

    .workload-subject-search-row {
        display: flex;
        gap: 8px;
        align-items: center;
        margin-bottom: 10px;
    }

    .workload-subject-clear-btn {
        border: 1px solid #d1d5db;
        background: #f8fafc;
        color: #334155;
        border-radius: 8px;
        padding: 8px 12px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .workload-subject-options {
        max-height: 260px;
        overflow-y: auto;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
    }

    .workload-subject-option {
        width: 100%;
        border: none;
        border-bottom: 1px solid #e5e7eb;
        background: #ffffff;
        padding: 10px 12px;
        font-size: 13px;
        line-height: 1.5;
        color: #0f172a;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        text-align: left;
    }

    .workload-subject-option:last-child {
        border-bottom: none;
    }

    .workload-subject-option:hover {
        background: #eff6ff;
    }

    .workload-subject-option-name {
        min-width: 0;
        flex: 1 1 auto;
    }

    .workload-subject-option-credit {
        flex: 0 0 auto;
        margin-left: auto;
        text-align: right;
        white-space: nowrap;
        color: #334155;
    }

    .workload-subject-empty {
        padding: 12px;
        text-align: center;
        color: #64748b;
        font-size: 13px;
    }

    .workload-modal-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 12px 16px;
        margin-top: 8px;
        grid-template-areas:
            "credit link"
            "workload link"
            "note link";
    }

    .workload-modal-field {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .workload-modal-subfields {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .workload-modal-sub-label {
        font-size: 12px;
        font-weight: 600;
        color: #374151;
        margin-bottom: 4px;
    }

    .workload-modal-sub-note {
        font-size: 12px;
        color: #6b7280;
        margin-bottom: 6px;
        line-height: 1.5;
    }

    .workload-link-btn {
        align-self: flex-start;
        margin-top: 4px;
        background: #e0f2fe;
        color: #1d4ed8;
        border: none;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
    }

    .workload-evidence-row {
        display: flex;
        gap: 8px;
        align-items: center;
    }

    #workload-evidence-links {
        display: flex;
        flex-direction: column;
        gap: 8px;
    }

    .workload-evidence-remove-btn {
        background: #fee2e2;
        color: #b91c1c;
        border: none;
        padding: 6px 10px;
        border-radius: 8px;
        font-size: 12px;
        font-weight: 600;
        white-space: nowrap;
    }

    .workload-modal-note {
        grid-column: span 1;
        background: #ecfdf3;
        border: 1px solid #86efac;
        border-radius: 12px;
        padding: 10px 12px;
        color: #166534;
        font-size: 12px;
    }

    .workload-modal-field-workload {
        grid-area: workload;
    }

    .workload-modal-field-item {
        grid-area: item;
    }

    .workload-modal-field-link {
        grid-area: link;
    }

    .workload-modal-field-group {
        grid-area: group;
    }

    .workload-modal-field-credit {
        grid-area: credit;
    }

    .workload-modal-field-instructors {
        grid-area: instructors;
    }

    .workload-modal-note-box {
        grid-area: note;
    }

    .workload-modal-note h6 {
        font-size: 12px;
        font-weight: 700;
        margin-bottom: 6px;
    }

    .workload-modal-note ul {
        margin: 0;
        padding-left: 16px;
    }

    .workload-alert-box {
        display: flex;
        gap: 10px;
        align-items: flex-start;
        background: #fffbeb;
        border: 1px solid #fbbf24;
        border-radius: 10px;
        padding: 10px 12px;
        font-size: 12px;
        color: #92400e;
    }

    .workload-alert-icon {
        color: #f59e0b;
        margin-top: 2px;
    }

    .workload-alert-link {
        color: #4f46e5;
        font-weight: 600;
        text-decoration: none;
    }

    .workload-modal-footer {
        padding: 12px 18px 16px;
        border-top: 1px solid #e5e7eb;
        justify-content: flex-end;
        gap: 10px;
    }

    #subjectModal {
        z-index: 1065;
    }

    .workload-backdrop-inert {
        pointer-events: none;
    }

    .workload-cancel-btn {
        background: #e5e7eb;
        color: #111827;
        border: none;
        padding: 8px 16px;
        border-radius: 8px;
        font-weight: 600;
    }

    .required {
        color: #ef4444;
    }

    @media (max-width: 1024px) {
        .workload-toolbar,
        .workload-list-row {
            flex-direction: column;
            align-items: flex-start;
        }

        .workload-toolbar-columns,
        .workload-list-columns {
            gap: 12px;
        }
    }

    @media (max-width: 768px) {
        .workload-modal-grid {
            grid-template-columns: 1fr;
            grid-template-areas:
                "credit"
                "workload"
                "link"
                "note";
        }

        .workload-modal-note {
            grid-column: span 1;
        }
    }
</style>
