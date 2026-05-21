{{-- ไฟล์มุมมอง: resources/views/user/role-management/partials/index-script-open-modal.blade.php --}}
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
