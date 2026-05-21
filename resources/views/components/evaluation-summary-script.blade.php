{{-- ไฟล์มุมมอง: resources/views/components/evaluation-summary-script.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function () {
    const summaryRoot = document.getElementById('evaluationSummary');
    if (!summaryRoot) {
        return;
    }

    const filterButtons = Array.from(summaryRoot.querySelectorAll('.evaluation-status-filter'));
    const clearFilterButton = document.getElementById('evaluationClearFilter');
    const rows = Array.from(summaryRoot.querySelectorAll('[data-evaluation-row]'));
    const emptyState = document.getElementById('evaluationSummaryEmptyState');
    const initialStatus = 'all';

    const clearStatusQuery = () => {
        const url = new URL(window.location.href);
        if (url.searchParams.has('status')) {
            url.searchParams.delete('status');
            window.history.replaceState({}, '', url);
        }
    };

    const setActiveButton = (status) => {
        filterButtons.forEach((button) => {
            const isActive = button.dataset.statusFilter === status;
            button.classList.toggle('ring-2', isActive);
            button.classList.toggle('ring-offset-2', isActive);
            button.classList.toggle('ring-blue-300', isActive);
        });
    };

    const applyFilter = (status = 'all') => {
        let visibleCount = 0;

        rows.forEach((row) => {
            const matches = status === 'all' || row.dataset.statusGroup === status;
            row.classList.toggle('hidden', !matches);
            if (matches) {
                visibleCount += 1;
            }
        });

        if (emptyState) {
            emptyState.classList.toggle('hidden', visibleCount > 0);
        }

        setActiveButton(status);
        document.dispatchEvent(new CustomEvent('evaluation-status-filter:changed', {
            detail: { status, visibleCount },
        }));
    };

    filterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            clearStatusQuery();
            applyFilter(button.dataset.statusFilter || 'all');
        });
    });

    if (clearFilterButton) {
        clearFilterButton.addEventListener('click', () => {
            clearStatusQuery();
            applyFilter('all');
        });
    }

    window.applyEvaluationStatusFilter = applyFilter;
    clearStatusQuery();
    applyFilter(initialStatus);
});

window.closeEvaluateeReviewerModal = window.closeEvaluateeReviewerModal || function () {
    const modal = document.getElementById('evaluateeReviewerModal');
    const body = document.getElementById('evaluateeReviewerModalBody');
    if (modal) modal.classList.add('hidden');
    if (body) body.innerHTML = '';
};

window.openEvaluateeReviewerModal = window.openEvaluateeReviewerModal || function (reviewers) {
    const modal = document.getElementById('evaluateeReviewerModal');
    const body = document.getElementById('evaluateeReviewerModalBody');
    if (!modal || !body) return;

    body.innerHTML = (reviewers || []).map((reviewer, index) => `
        <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
            <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ลำดับที่ ${index + 1}</div>
            <div class="mt-1 text-sm font-semibold text-slate-900">${reviewer.label}: ${reviewer.name}</div>
            <div class="mt-1 text-xs text-slate-500">${reviewer.position ?? '-'}</div>
        </div>
    `).join('');

    modal.classList.remove('hidden');
};

window.bindEvaluateeReviewerModal = window.bindEvaluateeReviewerModal || function () {
    const modal = document.getElementById('evaluateeReviewerModal');
    if (!modal || modal.dataset.bound === 'true') return;

    modal.dataset.bound = 'true';
    modal.addEventListener('click', function (event) {
        if (event.target === modal) {
            window.closeEvaluateeReviewerModal();
        }
    });

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            window.closeEvaluateeReviewerModal();
        }
    });
};

window.bindEvaluateeReviewerModal();
</script>
