{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/script-edit-feedback-helpers.blade.php --}}
        function showSuccess() {
            document.getElementById('success_modal').classList.remove('hidden');
        }

        function showError(message) {
            alert(message);
        }
