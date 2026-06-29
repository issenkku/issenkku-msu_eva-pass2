function openCreateModal() {
    document.getElementById('roleModal').classList.add('show');
    document.getElementById('modalTitle').innerText = 'เน€เธเธดเนเธกเธเธ—เธเธฒเธ—';
    document.getElementById('roleForm').action = '{{ route("roles.store") }}';
    document.getElementById('formMethod').value = 'POST';
    document.getElementById('roleName').value = '';

    document.querySelectorAll('.permission-checkbox').forEach((checkbox) => {
        checkbox.checked = false;
    });
}

document.addEventListener('click', function (event) {
    const openButton = event.target.closest('[data-role-modal-open]');

    if (!openButton) {
        return;
    }

    openCreateModal();
});
