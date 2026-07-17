<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('subjectImportPreviewModal');
    const cancelForm = document.getElementById('subjectImportCancelForm');
    if (!modalElement || !cancelForm) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    const conflicts = Array.from(modalElement.querySelectorAll('[data-subject-import-conflict]'));

    modalElement.querySelector('[data-subject-import-select-all]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = true; });
    });
    modalElement.querySelector('[data-subject-import-select-none]')?.addEventListener('click', function () {
        conflicts.forEach(function (checkbox) { checkbox.checked = false; });
    });

    modalElement.addEventListener('shown.bs.modal', function () {
        document.getElementById('subjectImportPreviewModalLabel')?.focus();
    });

    modalElement.addEventListener('keydown', function (event) {
        if (event.key === 'Escape') {
            event.preventDefault();
            event.stopImmediatePropagation();
            cancelForm.requestSubmit();
        }
    }, true);

    modal.show();
});
</script>
