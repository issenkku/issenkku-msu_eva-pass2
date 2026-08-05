<script>
    document.addEventListener('DOMContentLoaded', function () {
        const stateEl = document.getElementById('workloadSaveState');
        const reminderEl = document.getElementById('workloadSaveReminder');
        const reminderTextEl = document.getElementById('workloadSaveReminderText');
        const workloadScoreForm = document.getElementById('workloadScoreForm');
        const backLinks = document.querySelectorAll('.workload-back-btn');
        const unsavedModal = document.querySelector('[data-workload-unsaved-modal]');
        const unsavedCancelButtons = document.querySelectorAll('[data-workload-unsaved-cancel]');
        const unsavedConfirmButton = document.querySelector('[data-workload-unsaved-confirm]');

        if (!stateEl || !reminderEl) {
            return;
        }

        if (unsavedModal && unsavedModal.parentElement !== document.body) {
            document.body.appendChild(unsavedModal);
        }

        const parseScore = function (value) {
            const parsed = Number(value);
            return Number.isFinite(parsed) ? parsed : 0;
        };

        let currentTotal = parseScore(stateEl.dataset.currentTotal || '0');
        const savedTotalRaw = stateEl.dataset.savedTotal || '';
        const hasSavedTotal = savedTotalRaw !== '';
        const savedTotal = hasSavedTotal ? parseScore(savedTotalRaw) : 0;
        let hasUnsavedChanges = hasSavedTotal
            ? Math.abs(currentTotal - savedTotal) > 0.0001
            : currentTotal > 0;
        let allowPageExit = false;
        let pendingBackUrl = '';

        function updateReminder() {
            reminderEl.hidden = !hasUnsavedChanges;
            if (!hasUnsavedChanges || !reminderTextEl) {
                return;
            }

            reminderTextEl.textContent = 'กรุณากดบันทึกด้านล่างเพื่อยืนยันคะแนนภาระงานล่าสุด';
        }

        function openUnsavedModal(targetUrl) {
            if (!unsavedModal || !unsavedConfirmButton) {
                return false;
            }

            pendingBackUrl = targetUrl;
            unsavedModal.hidden = false;
            document.body.classList.add('workload-unsaved-confirm-open');
            unsavedConfirmButton.focus();

            return true;
        }

        function closeUnsavedModal() {
            if (!unsavedModal) {
                return;
            }

            pendingBackUrl = '';
            unsavedModal.hidden = true;
            document.body.classList.remove('workload-unsaved-confirm-open');
        }

        if (workloadScoreForm) {
            workloadScoreForm.addEventListener('submit', function () {
                allowPageExit = true;
                hasUnsavedChanges = false;
                updateReminder();
            });
        }

        document.addEventListener('submit', function (event) {
            if (
                event.target &&
                (
                    event.target.id === 'workloadEntryForm' ||
                    (event.target.id === 'deleteForm' && event.target.dataset.workloadItemId)
                )
            ) {
                return;
            }
            allowPageExit = true;
        }, true);

        document.addEventListener('workload:total-updated', function (event) {
            currentTotal = parseScore(event.detail ? event.detail.total : 0);
            hasUnsavedChanges = hasSavedTotal
                ? Math.abs(currentTotal - savedTotal) > 0.0001
                : currentTotal > 0;
            updateReminder();
        });

        backLinks.forEach(function (link) {
            link.addEventListener('click', function (event) {
                if (!hasUnsavedChanges) {
                    return;
                }

                event.preventDefault();

                if (!openUnsavedModal(link.href)) {
                    allowPageExit = true;
                    window.location.href = link.href;
                }
            });
        });

        unsavedCancelButtons.forEach(function (button) {
            button.addEventListener('click', function () {
                closeUnsavedModal();
            });
        });

        if (unsavedConfirmButton) {
            unsavedConfirmButton.addEventListener('click', function () {
                if (!pendingBackUrl) {
                    closeUnsavedModal();
                    return;
                }

                allowPageExit = true;
                window.location.href = pendingBackUrl;
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && unsavedModal && !unsavedModal.hidden) {
                closeUnsavedModal();
            }
        });

        window.addEventListener('beforeunload', function (event) {
            if (!hasUnsavedChanges || allowPageExit) {
                return;
            }

            event.preventDefault();
            event.returnValue = '';
        });

        updateReminder();
    });
</script>
