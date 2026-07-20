{{-- สคริปต์กลางสำหรับ modal ยืนยันและการ submit ฟอร์มประเมิน --}}
<script>
    function setFormStatus(status) {
        document.getElementById('formStatus').value = status;
    }

    document.addEventListener('DOMContentLoaded', function() {
        const evaluationForm = document.getElementById('evaluationForm');
        const openModalBtn = document.getElementById('openModalBtn');
        const confirmationModal = document.getElementById('confirmationModal');
        const modalContent = document.getElementById('modal-content');
        const cancelModalBtn = document.getElementById('cancelModalBtn');
        const confirmSubmitBtn = document.getElementById('confirmSubmitBtn');
        const loadingOverlay = document.getElementById('loading_overlay');

        if (!openModalBtn || !confirmationModal || !modalContent || !cancelModalBtn || !confirmSubmitBtn || !loadingOverlay || !evaluationForm) {
            return;
        }

        function openModal(modal, content) {
            modal.classList.remove('hidden');
            setTimeout(() => {
                modal.classList.remove('opacity-0');
                content.classList.remove('scale-95', 'opacity-0');
                content.classList.add('scale-100', 'opacity-100');
            }, 10);
        }

        function closeModal(modal, content) {
            content.classList.remove('scale-100', 'opacity-100');
            content.classList.add('scale-95', 'opacity-0');
            modal.classList.add('opacity-0');
            setTimeout(() => {
                modal.classList.add('hidden');
            }, 300);
        }

        function showLoading() {
            loadingOverlay.classList.remove('hidden');
        }

        function showSupportValidationErrors(errors) {
            document.querySelector('.support-validation-error-alert')?.remove();
            if (errors.length === 0) return;

            const alert = document.createElement('div');
            alert.className = 'support-validation-error-alert mb-4 rounded-lg border border-red-400 bg-red-100 px-4 py-3 text-red-700';
            alert.setAttribute('role', 'alert');
            const list = document.createElement('ul');
            list.className = 'list-inside list-disc';
            [...new Set(errors)].forEach((message) => {
                const item = document.createElement('li');
                item.textContent = message;
                list.appendChild(item);
            });
            alert.appendChild(list);
            evaluationForm.prepend(alert);
            alert.scrollIntoView({ behavior: 'smooth', block: 'center' });
        }

        evaluationForm.addEventListener('submit', (event) => {
            const errors = window.validateSupportCriteria?.() ?? [];
            if (errors.length === 0) return;

            event.preventDefault();
            showSupportValidationErrors(errors);
        });

        openModalBtn.addEventListener('click', (event) => {
            event.preventDefault();
            const errors = window.validateSupportCriteria?.() ?? [];
            if (errors.length > 0) {
                showSupportValidationErrors(errors);
                return;
            }

            openModal(confirmationModal, modalContent);
        });

        cancelModalBtn.addEventListener('click', () => {
            closeModal(confirmationModal, modalContent);
        });

        confirmationModal.addEventListener('click', function(event) {
            if (event.target === confirmationModal) {
                closeModal(confirmationModal, modalContent);
            }
        });

        confirmSubmitBtn.addEventListener('click', function() {
            closeModal(confirmationModal, modalContent);

            setTimeout(() => {
                setFormStatus(@json($confirmStatus));
                showLoading();
                evaluationForm.submit();
            }, 350);
        });

        document.querySelectorAll('[data-form-status-trigger]').forEach((button) => {
            button.addEventListener('click', function () {
                setFormStatus(this.dataset.formStatus || '');
            });
        });
    });
</script>
