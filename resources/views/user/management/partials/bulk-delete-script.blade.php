<script>
    (() => {
        const initializeUserBulkDelete = () => {
            const getCheckboxes = () => Array.from(document.querySelectorAll('[data-user-bulk-checkbox]'));
            const selectAll = document.querySelector('[data-user-bulk-select-all]');
            const bulkDeleteButton = document.querySelector('[data-user-bulk-delete-open]');
            const selectedCount = document.querySelector('[data-user-bulk-delete-selected-count]');
            const selectedInputs = document.querySelector('[data-user-bulk-delete-selected-inputs]');
            const modalElement = document.getElementById('bulkDeleteUsersModal');

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

            updateBulkDeleteState();
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', initializeUserBulkDelete, { once: true });
        } else {
            initializeUserBulkDelete();
        }
    })();
</script>
