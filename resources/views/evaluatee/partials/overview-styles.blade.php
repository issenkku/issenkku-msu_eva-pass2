{{-- ไฟล์มุมมอง: resources/views/evaluatee/partials/overview-styles.blade.php --}}
<style>
    .evaluation-overview-card {
        position: relative;
        width: 100%;
        text-align: left;
        transition: transform 160ms ease, box-shadow 160ms ease, border-color 160ms ease;
    }

    .evaluation-overview-card:hover {
        transform: translateY(-1px);
    }

    .evaluation-overview-card:focus-visible {
        outline: none;
        box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.18);
    }

    .evaluation-overview-card.is-active {
        transform: translateY(-1px);
        box-shadow: 0 12px 28px -18px rgba(15, 23, 42, 0.45);
    }

    .evaluation-overview-card.is-active::after {
        content: '';
        position: absolute;
        inset: 0;
        border-radius: 0.75rem;
        box-shadow: inset 0 0 0 2px currentColor;
        opacity: 0.2;
        pointer-events: none;
    }

    .evaluation-overview-card[data-status-filter='ยังไม่ประเมิน'].is-active {
        box-shadow: 0 14px 28px -18px rgba(244, 63, 94, 0.55);
    }

    .evaluation-overview-card[data-status-filter='กำลังดำเนินการ'].is-active {
        box-shadow: 0 14px 28px -18px rgba(59, 130, 246, 0.55);
    }

    .evaluation-overview-card[data-status-filter='รอผลการประเมิน'].is-active {
        box-shadow: 0 14px 28px -18px rgba(245, 158, 11, 0.55);
    }

    .evaluation-overview-card[data-status-filter='ประเมินเสร็จสิ้น'].is-active {
        box-shadow: 0 14px 28px -18px rgba(16, 185, 129, 0.55);
    }

    @media (max-width: 768px) {
        .space-y-6 > * + * {
            margin-top: 1rem;
        }

        .max-w-4xl {
            max-width: 100%;
            padding: 0 1rem;
        }
    }
</style>
