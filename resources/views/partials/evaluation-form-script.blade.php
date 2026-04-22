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

        openModalBtn.addEventListener('click', () => {
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
    });
</script>
