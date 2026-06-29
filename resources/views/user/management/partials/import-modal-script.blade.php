<script>
    let selectedFile = null;

    function openImportModal(button) {
        const modal = document.getElementById('importUserModal');
        const form = document.getElementById('importForm');

        if (!modal || !form) {
            return;
        }

        form.reset();
        resetFileUpload();

        if (button && button.getAttribute) {
            const action = button.getAttribute('data-action');
            if (action) {
                form.action = action;
            }
        }

        document.getElementById('formMethod').value = 'POST';
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }

    function closeImportModal() {
        const modal = document.getElementById('importUserModal');
        const form = document.getElementById('importForm');

        if (!modal || !form) {
            return;
        }

        form.reset();
        resetFileUpload();
        resetFormState();

        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }

    function handleDragOver(event) {
        event.preventDefault();
        event.currentTarget.classList.add('drag-over');
    }

    function handleDragLeave(event) {
        event.preventDefault();
        event.currentTarget.classList.remove('drag-over');
    }

    function handleDrop(event) {
        event.preventDefault();
        event.currentTarget.classList.remove('drag-over');

        const files = event.dataTransfer.files;
        if (files.length > 0) {
            handleFile(files[0]);
        }
    }

    function handleFileSelect(event) {
        const file = event.target.files[0];
        if (file) {
            handleFile(file);
        }
    }

    function handleFile(file) {
        const allowedTypes = ['.xlsx', '.xls', '.csv'];
        const fileExtension = '.' + file.name.split('.').pop().toLowerCase();

        if (!allowedTypes.includes(fileExtension)) {
            alert('กรุณาเลือกไฟล์ .xlsx, .xls หรือ .csv เท่านั้น');
            return;
        }

        if (file.size > 10 * 1024 * 1024) {
            alert('ขนาดไฟล์ใหญ่เกินไป กรุณาเลือกไฟล์ที่มีขนาดไม่เกิน 10MB');
            return;
        }

        selectedFile = file;
        displaySelectedFile(file);
        enableSubmitButton();
    }

    function displaySelectedFile(file) {
        const fileInfo = document.getElementById('selectedFileInfo');
        const fileName = document.getElementById('fileName');
        const fileSize = document.getElementById('fileSize');

        if (!fileInfo || !fileName || !fileSize) {
            return;
        }

        fileName.textContent = file.name;
        fileSize.textContent = formatFileSize(file.size);
        fileInfo.classList.remove('hidden');
    }

    function removeFile() {
        selectedFile = null;
        document.getElementById('fileInput').value = '';
        document.getElementById('selectedFileInfo').classList.add('hidden');
        disableSubmitButton();
    }

    function resetFileUpload() {
        selectedFile = null;
        document.getElementById('fileInput').value = '';
        document.getElementById('selectedFileInfo').classList.add('hidden');
        document.getElementById('uploadProgress').classList.add('hidden');
        disableSubmitButton();
    }

    function resetFormState() {
        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const loadingIcon = document.getElementById('loadingIcon');
        const progressDiv = document.getElementById('uploadProgress');

        if (!submitBtn || !submitText || !loadingIcon || !progressDiv) {
            return;
        }

        submitBtn.disabled = false;
        submitText.textContent = 'นำเข้าข้อมูล';
        loadingIcon.classList.add('hidden');
        progressDiv.classList.add('hidden');
    }

    function enableSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = false;
        }
    }

    function disableSubmitButton() {
        const submitBtn = document.getElementById('submitBtn');
        if (submitBtn) {
            submitBtn.disabled = true;
        }
    }

    function formatFileSize(bytes) {
        if (bytes === 0) {
            return '0 Bytes';
        }

        const k = 1024;
        const sizes = ['Bytes', 'KB', 'MB', 'GB'];
        const i = Math.floor(Math.log(bytes) / Math.log(k));
        return parseFloat((bytes / Math.pow(k, i)).toFixed(2)) + ' ' + sizes[i];
    }

    function simulateUploadProgress() {
        const progressBar = document.querySelector('.progress-bar');
        const progressText = document.getElementById('progressText');
        let progress = 0;

        if (!progressBar || !progressText) {
            return;
        }

        const interval = setInterval(() => {
            progress += Math.random() * 15;
            if (progress > 100) {
                progress = 100;
            }

            progressBar.style.width = progress + '%';
            progressText.textContent = Math.round(progress) + '%';

            if (progress >= 100) {
                clearInterval(interval);
            }
        }, 200);
    }

    document.addEventListener('click', function(event) {
        const importOpenButton = event.target.closest('[data-import-modal-open]');
        if (importOpenButton) {
            event.preventDefault();
            openImportModal(importOpenButton);
            return;
        }

        const importCloseButton = event.target.closest('[data-import-modal-close]');
        if (importCloseButton) {
            event.preventDefault();
            closeImportModal();
            return;
        }

        const dropZone = event.target.closest('[data-import-drop-zone]');
        if (dropZone) {
            const fileInput = document.getElementById('fileInput');
            if (fileInput) {
                fileInput.click();
            }
        }

        const removeFileButton = event.target.closest('[data-import-remove-file]');
        if (removeFileButton) {
            event.preventDefault();
            removeFile();
        }
    });

    document.addEventListener('dragover', function(event) {
        const dropZone = event.target.closest?.('[data-import-drop-zone]');
        if (!dropZone) {
            return;
        }

        handleDragOver({
            preventDefault: () => event.preventDefault(),
            currentTarget: dropZone,
        });
    });

    document.addEventListener('dragleave', function(event) {
        const dropZone = event.target.closest?.('[data-import-drop-zone]');
        if (!dropZone) {
            return;
        }

        handleDragLeave({
            preventDefault: () => event.preventDefault(),
            currentTarget: dropZone,
        });
    });

    document.addEventListener('drop', function(event) {
        const dropZone = event.target.closest?.('[data-import-drop-zone]');
        if (!dropZone) {
            return;
        }

        handleDrop({
            preventDefault: () => event.preventDefault(),
            currentTarget: dropZone,
            dataTransfer: event.dataTransfer,
        });
    });

    document.addEventListener('change', function(event) {
        const fileInput = event.target.closest?.('[data-import-file-input]');
        if (!fileInput) {
            return;
        }

        handleFileSelect({ target: fileInput });
    });

    document.getElementById('importForm')?.addEventListener('submit', function(e) {
        if (!selectedFile) {
            e.preventDefault();
            alert('กรุณาเลือกไฟล์ที่ต้องการนำเข้า');
            return;
        }

        const submitBtn = document.getElementById('submitBtn');
        const submitText = document.getElementById('submitText');
        const loadingIcon = document.getElementById('loadingIcon');
        const progressDiv = document.getElementById('uploadProgress');

        submitBtn.disabled = true;
        submitText.textContent = 'กำลังนำเข้า...';
        loadingIcon.classList.remove('hidden');
        progressDiv.classList.remove('hidden');

        simulateUploadProgress();
    });

    document.addEventListener('DOMContentLoaded', function() {
        const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');

        messages.forEach(function(message) {
            setTimeout(function() {
                if (message.parentElement) {
                    message.style.transform = 'translateX(100%)';
                    setTimeout(function() {
                        if (message.parentElement) {
                            message.remove();
                        }
                    }, 300);
                }
            }, 5000);
        });
    });
</script>

@if(session('import_errors'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            openImportModal();
        });
    </script>
@endif
