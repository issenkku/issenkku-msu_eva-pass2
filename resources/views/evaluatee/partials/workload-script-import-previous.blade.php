<script>
    document.addEventListener('DOMContentLoaded', function () {
        const modal = document.querySelector('[data-import-previous-workload-modal]');
        const confirmButton = document.querySelector('[data-import-previous-workload-confirm]');
        const cancelButtons = document.querySelectorAll('[data-import-previous-workload-cancel]');
        let pendingForm = null;

        if (!modal || !confirmButton) {
            return;
        }

        if (modal.parentElement !== document.body) {
            document.body.appendChild(modal);
        }

        const openModal = function (form) {
            pendingForm = form;
            modal.hidden = false;
            document.body.classList.add('workload-import-confirm-open');
            confirmButton.focus();
        };

        const closeModal = function () {
            modal.hidden = true;
            document.body.classList.remove('workload-import-confirm-open');
            pendingForm = null;
        };

        document.querySelectorAll('[data-import-previous-workload-form]').forEach(function (form) {
            form.addEventListener('submit', function (event) {
                event.preventDefault();
                openModal(form);
            });
        });

        confirmButton.addEventListener('click', async function () {
            if (!pendingForm) {
                closeModal();
                return;
            }

            const sourceSelect = modal.querySelector('[data-import-previous-workload-source-select]');
            const sourceInput = pendingForm.querySelector('[data-import-previous-workload-source-input]');

            if (sourceInput && sourceSelect) {
                sourceInput.value = sourceSelect.value || '';
            }

            const form = pendingForm;
            const coordinator = window.AsyncForm?.createAsyncFormCoordinator({
                applySuccess: async function (payload) {
                    window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(
                        document,
                        payload,
                        payload.active_item_id || '',
                    );
                    closeModal();
                },
                form,
                getSubmitButton: function () { return confirmButton; },
                request: window.AsyncForm.requestFormMutation,
                showMessage: window.MasterDataPage.showMasterDataMessage,
            });

            if (!coordinator) {
                HTMLFormElement.prototype.submit.call(form);
                return;
            }

            confirmButton.classList.add('is-loading');
            await coordinator({ preventDefault: function () {} });
            confirmButton.classList.remove('is-loading');
        });

        cancelButtons.forEach(function (button) {
            button.addEventListener('click', closeModal);
        });

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !modal.hidden) {
                closeModal();
            }
        });
    });
</script>
