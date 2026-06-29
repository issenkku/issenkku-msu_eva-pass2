{{-- สคริปต์เปิด modal ยืนยันการลบ --}}
<script>
    window.confirmDelete = function (id) {
        const form = document.getElementById('deleteForm');
        const modalEl = document.getElementById('deleteModal');

        if (!form || !modalEl) return;

        const actionTemplate = @json($formAction);
        form.action = actionTemplate
            .replace(':id', encodeURIComponent(id))
            .replace('%3Aid', encodeURIComponent(id));

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };

    document.addEventListener('click', function (event) {
        const deleteButton = event.target.closest('[data-delete-trigger]');
        if (!deleteButton) {
            return;
        }

        event.preventDefault();

        const deleteId = deleteButton.dataset.deleteId;
        if (deleteId && typeof window.confirmDelete === 'function') {
            window.confirmDelete(deleteId);
        }
    });
</script>
