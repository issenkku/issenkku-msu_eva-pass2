{{-- สคริปต์คำนวณคะแนนรวมของฟอร์ม evaluator --}}
<script>
function calculateScoreD(input) {
    const subCriteriaId = input.dataset.subCriteriaId;
    const parent = input.closest('.grid');

    const scoreA = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_A]"]`).value) || 0;
    const scoreB = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_B]"]`).value) || 1;
    let scoreC = parseFloat(input.value) || 0;
    if (scoreC < 0) {
        scoreC = 0;
        input.value = 0;
    }

    const scoreD = (scoreA * scoreC) / scoreB;
    document.getElementById(`score-D-${subCriteriaId}`).value = scoreD ? scoreD.toFixed(2) : '';
}

function recalculateSummaryScores() {
    const readonlyInput = document.getElementById('quality-readonly');
    if (readonlyInput && readonlyInput.value === '1') {
        return;
    }

    const quantityScoreInputs = document.querySelectorAll('[data-quantity-score-input]');
    const quantityCompletedCount = Array.from(quantityScoreInputs)
        .filter(input => input.value.trim() !== '')
        .length;
    let quantitySum = 0;
    const quantityInputs = document.querySelectorAll('input[name^="quantity_list"][name$="[score_D]"]');
    if (quantityInputs.length > 0) {
        const quantityListTotals = {};
        quantityInputs.forEach(input => {
            const val = parseFloat(input.value);
            if (isNaN(val)) return;
            const scoreMax = parseFloat(input.dataset.scoreMax);
            const cappedValue = isNaN(scoreMax)
                ? Math.max(0, val)
                : Math.min(Math.max(0, val), Math.max(0, scoreMax));

            const listId = input.dataset.evaluationListId || 'unknown';
            const listMax = parseFloat(input.dataset.listMax);
            if (!quantityListTotals[listId]) {
                quantityListTotals[listId] = { sum: 0, max: isNaN(listMax) ? 0 : listMax };
            }
            quantityListTotals[listId].sum += cappedValue;
        });

        Object.values(quantityListTotals).forEach(({ sum, max }) => {
            let cappedSum = sum;
            if (max > 0 && cappedSum > max) cappedSum = max;
            quantitySum += cappedSum;
        });
    } else {
        const baseInput = document.getElementById('quantity-base-score');
        quantitySum = baseInput ? parseFloat(baseInput.value) || 0 : 0;
    }

    const listTotals = {};
    document.querySelectorAll('input[name^="quality_list"][name$="[score]"]').forEach(input => {
        const val = parseFloat(input.value);
        if (isNaN(val)) return;

        const listId = input.dataset.evaluationListId || 'unknown';
        const listMax = parseFloat(input.dataset.listMax);

        if (!listTotals[listId]) {
            listTotals[listId] = { sum: 0, max: isNaN(listMax) ? 0 : listMax };
        }
        listTotals[listId].sum += val;
    });

    let qualitySum = 0;
    Object.values(listTotals).forEach(({ sum, max }) => {
        let cappedSum = sum;
        if (max > 0 && cappedSum > max) cappedSum = max;
        qualitySum += cappedSum;
    });

    const qualityMaxInput = document.getElementById('quality-max-score');
    const qualityMax = qualityMaxInput ? parseFloat(qualityMaxInput.value) : 0;
    if (!isNaN(qualityMax) && qualityMax > 0 && qualitySum > qualityMax) {
        qualitySum = qualityMax;
    }

    const quantitySummary = document.getElementById('quantity-summary');
    const qualitySummary = document.getElementById('quality-summary');
    const supportSummary = document.getElementById('support-summary');
    const totalSummary = document.getElementById('total-summary');
    const quantityProgress = document.getElementById('quantity-progress');
    const supportSum = parseFloat(supportSummary?.textContent?.replaceAll(',', '') || '0') || 0;

    if (quantitySummary) quantitySummary.textContent = quantitySum.toFixed(2);
    if (quantityProgress) quantityProgress.textContent = `กรอกแล้ว ${quantityCompletedCount}/${quantityScoreInputs.length} ข้อ`;
    if (qualitySummary) qualitySummary.textContent = qualitySum.toFixed(2);
    if (totalSummary) totalSummary.textContent = (quantitySum + qualitySum + supportSum).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll(
        'input[name^="quantity_list"][name$="[score_D]"], input[name^="quality_list"][name$="[score]"]'
    ).forEach(input => {
        input.addEventListener('input', recalculateSummaryScores);
    });

    if (!window.__quantityScoreInputBound) {
        window.__quantityScoreInputBound = true;

        document.addEventListener('input', function(event) {
            const input = event.target.closest('[data-quantity-score-input]');
            if (!input) {
                return;
            }

            calculateScoreD(input);
            recalculateSummaryScores();
        });
    }

    document.querySelectorAll('[data-quantity-score-input]').forEach(input => {
        if (input.value !== '') {
            calculateScoreD(input);
        }
    });

    recalculateSummaryScores();
});

function handleQualityCheckboxChange(checkbox) {
    const subCriteriaId = checkbox.dataset.subCriteriaId;
    const score = parseFloat(checkbox.dataset.score) || 0;
    const scoreInput = document.getElementById(`quality-score-${subCriteriaId}`);
    const mainCriteriaId = checkbox.dataset.mainCriteriaId;
    const allowMultiple = checkbox.dataset.allowMultiple === '1';

    if (checkbox.checked && mainCriteriaId && !allowMultiple) {
        document.querySelectorAll(`input[name*="quality_criteria"][data-main-criteria-id="${mainCriteriaId}"]`).forEach(otherCheckbox => {
            if (otherCheckbox !== checkbox) {
                otherCheckbox.checked = false;
                const otherSubCriteriaId = otherCheckbox.dataset.subCriteriaId;
                const otherScoreInput = document.getElementById(`quality-score-${otherSubCriteriaId}`);
                if (otherScoreInput) {
                    otherScoreInput.value = '';
                }
            }
        });
    }

    if (scoreInput) {
        scoreInput.value = checkbox.checked ? score : '';
    }

    recalculateSummaryScores();
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

});
</script>
