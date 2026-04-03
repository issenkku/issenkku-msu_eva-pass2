{{-- script ของหน้าบทบาท ใช้เปิดและปิด modal สร้างบทบาทใหม่ --}}
<script>
    function openCreateModal() {
        document.getElementById('roleModal').classList.add('show');
        document.getElementById('modalTitle').innerText = 'เพิ่มบทบาท';
        document.getElementById('roleForm').action = '{{ route("roles.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('roleName').value = '';
        document.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
            checkbox.checked = false;
        });
    }

    function closeModal() {
        document.getElementById('roleModal').classList.remove('show');
    }
</script>
