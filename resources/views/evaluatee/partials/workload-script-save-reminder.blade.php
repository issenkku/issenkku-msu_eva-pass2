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
        let hasSavedTotal = savedTotalRaw !== '';
        let savedTotal = hasSavedTotal ? parseScore(savedTotalRaw) : 0;
        let hasUnsavedChanges = hasSavedTotal
            ? Math.abs(currentTotal - savedTotal) > 0.0001
            : currentTotal > 0;
        let allowPageExit = false;
        let pendingBackUrl = '';
        let workloadScoreSaveInFlight = false;

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

        function showSaveMessage(message, isError) {
            if (typeof window.MasterDataPage?.showMasterDataMessage === 'function') {
                window.MasterDataPage.showMasterDataMessage(message, isError);
                return;
            }

            let messageEl = document.getElementById('asyncMutationMessage');
            if (!messageEl) {
                messageEl = document.createElement('div');
                messageEl.id = 'asyncMutationMessage';
                messageEl.setAttribute?.('role', 'alert');
                document.body.append?.(messageEl);
            }

            messageEl.className = isError
                ? 'fixed bottom-4 right-4 z-[10000] rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-red-800 shadow-lg'
                : 'fixed bottom-4 right-4 z-[10000] rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-emerald-800 shadow-lg';
            messageEl.textContent = message;
            messageEl.hidden = false;
            window.setTimeout?.(function () {
                messageEl.hidden = true;
            }, 5000);
        }

        async function requestWorkloadScoreSave() {
            const response = await window.fetch(workloadScoreForm.action, {
                body: new FormData(workloadScoreForm),
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                method: 'POST',
            });

            let payload = {};
            try {
                payload = await response.json();
            } catch {
                throw new Error('เซิร์ฟเวอร์ตอบกลับไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง');
            }

            if (response.redirected || !response.ok || payload?.success !== true) {
                const error = new Error(payload?.message || 'ไม่สามารถบันทึกคะแนนภาระงานได้ กรุณาลองใหม่อีกครั้ง');
                error.status = response.status;
                error.errors = payload?.errors || {};
                throw error;
            }

            return payload;
        }

        async function applyScoreSave(payload) {
            currentTotal = parseScore(payload.total_score);
            savedTotal = parseScore(payload.saved_total);
            hasSavedTotal = true;
            hasUnsavedChanges = false;
            stateEl.dataset.currentTotal = String(currentTotal);
            stateEl.dataset.savedTotal = String(savedTotal);
            updateReminder();
        }

        if (workloadScoreForm) {
            workloadScoreForm.addEventListener('submit', async function (event) {
                event.preventDefault();

                if (workloadScoreSaveInFlight) {
                    return;
                }
                workloadScoreSaveInFlight = true;

                const createCoordinator = window.AsyncForm?.createAsyncFormCoordinator;
                const requestMutation = window.AsyncForm?.requestFormMutation;
                const coordinator = typeof createCoordinator === 'function' && typeof requestMutation === 'function'
                    ? createCoordinator({
                        applySuccess: applyScoreSave,
                        form: workloadScoreForm,
                        getSubmitButton: function () {
                            return workloadScoreForm.querySelector('[type="submit"]');
                        },
                        request: requestMutation,
                        showMessage: showSaveMessage,
                    })
                    : null;

                if (coordinator) {
                    try {
                        await coordinator({ preventDefault: function () {} });
                    } finally {
                        workloadScoreSaveInFlight = false;
                    }
                    return;
                }

                const submitButton = workloadScoreForm.querySelector('[type="submit"]');
                const originalLabel = submitButton?.textContent;
                if (submitButton) {
                    submitButton.disabled = true;
                    submitButton.textContent = 'กำลังบันทึก...';
                }

                try {
                    const payload = await requestWorkloadScoreSave();
                    await applyScoreSave(payload);
                    showSaveMessage(payload.message || 'บันทึกคะแนนภาระงานเรียบร้อยแล้ว', false);
                } catch (error) {
                    showSaveMessage(error?.message || 'ไม่สามารถบันทึกคะแนนภาระงานได้ กรุณาลองใหม่อีกครั้ง', true);
                } finally {
                    if (submitButton) {
                        submitButton.disabled = false;
                        submitButton.textContent = originalLabel;
                    }
                    workloadScoreSaveInFlight = false;
                }
            });
        }

        document.addEventListener('submit', function (event) {
            if (
                event.target &&
                (
                    event.target.id === 'workloadEntryForm' ||
                    event.target.id === 'workloadScoreForm' ||
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
