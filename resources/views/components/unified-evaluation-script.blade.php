{{-- สคริปต์คำนวณคะแนนรวมและจัดการลิงก์หลักฐานของฟอร์มประเมินรวม --}}
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

        document.querySelectorAll('[data-quality-checkbox]').forEach(checkbox => {
            if (checkbox.checked) {
                handleQualityCheckboxChange(checkbox);
            }
        });
    });

    function recalculateSummaryScores() {
        const readonlyInput = document.getElementById('quality-readonly');
        const isReadonly = readonlyInput && readonlyInput.value === '1';
        if (isReadonly) {
            return;
        }

        let quantitySum = 0;
        document.querySelectorAll('input[name^="quantity_list"][name$="[score_C]"]').forEach(input => {
            const match = input.name.match(/^quantity_list\[(.+?)\]\[score_C\]$/);
            if (!match) return;

            const subCriteriaId = match[1];
            const rawValue = input.value.trim();
            if (rawValue === '') return;

            const scoreC = Math.max(0, parseFloat(rawValue));
            if (isNaN(scoreC)) return;

            const summaryRow = document.querySelector(`[data-summary-quantity-row][data-sub-id="${subCriteriaId}"]`);
            const scoreA = parseFloat(summaryRow?.dataset.scoreA || '0');
            const scoreB = parseFloat(summaryRow?.dataset.scoreB || '0');

            if (!isNaN(scoreA) && !isNaN(scoreB) && scoreB !== 0) {
                quantitySum += (scoreA * scoreC) / scoreB;
            }
        });

        const listTotals = {};
        document.querySelectorAll('input[name^="quality_list"][name$="[score]"]').forEach(input => {
            const val = parseFloat(input.value);
            if (isNaN(val)) return;

            const listId = input.dataset.evaluationListId || 'unknown';
            const listMax = parseFloat(input.dataset.listMax);

            if (!listTotals[listId]) {
                listTotals[listId] = {
                    sum: 0,
                    max: isNaN(listMax) ? 0 : listMax
                };
            }

            listTotals[listId].sum += val;
        });

        let qualitySum = 0;
        Object.values(listTotals).forEach(({ sum, max }) => {
            let cappedSum = sum;
            if (max > 0 && cappedSum > max) {
                cappedSum = max;
            }
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
        document.querySelectorAll('input[name^="quality_list"][name$="[score]"]').forEach(input => {
            input.addEventListener('input', recalculateSummaryScores);
        });

        recalculateSummaryScores();
    });

    document.addEventListener('DOMContentLoaded', function() {
        function updateRemoveButtonVisibility(container) {
            if (!container) {
                return;
            }

            const rows = container.querySelectorAll('.evidence-link-row');
            const hasContent = Array.from(rows).some(row => {
                const input = row.querySelector('input[type="url"]');
                return input && input.value.trim() !== '';
            });

            rows.forEach(row => {
                const removeBtn = row.querySelector('.remove-evidence-link');
                if (!removeBtn) {
                    return;
                }

                if (rows.length > 1 || hasContent) {
                    removeBtn.style.display = 'block';
                } else {
                    removeBtn.style.display = 'none';
                }
            });
        }

        document.querySelectorAll('.add-evidence-link').forEach(function(btn) {
            btn.addEventListener('click', function() {
                const mainCriteriaId = btn.getAttribute('data-quality-main');
                const container = document.getElementById(`evidence-links-quality-${mainCriteriaId}`);
                if (!container) {
                    return;
                }

                const div = document.createElement('div');
                div.className = 'flex items-center mb-2 evidence-link-row';
                div.innerHTML = `
                <input type="url"
                    name="evidence_list[${mainCriteriaId}][links][]"
                    class="form-input text-base w-full h-12 px-4 rounded-lg border border-gray-300 bg-gray-50 focus:ring-blue-500 focus:border-blue-500 focus:bg-white transition-colors"
                    placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้">
                <button type="button"
                    class="ml-2 px-3 py-2 bg-red-100 text-red-700 rounded hover:bg-red-200 transition-colors remove-evidence-link"
                    title="ลบลิงก์">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                    </svg>
                </button>
            `;

                container.appendChild(div);
                updateRemoveButtonVisibility(container);

                const newInput = div.querySelector('input[type="url"]');
                newInput.focus();
            });
        });

        document.addEventListener('click', function(e) {
            if (e.target.closest('.remove-evidence-link')) {
                const container = e.target.closest('[id^="evidence-links-quality-"]');
                const row = e.target.closest('.evidence-link-row');
                if (!container || !row) {
                    return;
                }

                const rows = container.querySelectorAll('.evidence-link-row');

                if (rows.length === 1) {
                    const input = row.querySelector('input[type="url"]');
                    if (input) {
                        input.value = '';
                    }
                } else {
                    row.remove();
                }

                updateRemoveButtonVisibility(container);
            }
        });

        document.addEventListener('input', function(e) {
            if (e.target.type === 'url' && e.target.name && e.target.name.includes('evidence_list')) {
                const container = e.target.closest('[id^="evidence-links-quality-"]');
                if (container) {
                    updateRemoveButtonVisibility(container);
                }
            }
        });

        document.querySelectorAll('[id^="evidence-links-quality-"]').forEach(updateRemoveButtonVisibility);
    });
</script>
