{{-- สคริปต์เปิด modal ยืนยันการลบ --}}
<script>
    window.confirmDelete = function (id) {
        const form = document.getElementById('deleteForm');
        const modalEl = document.getElementById('deleteModal');

        if (!form || !modalEl) return;

        const actionTemplate = "{{ $formAction }}";
        form.action = actionTemplate.replace(':id', id);

        const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
        modal.show();
    };
</script>
