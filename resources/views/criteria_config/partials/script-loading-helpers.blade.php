{{-- ไฟล์มุมมอง: resources/views/criteria_config/partials/script-loading-helpers.blade.php --}}
        function showLoading() {
            document.getElementById('loading_overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading_overlay').classList.add('hidden');
        }
