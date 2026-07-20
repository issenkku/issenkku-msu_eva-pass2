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

        const validateSupportItem = (item) => {
            const errors = [];
            let firstInvalid = null;
            const modalErrorId = 'support-modal-errors';
            item.querySelectorAll('[aria-invalid="true"]').forEach((element) => {
                element.removeAttribute('aria-invalid');
                const describedBy = (element.getAttribute('aria-describedby') || '')
                    .split(/\s+/)
                    .filter((id) => id && id !== modalErrorId);
                if (describedBy.length > 0) {
                    element.setAttribute('aria-describedby', describedBy.join(' '));
                } else {
                    element.removeAttribute('aria-describedby');
                }
            });
            const rememberInvalid = (element) => {
                if (!firstInvalid && element) firstInvalid = element;
                if (!element?.matches('input, textarea, select')) return;
                element.setAttribute('aria-invalid', 'true');
                const describedBy = new Set((element.getAttribute('aria-describedby') || '').split(/\s+/).filter(Boolean));
                describedBy.add(modalErrorId);
                element.setAttribute('aria-describedby', [...describedBy].join(' '));
            };
            const input = item.querySelector('[data-support-score]');
            if (!input) return { errors, firstInvalid };

            const activity = item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน';
            const value = input.value.trim();
            if (value !== '' && (!/^\d+(\.\d{1,2})?$/.test(value) || Number(value) < 0)) {
                errors.push(`ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นเลขตั้งแต่ 0 และมีทศนิยมไม่เกิน 2 ตำแหน่ง`);
                rememberInvalid(input);
            }

            const evidenceInputs = Array.from(item.querySelectorAll('[data-support-evidence-input]'));
            const evidenceLinks = evidenceInputs.map((field) => field.value.trim()).filter(Boolean);
            const invalidEvidenceInput = evidenceInputs.find((field) => {
                const link = field.value.trim();
                if (link === '') return false;
                try {
                    return !['http:', 'https:'].includes(new URL(link).protocol);
                } catch (error) {
                    return true;
                }
            });
            if (invalidEvidenceInput) {
                errors.push(`ลิงก์หลักฐานของ "${activity}" ต้องเป็น URL ที่ขึ้นต้นด้วย http:// หรือ https://`);
                rememberInvalid(invalidEvidenceInput);
            }

            if (item.dataset.supportRequired === '1' && evidenceLinks.length === 0) {
                errors.push(`กรุณาแนบหลักฐานสำหรับ "${activity}"`);
                rememberInvalid(item.querySelector('[data-add-support-evidence]') || input);
            }

            const reason = item.querySelector('[data-support-reason]');
            const scoreChanged = normalizeScore(value) !== normalizeScore(input.dataset.supportOriginalScore);
            if (item.dataset.supportRequireReason === '1' && scoreChanged && !reason?.value.trim()) {
                errors.push(`กรุณาระบุเหตุผลการแก้คะแนนของ "${activity}"`);
                rememberInvalid(reason || input);
            }

            return { errors, firstInvalid };
        };

        window.validateSupportCriteria = function validateSupportCriteria() {
            const errors = [];
            let firstInvalidItem = null;
            let firstInvalidControl = null;

            document.querySelectorAll('[data-support-item]').forEach((item) => {
                const result = validateSupportItem(item);
                errors.push(...result.errors);
                if (!firstInvalidItem && result.errors.length > 0) {
                    firstInvalidItem = item;
                    firstInvalidControl = result.firstInvalid;
                }
            });

            if (firstInvalidItem) {
                openSupportModal(firstInvalidItem.dataset.supportId, 'error');
                renderModalErrors(errors);
                window.requestAnimationFrame(() => firstInvalidControl?.focus());
            }

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
            input.setAttribute('aria-label', 'ลิงก์หลักฐาน');
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

        const modal = document.querySelector('[data-support-modal]');
        const modalBody = modal?.querySelector('[data-support-modal-body]');
        const editorStore = document.querySelector('[data-support-editor-store]');
        let activeItem = null;
        let activeSnapshot = null;
        let previouslyFocusedElement = null;
        let previousBodyOverflow = '';

        const snapshotSupportItem = (item) => ({
            score: item.querySelector('[data-support-score]')?.value ?? '',
            reason: item.querySelector('[data-support-reason]')?.value ?? '',
            evidenceLinks: Array.from(item.querySelectorAll('[data-support-evidence-input]'))
                .map((input) => input.value),
        });

        const restoreSupportItem = (item, snapshot) => {
            const score = item.querySelector('[data-support-score]');
            const reason = item.querySelector('[data-support-reason]');
            if (score) score.value = snapshot.score;
            if (reason) reason.value = snapshot.reason;

            const container = item.querySelector('[data-support-evidence-container]');
            if (container) {
                const evidenceLinks = snapshot.evidenceLinks.length > 0 ? snapshot.evidenceLinks : [''];
                container.replaceChildren(...evidenceLinks.map((link) => {
                    const row = createEvidenceRow(item.dataset.supportId);
                    const input = row.querySelector('[data-support-evidence-input]');
                    if (input) input.value = link;
                    return row;
                }));
            }
        };

        const updateSupportRow = (item) => {
            const id = item.dataset.supportId;
            const score = item.querySelector('[data-support-score]')?.value.trim() || '';
            const evidenceInputs = Array.from(item.querySelectorAll('[data-support-evidence-input]'));
            const evidenceLinks = evidenceInputs.length > 0
                ? evidenceInputs.map((input) => input.value.trim()).filter(Boolean)
                : Array.from(item.querySelectorAll('[data-support-evidence-section] a[href]'))
                    .map((link) => link.getAttribute('href'))
                    .filter(Boolean);

            document.querySelectorAll(`[data-support-evidence-count="${id}"]`).forEach((container) => {
                container.replaceChildren();
                if (evidenceLinks.length === 0) {
                    const empty = document.createElement('span');
                    empty.className = 'text-slate-400';
                    empty.textContent = 'ไม่มีหลักฐาน';
                    container.appendChild(empty);
                    return;
                }

                const button = document.createElement('button');
                button.type = 'button';
                button.dataset.supportEvidenceOpen = id;
                button.setAttribute('aria-label', `ดูหลักฐานของ ${item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน'} ${evidenceLinks.length} ลิงก์`);
                button.className = 'font-semibold text-blue-700 underline decoration-blue-300 underline-offset-4 focus:outline-none focus:ring-2 focus:ring-blue-400';
                button.textContent = `${evidenceLinks.length} ลิงก์`;
                container.appendChild(button);
            });

            document.querySelectorAll(`[data-support-manage-open="${id}"]`).forEach((button) => {
                const label = score !== '' || evidenceLinks.length > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล';
                button.textContent = label;
                button.setAttribute('aria-label', `${label}สำหรับ ${item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน'}`);
            });
        };

        const clearModalErrors = () => {
            const errors = modal?.querySelector('[data-support-modal-errors]');
            if (!errors) return;
            errors.replaceChildren();
            errors.classList.add('hidden');
        };

        const renderModalErrors = (errors) => {
            const container = modal?.querySelector('[data-support-modal-errors]');
            if (!container) return;
            container.replaceChildren();
            container.classList.toggle('hidden', errors.length === 0);
            if (errors.length === 0) return;

            const list = document.createElement('ul');
            list.className = 'list-inside list-disc space-y-1';
            [...new Set(errors)].forEach((message) => {
                const entry = document.createElement('li');
                entry.textContent = message;
                list.appendChild(entry);
            });
            container.appendChild(list);
        };

        const closeSupportModal = ({ restore = true } = {}) => {
            if (!activeItem || !modal || !editorStore) return;

            const item = activeItem;
            if (restore && activeSnapshot) restoreSupportItem(item, activeSnapshot);
            editorStore.appendChild(item);
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = previousBodyOverflow;
            clearModalErrors();
            updateSupportRow(item);
            window.recalculateSupportScores();

            activeItem = null;
            activeSnapshot = null;
            if (previouslyFocusedElement?.isConnected) previouslyFocusedElement.focus();
            previouslyFocusedElement = null;
        };

        const openSupportModal = (criterionId, section = 'score') => {
            if (!modal || !modalBody || !editorStore) return;
            if (activeItem?.dataset.supportId === String(criterionId)) return;
            if (activeItem) closeSupportModal({ restore: true });

            const item = document.querySelector(`[data-support-item][data-support-id="${criterionId}"]`);
            if (!item) return;

            activeItem = item;
            activeSnapshot = snapshotSupportItem(item);
            previouslyFocusedElement = document.activeElement;
            previousBodyOverflow = document.body.style.overflow;

            const sequence = modal.querySelector('[data-support-modal-sequence]');
            const title = modal.querySelector('[data-support-modal-title]');
            if (sequence) sequence.textContent = `กรอกผลรายการ ${item.dataset.supportSequence || ''}`;
            if (title) title.textContent = item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน';

            clearModalErrors();
            modalBody.appendChild(item);
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';

            const target = section === 'evidence'
                ? item.querySelector('[data-support-evidence-input], [data-support-evidence-section] a, [data-add-support-evidence]')
                : item.querySelector('[data-support-score], [data-support-evidence-section] a');
            window.requestAnimationFrame(() => target?.focus());
        };

        document.addEventListener('input', (event) => {
            if (event.target.closest('[data-support-score]')) {
                window.recalculateSupportScores();
            }
        });

        document.addEventListener('click', (event) => {
            const manageButton = event.target.closest('[data-support-manage-open]');
            if (manageButton) {
                openSupportModal(manageButton.dataset.supportManageOpen, 'score');
                return;
            }

            const evidenceButton = event.target.closest('[data-support-evidence-open]');
            if (evidenceButton) {
                openSupportModal(evidenceButton.dataset.supportEvidenceOpen, 'evidence');
                return;
            }

            if (event.target.closest('[data-support-modal-cancel]')) {
                closeSupportModal({ restore: true });
                return;
            }

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

        modal?.querySelector('[data-support-modal-save]')?.addEventListener('click', () => {
            if (!activeItem) return;
            const result = validateSupportItem(activeItem);
            renderModalErrors(result.errors);
            if (result.errors.length > 0) {
                result.firstInvalid?.focus();
                return;
            }
            updateSupportRow(activeItem);
            window.recalculateSupportScores();
            closeSupportModal({ restore: false });
        });

        modal?.addEventListener('click', (event) => {
            if (event.target === modal) closeSupportModal({ restore: true });
        });

        const trapSupportModalFocus = (event) => {
            if (!modal) return;
            const focusable = Array.from(modal.querySelectorAll(
                'a[href], button:not([disabled]), input:not([type="hidden"]):not([disabled]), textarea:not([disabled]), select:not([disabled]), [tabindex]:not([tabindex="-1"])',
            )).filter((element) => element.offsetParent !== null);
            if (focusable.length === 0) return;

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (event.shiftKey && document.activeElement === first) {
                event.preventDefault();
                last.focus();
            } else if (!event.shiftKey && document.activeElement === last) {
                event.preventDefault();
                first.focus();
            }
        };

        document.addEventListener('keydown', (event) => {
            if (event.key === 'Escape' && activeItem) closeSupportModal({ restore: true });
            if (event.key === 'Tab' && activeItem) trapSupportModalFocus(event);
        });

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', window.recalculateSupportScores, { once: true });
        } else {
            window.recalculateSupportScores();
        }
    })();
</script>
