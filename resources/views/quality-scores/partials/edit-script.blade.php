{{-- ไฟล์มุมมอง: resources/views/quality-scores/partials/edit-script.blade.php --}}
@push('scripts')
    <script>
        function validateQualityScoreEditForm() {
            const criteriaSelect = document.getElementById('quality_sub_criteria_id');
            const userSelect = document.getElementById('user_id');
            const scoreInput = document.getElementById('score');
            const submitBtn = document.getElementById('submitBtn');
            const score = parseFloat(scoreInput.value);

            submitBtn.disabled = !(
                criteriaSelect.value &&
                userSelect.value &&
                scoreInput.value !== '' &&
                !Number.isNaN(score) &&
                score >= 0 &&
                score <= 100
            );
        }

        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('quality_sub_criteria_id').addEventListener('change', validateQualityScoreEditForm);
            document.getElementById('user_id').addEventListener('change', validateQualityScoreEditForm);
            document.getElementById('score').addEventListener('input', validateQualityScoreEditForm);

            document.getElementById('qualityScoreForm').addEventListener('submit', function (event) {
                const score = parseFloat(document.getElementById('score').value);
                if (Number.isNaN(score) || score < 0 || score > 100) {
                    event.preventDefault();
                    alert('กรุณาระบุคะแนนที่ถูกต้อง (0-100)');
                    return false;
                }
            });

            validateQualityScoreEditForm();
        });
    </script>
@endpush
