<script>
    (() => {
        const parseDisplayedNumber = (value) => Number.parseFloat(String(value || '0').replaceAll(',', '')) || 0;

        const normalizeScore = (value) => {
            const trimmed = String(value ?? '').trim();
            if (trimmed === '') return '';
            const number = Number(trimmed);
            return Number.isFinite(number) ? number.toFixed(2) : trimmed;
        };

        window.recalculateSupportScores = function recalculateSupportScores() {
            let rawTotal = 0;

            document.querySelectorAll('[data-support-item]').forEach((item) => {
                const id = item.dataset.supportId;
                const input = item.querySelector('[data-support-score]');
                let achieved = null;
                let weighted = null;

                if (input) {
                    const rawValue = input.value.trim();
                    if (rawValue !== '' && Number.isFinite(Number(rawValue)) && Number(rawValue) >= 0) {
                        achieved = Number(rawValue);
                        weighted = (Number(input.dataset.supportWeight || 0) * achieved) / 100;
                    }
                } else {
                    const existingWeighted = item.dataset.supportExistingWeighted;
                    if (existingWeighted !== '' && Number.isFinite(Number(existingWeighted))) {
                        weighted = Number(existingWeighted);
                    }
                }

                document.querySelectorAll(`[data-support-achieved-display="${id}"]`).forEach((display) => {
                    display.textContent = achieved === null
                        ? (input ? '-' : display.textContent)
                        : achieved.toFixed(2);
                });
                document.querySelectorAll(`[data-support-weighted-display="${id}"]`).forEach((display) => {
                    display.textContent = weighted === null ? '-' : weighted.toFixed(2);
                });

                if (weighted !== null) rawTotal += weighted;
            });

            const cappedSupport = Math.min(rawTotal, 100);
            const supportSummary = document.getElementById('support-summary');
            if (supportSummary) supportSummary.textContent = cappedSupport.toFixed(2);

            const quantity = parseDisplayedNumber(document.getElementById('quantity-summary')?.textContent);
            const quality = parseDisplayedNumber(document.getElementById('quality-summary')?.textContent);
            const totalSummary = document.getElementById('total-summary');
            if (totalSummary) totalSummary.textContent = (quantity + quality + cappedSupport).toFixed(2);
        };

        window.validateSupportCriteria = function validateSupportCriteria() {
            const errors = [];

            document.querySelectorAll('[data-support-item]').forEach((item) => {
                const input = item.querySelector('[data-support-score]');
                if (!input) return;

                const activity = item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน';
                const value = input.value.trim();
                if (value !== '' && (!/^\d+(\.\d{1,2})?$/.test(value) || Number(value) < 0)) {
                    errors.push(`ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นเลขตั้งแต่ 0 และมีทศนิยมไม่เกิน 2 ตำแหน่ง`);
                }

                const evidenceInputs = Array.from(item.querySelectorAll('[data-support-evidence-input]'));
                const evidenceLinks = evidenceInputs.map((evidenceInput) => evidenceInput.value.trim()).filter(Boolean);
                const invalidLink = evidenceLinks.some((link) => {
                    try {
                        const url = new URL(link);
                        return !['http:', 'https:'].includes(url.protocol);
                    } catch (error) {
                        return true;
                    }
                });
                if (invalidLink) {
                    errors.push(`ลิงก์หลักฐานของ "${activity}" ต้องเป็น URL ที่ขึ้นต้นด้วย http:// หรือ https://`);
                }

                const required = item.dataset.supportRequired === '1';
                if (required && evidenceLinks.length === 0) {
                    errors.push(`กรุณาแนบหลักฐานสำหรับ "${activity}"`);
                }

                const requireReason = item.dataset.supportRequireReason === '1';
                const normalizedCurrent = normalizeScore(value);
                const normalizedOriginal = normalizeScore(input.dataset.supportOriginalScore);
                const reason = item.querySelector('[data-support-reason]')?.value.trim() || '';
                if (requireReason && normalizedCurrent !== normalizedOriginal && reason === '') {
                    errors.push(`กรุณาระบุเหตุผลการแก้คะแนนของ "${activity}"`);
                }
            });

            return errors;
        };

        if (window.__supportCriteriaBound) {
            window.recalculateSupportScores();
            return;
        }
        window.__supportCriteriaBound = true;

        const createEvidenceRow = (criterionId) => {
            const row = document.createElement('div');
            row.className = 'support-evidence-row flex items-center gap-2';

            const input = document.createElement('input');
            input.type = 'url';
            input.name = `support_list[${criterionId}][evidence_links][]`;
            input.dataset.supportEvidenceInput = '';
            input.placeholder = 'https://example.com/evidence';
            input.className = 'block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900 shadow-sm outline-none transition focus:border-amber-500 focus:ring-2 focus:ring-amber-200';

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.dataset.removeSupportEvidence = '';
            removeButton.className = 'rounded-lg bg-red-50 px-3 py-2.5 text-sm font-semibold text-red-700 hover:bg-red-100';
            removeButton.setAttribute('aria-label', 'ลบลิงก์หลักฐาน');
            removeButton.textContent = 'ลบ';

            row.append(input, removeButton);
            return row;
        };

        document.addEventListener('input', (event) => {
            if (event.target.closest('[data-support-score]')) {
                window.recalculateSupportScores();
            }
        });

        document.addEventListener('click', (event) => {
            const addButton = event.target.closest('[data-add-support-evidence]');
            if (addButton) {
                const criterionId = addButton.dataset.addSupportEvidence;
                const container = document.getElementById(`support-evidence-links-${criterionId}`);
                if (!container) return;
                const row = createEvidenceRow(criterionId);
                container.appendChild(row);
                row.querySelector('input')?.focus();
                return;
            }

            const removeButton = event.target.closest('[data-remove-support-evidence]');
            if (!removeButton) return;

            const row = removeButton.closest('.support-evidence-row');
            const container = row?.closest('[data-support-evidence-container]');
            if (!row || !container) return;

            const rows = container.querySelectorAll('.support-evidence-row');
            if (rows.length === 1) {
                const input = row.querySelector('[data-support-evidence-input]');
                if (input) input.value = '';
            } else {
                row.remove();
            }
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.recalculateSupportScores, { once: true });
        } else {
            window.recalculateSupportScores();
        }
    })();
</script>
