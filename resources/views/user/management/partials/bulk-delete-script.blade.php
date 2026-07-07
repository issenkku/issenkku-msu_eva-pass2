<script>
    (() => {
        const initializeUserBulkDelete = () => {
            const getCheckboxes = () => Array.from(document.querySelectorAll('[data-user-bulk-checkbox]'));
            const selectAll = document.querySelector('[data-user-bulk-select-all]');
            const bulkDeleteButton = document.querySelector('[data-user-bulk-delete-open]');
            const selectedCount = document.querySelector('[data-user-bulk-delete-selected-count]');
            const selectedInputs = document.querySelector('[data-user-bulk-delete-selected-inputs]');
            const bulkStatusForm = document.querySelector('[data-user-bulk-status-form]');
            const bulkStatusInputs = document.querySelector('[data-user-bulk-status-selected-inputs]');
            const bulkStatusValue = document.querySelector('[data-user-bulk-status-value]');
            const modalElement = document.getElementById('bulkDeleteUsersModal');
            const statusModalElement = document.getElementById('bulkStatusUsersModal');
            const statusModalCount = document.querySelector('[data-user-bulk-status-selected-count]');
            const statusModalLabel = document.querySelector('[data-user-bulk-status-label]');
            const statusConfirmButton = document.querySelector('[data-user-bulk-status-confirm]');

            const getSelectedIds = () => getCheckboxes()
                .filter((checkbox) => checkbox.checked)
                .map((checkbox) => checkbox.value);

            const updateBulkDeleteState = () => {
                const checkboxes = getCheckboxes();
                const selectedIds = getSelectedIds();
                const selectedTotal = selectedIds.length;

                if (bulkDeleteButton) {
                    bulkDeleteButton.classList.toggle('hidden', selectedTotal === 0);
                }

                if (bulkStatusForm) {
                    bulkStatusForm.classList.toggle('hidden', selectedTotal === 0);
                }

                if (selectedCount) {
                    selectedCount.textContent = String(selectedTotal);
                }

                if (selectAll) {
                    selectAll.checked = checkboxes.length > 0 && selectedTotal === checkboxes.length;
                    selectAll.indeterminate = selectedTotal > 0 && selectedTotal < checkboxes.length;
                }
            };

            const fillSelectedInputs = () => {
                if (!selectedInputs) {
                    return;
                }

                selectedInputs.innerHTML = '';
                getSelectedIds().forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = id;
                    selectedInputs.appendChild(input);
                });
            };

            const fillBulkStatusInputs = () => {
                if (!bulkStatusInputs) {
                    return;
                }

                bulkStatusInputs.innerHTML = '';
                getSelectedIds().forEach((id) => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'user_ids[]';
                    input.value = id;
                    bulkStatusInputs.appendChild(input);
                });
            };

            selectAll?.addEventListener('change', () => {
                getCheckboxes().forEach((checkbox) => {
                    checkbox.checked = selectAll.checked;
                });
                updateBulkDeleteState();
            });

            document.addEventListener('change', (event) => {
                if (event.target.closest('[data-user-bulk-checkbox]')) {
                    updateBulkDeleteState();
                }
            });

            bulkDeleteButton?.addEventListener('click', (event) => {
                event.preventDefault();

                if (getSelectedIds().length === 0 || !modalElement) {
                    return;
                }

                fillSelectedInputs();
                bootstrap.Modal.getOrCreateInstance(modalElement).show();
            });

            bulkStatusForm?.addEventListener('submit', (event) => {
                const submitter = event.submitter?.closest('[data-user-bulk-status-option]');
                const status = submitter?.value || '';
                const selectedTotal = getSelectedIds().length;
                const statusLabel = submitter?.textContent?.trim() || status;

                event.preventDefault();

                if (bulkStatusValue) {
                    bulkStatusValue.value = status;
                }

                if (selectedTotal === 0 || !status) {
                    return;
                }

                fillBulkStatusInputs();

                if (statusModalCount) {
                    statusModalCount.textContent = String(selectedTotal);
                }

                if (statusModalLabel) {
                    statusModalLabel.textContent = statusLabel;
                }

                if (statusModalElement) {
                    bootstrap.Modal.getOrCreateInstance(statusModalElement).show();
                }
            });

            statusConfirmButton?.addEventListener('click', () => {
                if (!bulkStatusForm || !bulkStatusValue?.value || getSelectedIds().length === 0) {
                    return;
                }

                bulkStatusForm.submit();
            });

            updateBulkDeleteState();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeUserBulkDelete, { once: true });
        } else {
            initializeUserBulkDelete();
        }
    })();
</script>
