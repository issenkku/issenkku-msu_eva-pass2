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

    if (!openModalBtn || !confirmationModal) {
        return;
    }

    function isValidUrl(value) {
        try {
            const url = new URL(value);
            return url.protocol === 'http:' || url.protocol === 'https:';
        } catch (e) {
            return false;
        }
    }

    function validateForm(isSubmit = false) {
        const errors = [];

        const quantityInputs = document.querySelectorAll('input[name^="quantity_list"][name$="[score_C]"]');
        quantityInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '') {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    errors.push('คะแนนด้านปริมาณต้องเป็นตัวเลขที่ไม่ติดลบ');
                }
            }
        });

        const qualityInputs = document.querySelectorAll('input[name^="quality_list"][name$="[score]"]');
        qualityInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '') {
                const numValue = parseFloat(value);
                if (isNaN(numValue) || numValue < 0) {
                    errors.push('คะแนนด้านคุณภาพต้องเป็นตัวเลขที่ไม่ติดลบ');
                }
            }
        });

        const evidenceInputs = document.querySelectorAll('input[name^="evidence_list"][name$="[links][]"]');
        evidenceInputs.forEach(input => {
            const value = input.value.trim();
            if (value !== '' && !isValidUrl(value)) {
                errors.push('ลิงก์หลักฐานไม่ถูกต้อง กรุณาตรวจสอบ URL');
            }
        });

        if (isSubmit) {
            document.querySelectorAll('[id^="evidence-links-quality-"][data-require-evidence="1"]').forEach(container => {
                const mainCriteriaName = container.dataset.mainCriteriaName || 'เกณฑ์ที่เลือก';
                const qualityMainCard = container.closest('details');
                const hasSelectedScore = qualityMainCard
                    ? Array.from(qualityMainCard.querySelectorAll('input[name^="quality_list"][name$="[score]"]')).some(input => input.value.trim() !== '')
                    : false;

                if (!hasSelectedScore) {
                    return;
                }

                const hasEvidence = Array.from(container.querySelectorAll('input[type="url"]'))
                    .some(input => input.value.trim() !== '');

                if (!hasEvidence) {
                    errors.push(`กรุณาแนบหลักฐานสำหรับเกณฑ์ "${mainCriteriaName}"`);
                }
            });
        }

        return errors;
    }

    function showValidationErrors(errors) {
        const existingAlert = document.querySelector('.validation-error-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        if (errors.length === 0) {
            return;
        }

        const errorDiv = document.createElement('div');
        errorDiv.className = 'validation-error-alert bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4';
        errorDiv.setAttribute('role', 'alert');

        const errorList = document.createElement('ul');
        errorList.className = 'list-disc list-inside';

        const uniqueErrors = [...new Set(errors)];
        uniqueErrors.forEach(error => {
            const li = document.createElement('li');
            li.textContent = error;
            errorList.appendChild(li);
        });

        errorDiv.appendChild(errorList);
        evaluationForm.insertBefore(errorDiv, evaluationForm.firstChild);
        errorDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }

    function setBadgeState(element, hasData, readyText = 'มีข้อมูลแล้ว', emptyText = 'ยังไม่มีข้อมูล') {
        if (!element) {
            return;
        }

        element.textContent = hasData ? readyText : emptyText;
        element.className = hasData
            ? 'inline-flex items-center rounded-full bg-emerald-100 px-2.5 py-1 text-xs font-semibold text-emerald-700'
            : 'inline-flex items-center rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-700';
    }

    function updateSubmissionSummary() {
        const quantitySummary = parseFloat(document.getElementById('quantity-summary')?.textContent || '0') || 0;
        const qualitySummary = parseFloat(document.getElementById('quality-summary')?.textContent || '0') || 0;
        const totalSummary = parseFloat(document.getElementById('total-summary')?.textContent || '0') || 0;

        const modalQuantity = document.getElementById('modal-quantity-summary');
        const modalQuality = document.getElementById('modal-quality-summary');
        const modalTotal = document.getElementById('modal-total-summary');

        if (modalQuantity) modalQuantity.textContent = quantitySummary.toFixed(2);
        if (modalQuality) modalQuality.textContent = qualitySummary.toFixed(2);
        if (modalTotal) modalTotal.textContent = totalSummary.toFixed(2);

        document.querySelectorAll('[data-summary-quantity-row]').forEach(row => {
            const subId = row.dataset.subId;
            const scoreA = parseFloat(row.dataset.scoreA || '0') || 0;
            const scoreB = parseFloat(row.dataset.scoreB || '0') || 0;
            const scoreCInput = document.querySelector(`input[name="quantity_list[${subId}][score_C]"]`);
            const descriptionInput = document.querySelector(`input[name="quantity_list[${subId}][description]"]`);
            const scoreC = parseFloat(scoreCInput?.value || '') || 0;
            const description = (descriptionInput?.value || '').trim();
            const hasData = (scoreCInput?.value || '').trim() !== '' || description !== '';
            const scoreD = hasData && scoreB !== 0 ? (scoreA * scoreC) / scoreB : 0;

            const statusEl = document.getElementById(`summary-quantity-status-${subId}`);
            const scoreEl = document.getElementById(`summary-quantity-score-${subId}`);

            if (statusEl) {
                statusEl.textContent = hasData ? 'มีข้อมูลแล้ว' : 'ยังไม่มีข้อมูล';
                statusEl.className = hasData ? 'text-xs text-emerald-700 mt-1' : 'text-xs text-gray-500 mt-1';
            }

            if (scoreEl) {
                scoreEl.textContent = scoreD.toFixed(2);
            }
        });

        document.querySelectorAll('[data-summary-quality-main]').forEach(row => {
            const mainId = row.dataset.mainId;
            const subIds = (row.dataset.subIds || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);

            let total = 0;
            let selectedCount = 0;
            subIds.forEach(subId => {
                const scoreInput = document.getElementById(`quality-score-${subId}`);
                const value = parseFloat(scoreInput?.value || '');
                if (!isNaN(value)) {
                    total += value;
                    selectedCount++;
                }
            });

            const hasData = selectedCount > 0;
            const statusEl = document.getElementById(`summary-quality-status-${mainId}`);
            const scoreEl = document.getElementById(`summary-quality-score-${mainId}`);

            if (statusEl) {
                statusEl.textContent = hasData ? `มีข้อมูลแล้ว ${selectedCount} รายการ` : 'ยังไม่มีข้อมูล';
                statusEl.className = hasData ? 'text-xs text-emerald-700 mt-1' : 'text-xs text-gray-500 mt-1';
            }

            if (scoreEl) {
                scoreEl.textContent = total.toFixed(2);
            }
        });

        document.querySelectorAll('[data-summary-list]').forEach(listEl => {
            const listId = listEl.dataset.listId;
            const listMax = parseFloat(listEl.dataset.listMax || '0') || 0;
            const qualitySubIds = (listEl.dataset.qualitySubIds || '')
                .split(',')
                .map(id => id.trim())
                .filter(Boolean);

            let quantityTotal = 0;
            let hasAnyData = false;

            listEl.querySelectorAll('[data-summary-quantity-row]').forEach(row => {
                const subId = row.dataset.subId;
                const scoreA = parseFloat(row.dataset.scoreA || '0') || 0;
                const scoreB = parseFloat(row.dataset.scoreB || '0') || 0;
                const scoreCInput = document.querySelector(`input[name="quantity_list[${subId}][score_C]"]`);
                const descriptionInput = document.querySelector(`input[name="quantity_list[${subId}][description]"]`);
                const scoreC = parseFloat(scoreCInput?.value || '') || 0;
                const description = (descriptionInput?.value || '').trim();
                const hasData = (scoreCInput?.value || '').trim() !== '' || description !== '';
                if (hasData) {
                    hasAnyData = true;
                    quantityTotal += scoreB !== 0 ? (scoreA * scoreC) / scoreB : 0;
                }
            });

            let qualityTotal = 0;
            qualitySubIds.forEach(subId => {
                const scoreInput = document.getElementById(`quality-score-${subId}`);
                const value = parseFloat(scoreInput?.value || '');
                if (!isNaN(value)) {
                    hasAnyData = true;
                    qualityTotal += value;
                }
            });

            if (listMax > 0 && qualityTotal > listMax) {
                qualityTotal = listMax;
            }

            const total = quantityTotal + qualityTotal;
            const statusEl = document.getElementById(`summary-list-status-${listId}`);
            const scoreEl = document.getElementById(`summary-list-score-${listId}`);

            setBadgeState(statusEl, hasAnyData);
            if (scoreEl) {
                scoreEl.textContent = total.toFixed(2);
            }
        });
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

    function hideLoading() {
        loadingOverlay.classList.add('hidden');
    }

    evaluationForm.addEventListener('submit', function(e) {
        const status = document.getElementById('formStatus').value;
        if (status === 'Draft') {
            const errors = validateForm(false);
            if (errors.length > 0) {
                e.preventDefault();
                showValidationErrors(errors);
                hideLoading();
            }
        }
    });

    openModalBtn.addEventListener('click', (e) => {
        e.preventDefault();

        const errors = validateForm(true);
        if (errors.length > 0) {
            showValidationErrors(errors);
            return;
        }

        if (typeof recalculateSummaryScores === 'function') {
            recalculateSummaryScores();
        }

        updateSubmissionSummary();
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
            setFormStatus('Pending');
            showLoading();
            evaluationForm.submit();
        }, 350);
    });

    document.querySelectorAll('input[type="number"]').forEach(input => {
        input.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (!isNaN(value) && value < 0) {
                this.setCustomValidity('คะแนนต้องไม่ติดลบ');
            } else {
                this.setCustomValidity('');
            }
        });
    });
});
</script>
