{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/script-create-modal-helpers.blade.php --}}
        function showConfirmModal(reportTitle) {
            document.getElementById('version_name_display').textContent = reportTitle || 'ไม่ระบุ';
            document.getElementById('confirm_modal').classList.remove('hidden');
        }

        function hideConfirmModal() {
            document.getElementById('confirm_modal').classList.add('hidden');
        }

        function showSuccessModal() {
            isSubmitting = false;
            resetDirtyState();
            document.getElementById('success_modal').classList.remove('hidden');
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const interval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = "{{ route('criteria_config.index') }}";
                }
            }, 1000);
        }
