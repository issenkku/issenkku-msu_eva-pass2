<script>
    document.addEventListener('DOMContentLoaded', function () {
        const deleteForm = document.getElementById('deleteForm');
        const deleteModalEl = document.getElementById('deleteModal');

        if (!deleteForm || !deleteModalEl) {
            return;
        }

        document.addEventListener('click', function (event) {
            const deleteButton = event.target.closest('[data-delete-trigger]');
            if (!deleteButton) {
                return;
            }

            deleteForm.dataset.workloadItemId = deleteButton.dataset.workloadItemId || '';
        }, true);

        if (
            !window.WorkloadEntrySubmit ||
            !window.WorkloadEntrySubmit.createWorkloadEntryDeleteCoordinator
        ) {
            return;
        }

        const deleteCoordinator = window.WorkloadEntrySubmit.createWorkloadEntryDeleteCoordinator({
            form: deleteForm,
            deletingLabel: 'กำลังลบ...',
            getSubmitButton: function () {
                return deleteForm.querySelector('button[type="submit"]');
            },
            requestDelete: window.WorkloadEntrySubmit.requestWorkloadEntrySave,
            applyResponse: function (payload) {
                window.WorkloadEntrySubmit.applyWorkloadEntrySaveResponse(
                    document,
                    payload,
                    deleteForm.dataset.workloadItemId || ''
                );
            },
            hideModal: function () {
                bootstrap.Modal.getOrCreateInstance(deleteModalEl).hide();
            },
            showMessage: function (message, isError) {
                if (typeof window.showWorkloadSaveMessage === 'function') {
                    window.showWorkloadSaveMessage(message, isError);
                }
            },
        });

        deleteForm.addEventListener('submit', deleteCoordinator);
    });
</script>
