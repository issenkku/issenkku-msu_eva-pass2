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
</script>
