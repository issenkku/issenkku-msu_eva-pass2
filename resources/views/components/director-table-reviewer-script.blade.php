{{-- สคริปต์ควบคุม modal รายชื่อผู้ประเมินของตาราง director --}}
<script>
    window.closeDirectorReviewerModal = window.closeDirectorReviewerModal || function () {
        const modal = document.getElementById('directorReviewerModal');
        const body = document.getElementById('directorReviewerModalBody');

        if (modal) {
            modal.classList.add('hidden');
        }

        if (body) {
            body.innerHTML = '';
        }
    };

    window.openDirectorReviewerModal = window.openDirectorReviewerModal || function (reviewers) {
        const modal = document.getElementById('directorReviewerModal');
        const body = document.getElementById('directorReviewerModalBody');

        if (!modal || !body) {
            return;
        }

        body.innerHTML = (reviewers || []).map((reviewer, index) => `
            <div class="rounded-xl border border-slate-200 bg-slate-50 px-4 py-3">
                <div class="text-xs font-semibold uppercase tracking-wide text-slate-500">ลำดับที่ ${index + 1}</div>
                <div class="mt-1 text-sm font-semibold text-slate-900">${reviewer.label}: ${reviewer.name}</div>
                <div class="mt-1 text-xs text-slate-500">${reviewer.position ?? '-'}</div>
            </div>
        `).join('');

        modal.classList.remove('hidden');
    };

    window.bindDirectorReviewerModal = window.bindDirectorReviewerModal || function () {
        const modal = document.getElementById('directorReviewerModal');
        if (!modal || modal.dataset.bound === 'true') {
            return;
        }

        modal.dataset.bound = 'true';

        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                window.closeDirectorReviewerModal();
            }
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                window.closeDirectorReviewerModal();
            }
        });
    };

    window.bindDirectorReviewerModal();
</script>
