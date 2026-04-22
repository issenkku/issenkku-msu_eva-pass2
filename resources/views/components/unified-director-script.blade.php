{{-- สคริปต์คำนวณคะแนนรวมของฟอร์ม director --}}
<script>
function calculateScoreD(input) {
    const subCriteriaId = input.dataset.subCriteriaId;
    const parent = input.closest('.grid');

    const scoreA = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_A]"]`).value) || 0;
    const scoreB = parseFloat(parent.querySelector(`input[name="quantity_list[${subCriteriaId}][score_B]"]`).value) || 1;
    const scoreC = parseFloat(input.value) || 0;

    const scoreD = (scoreA * scoreC) / scoreB;

    document.getElementById(`score-D-${subCriteriaId}`).value = scoreD ? scoreD.toFixed(2) : '';
}

function recalculateSummaryScores() {
    const readonlyInput = document.getElementById('quality-readonly');
    const isReadonly = readonlyInput && readonlyInput.value === '1';
    if (isReadonly) {
        return;
    }

    let quantitySum = 0;
    document.querySelectorAll('input[name^="quantity_list"][name$="[score_D]"]').forEach(input => {
        const val = parseFloat(input.value);
        if (!isNaN(val)) quantitySum += val;
    });

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

    const quantityEl = document.getElementById('quantity-summary');
    const qualityEl = document.getElementById('quality-summary');
    const totalEl = document.getElementById('total-summary');
    if (quantityEl) quantityEl.textContent = quantitySum.toFixed(2);
    if (qualityEl) qualityEl.textContent = qualitySum.toFixed(2);
    if (totalEl) totalEl.textContent = (quantitySum + qualitySum).toFixed(2);
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll(
        'input[name^="quantity_list"][name$="[score_C]"], input[name^="quantity_list"][name$="[score_D]"], input[name^="quality_list"][name$="[score]"]'
    ).forEach(input => {
        input.addEventListener('input', recalculateSummaryScores);
    });

    recalculateSummaryScores();
});

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
    const checkboxes = document.querySelectorAll('input[name*="quality_criteria"]');
    checkboxes.forEach(checkbox => {
        if (checkbox.checked) {
            handleQualityCheckboxChange(checkbox);
        }
    });
});
</script>
