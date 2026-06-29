{{-- สคริปต์จัดการ checkbox และคะแนนของตารางด้านคุณภาพ --}}
<script>
function handleQualityCheckboxChange(checkbox) {
    const subCriteriaId = checkbox.dataset.subCriteriaId;
    const score = parseFloat(checkbox.dataset.score) || 0;
    const mainCriteriaId = checkbox.dataset.mainCriteriaId;
    const allowMultiple = checkbox.dataset.allowMultiple === '1';
    const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);

    if (checkbox.checked && mainCriteriaId && !allowMultiple) {
        document.querySelectorAll(`input[name*="quality_criteria"][data-main-criteria-id="${mainCriteriaId}"]`).forEach(otherCheckbox => {
            if (otherCheckbox !== checkbox) {
                otherCheckbox.checked = false;
                const otherSubCriteriaId = otherCheckbox.dataset.subCriteriaId;
                const otherScoreInput = document.getElementById(`quality-score-${otherSubCriteriaId}`);
                if (otherScoreInput) {
                    otherScoreInput.value = '';
                    otherScoreInput.classList.remove('bg-white');
                    otherScoreInput.classList.add('bg-gray-50', 'cursor-not-allowed');
                }
            }
        });
    }

    if (scoreInput) {
        if (checkbox.checked) {
            scoreInput.value = score;
            scoreInput.classList.remove('bg-gray-50', 'cursor-not-allowed');
            scoreInput.classList.add('bg-white');
        } else {
            scoreInput.value = '';
            scoreInput.classList.remove('bg-white');
            scoreInput.classList.add('bg-gray-50', 'cursor-not-allowed');
        }

        updateQualityTotalScore();
    }
}

function updateQualityTotalScore() {
    const scoreInputs = document.querySelectorAll('input[name*="quality_list"][name*="[score]"]');
    let totalScore = 0;

    scoreInputs.forEach(input => {
        const value = parseFloat(input.value) || 0;
        totalScore += value;
    });
}

function resetQualityForm() {
    const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
    checkboxes.forEach(checkbox => {
        checkbox.checked = false;
        handleQualityCheckboxChange(checkbox);
    });

    const evidenceInputs = document.querySelectorAll('input[name*="evidence_list"]');
    evidenceInputs.forEach(input => {
        if (input.type !== 'hidden') {
            input.value = '';
        }
    });
}

function initializeCheckboxStates() {
    const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
    checkboxes.forEach(checkbox => {
        const subCriteriaId = checkbox.dataset.subCriteriaId;
        const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);

        if (scoreInput && checkbox.checked) {
            scoreInput.classList.remove('bg-gray-50', 'cursor-not-allowed');
            scoreInput.classList.add('bg-white');
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    if (!window.__qualityCheckboxBound) {
        window.__qualityCheckboxBound = true;

        document.addEventListener('change', function(event) {
            const checkbox = event.target.closest('[data-quality-checkbox]');
            if (!checkbox) {
                return;
            }

            handleQualityCheckboxChange(checkbox);
        });
    }

    initializeCheckboxStates();
    updateQualityTotalScore();
});
</script>
