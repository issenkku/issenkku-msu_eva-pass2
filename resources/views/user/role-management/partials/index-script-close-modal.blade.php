function closeModal() {
    document.getElementById('roleModal').classList.remove('show');
}

document.addEventListener('click', function (event) {
    const closeButton = event.target.closest('[data-role-modal-close]');

    if (!closeButton) {
        return;
    }

    closeModal();
});
