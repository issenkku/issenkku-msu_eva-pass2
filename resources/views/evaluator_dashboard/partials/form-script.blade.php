<script>
    function showLoading() {
        const loadingOverlay = document.getElementById('loading_overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.remove('hidden');
        }
    }

    function hideLoading() {
        const loadingOverlay = document.getElementById('loading_overlay');
        if (loadingOverlay) {
            loadingOverlay.classList.add('hidden');
        }
    }

    function confirmSubmit() {
        const scoreInputs = document.querySelectorAll('.score-input[type="number"]');
        let emptyFound = false;

        scoreInputs.forEach((input) => {
            if (!input.disabled && input.offsetParent !== null && (input.value === '' || input.value === null)) {
                emptyFound = true;
            }
        });

        if (emptyFound) {
            alert('กรุณากรอกคะแนนให้ครบทุกช่องก่อนบันทึกข้อมูล');
            return;
        }

        const modal = document.getElementById('submitConfirmationModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    function confirmReject() {
        const modal = document.getElementById('rejectConfirmationModal');
        if (modal) {
            modal.classList.remove('hidden');
        }
    }

    document.addEventListener('click', function (event) {
        const confirmButton = event.target.closest('[data-form-submit-confirm]');

        if (!confirmButton) {
            return;
        }

        confirmSubmit();
    });

    document.addEventListener('DOMContentLoaded', function () {
        const submitModal = document.getElementById('submitConfirmationModal');
        const cancelSubmitBtn = document.getElementById('cancelSubmitModalBtn');
        const confirmSubmitBtn = document.getElementById('confirmSubmitModalBtn');

        const rejectModal = document.getElementById('rejectConfirmationModal');
        const cancelRejectBtn = document.getElementById('cancelRejectModalBtn');
        const confirmRejectBtn = document.getElementById('confirmRejectModalBtn');

        if (submitModal && cancelSubmitBtn && confirmSubmitBtn) {
            cancelSubmitBtn.addEventListener('click', function () {
                submitModal.classList.add('hidden');
            });

            confirmSubmitBtn.addEventListener('click', function () {
                const form = document.querySelector('#approve_eva');
                if (form) {
                    showLoading();

                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'change_status';
                    input.value = '1';
                    form.appendChild(input);

                    submitModal.classList.add('hidden');

                    form.addEventListener('submit', function () {
                        setTimeout(function () {
                            hideLoading();
                        }, 5000);
                    });

                    form.submit();
                }
            });

            submitModal.addEventListener('click', function (event) {
                if (event.target === submitModal) {
                    submitModal.classList.add('hidden');
                }
            });
        }

        if (rejectModal && cancelRejectBtn && confirmRejectBtn) {
            cancelRejectBtn.addEventListener('click', function () {
                rejectModal.classList.add('hidden');
            });

            confirmRejectBtn.addEventListener('click', function () {
                const rejectForm = document.getElementById('reject-form');
                if (rejectForm) {
                    showLoading();
                    rejectModal.classList.add('hidden');

                    rejectForm.addEventListener('submit', function () {
                        setTimeout(function () {
                            hideLoading();
                        }, 5000);
                    });

                    rejectForm.submit();
                }
            });

            rejectModal.addEventListener('click', function (event) {
                if (event.target === rejectModal) {
                    rejectModal.classList.add('hidden');
                }
            });
        }

        window.addEventListener('load', function () {
            hideLoading();
        });

        window.addEventListener('pageshow', function () {
            hideLoading();
        });
    });
</script>
