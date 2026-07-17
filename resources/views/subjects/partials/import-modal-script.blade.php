<script>
document.addEventListener('DOMContentLoaded', function () {
    const modalElement = document.getElementById('subjectImportModal');
    const input = document.querySelector('[data-subject-import-file]');
    const zone = document.querySelector('[data-subject-import-drop-zone]');
    const submit = document.querySelector('[data-subject-import-submit]');
    const selection = document.querySelector('[data-subject-import-selection]');
    const filename = document.querySelector('[data-subject-import-filename]');
    const clientError = document.querySelector('[data-subject-import-client-error]');
    if (!modalElement || !input || !zone || !submit || !selection || !filename || !clientError) return;

    const modal = bootstrap.Modal.getOrCreateInstance(modalElement);
    let opener = null;
    const setFile = function (file) {
        const valid = file && file.name.toLowerCase().endsWith('.xlsx') && file.size <= 10 * 1024 * 1024;
        submit.disabled = !valid;
        selection.classList.toggle('d-none', !valid);
        filename.textContent = valid ? `${file.name} (${(file.size / 1024 / 1024).toFixed(2)} MB)` : '';
        clientError.textContent = !valid && file ? 'กรุณาเลือกไฟล์ .xlsx ขนาดไม่เกิน 10 MB' : '';
        clientError.classList.toggle('d-none', valid || !file);
    };

    document.querySelectorAll('[data-subject-import-open]').forEach(function (button) {
        button.addEventListener('click', function () {
            opener = button;
            modal.show();
        });
    });
    modalElement.addEventListener('hidden.bs.modal', function () {
        opener?.focus();
        opener = null;
    });
    zone.addEventListener('click', function () { input.click(); });
    zone.addEventListener('keydown', function (event) {
        if (event.key === 'Enter' || event.key === ' ') {
            event.preventDefault();
            input.click();
        }
    });
    ['dragenter', 'dragover'].forEach(function (name) {
        zone.addEventListener(name, function (event) {
            event.preventDefault();
            zone.classList.add('border-primary');
        });
    });
    ['dragleave', 'drop'].forEach(function (name) {
        zone.addEventListener(name, function (event) {
            event.preventDefault();
            zone.classList.remove('border-primary');
        });
    });
    zone.addEventListener('drop', function (event) {
        if (event.dataTransfer.files.length) {
            input.files = event.dataTransfer.files;
            setFile(event.dataTransfer.files[0]);
        }
    });
    input.addEventListener('change', function () { setFile(input.files[0]); });
    document.querySelector('[data-subject-import-remove]')?.addEventListener('click', function () {
        input.value = '';
        setFile(null);
        input.focus();
    });

    @if(isset($errors) && $errors->subjectImport->any())
        modal.show();
    @endif
});
</script>
