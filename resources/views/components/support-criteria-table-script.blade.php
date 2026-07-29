<script>
    (() => {
        const parseDisplayedNumber = (value) => Number.parseFloat(String(value || '0').replaceAll(',', '')) || 0;

        const normalizeScore = (value) => {
            const trimmed = String(value ?? '').trim();
            if (trimmed === '') return '';
            const number = Number(trimmed);
            return Number.isFinite(number) ? number.toFixed(2) : trimmed;
        };

        const activityTools = () => window.SupportActivityEntries || {
            activityEntryFieldName: (criterionId, index, field) =>
                `support_list[${criterionId}][activity_entries][${index}][${field}]`,
            activityHtmlHasVisibleText: (html) => String(html ?? '')
                .replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, '')
                .replace(/<[^>]*>/g, ' ')
                .replace(/&(nbsp|#160|#xA0);/gi, ' ')
                .trim() !== '',
            activityHtmlPlainText: (html) => String(html ?? '')
                .replace(/<(script|style)\b[^>]*>[\s\S]*?<\/\1>/gi, '')
                .replace(/<[^>]*>/g, ' ')
                .replace(/&(nbsp|#160|#xA0);/gi, ' ')
                .replace(/\s+/gu, ' ')
                .trim(),
        };

        const calculateEntryWeightedScore = (weight, achievedScore) => {
            const calculator = window.SupportScoreCalculator?.calculateEntryWeightedScore;
            if (calculator) return calculator(weight, achievedScore);
            if (weight === '' || achievedScore === '') return null;
            const result = (Number(weight) * Number(achievedScore)) / 100;
            return Number.isFinite(result) ? Math.round(result * 100) / 100 : null;
        };

        const isCriterionScoreValid = (value, targetValue) => {
            const validator = window.SupportScoreCalculator?.isCriterionScoreValid;
            if (validator) return validator(value, targetValue);

            const rawValue = String(value ?? '').trim();
            if (rawValue === '') return true;
            const score = Number(rawValue);
            const target = Number(targetValue);
            return Number.isInteger(score)
                && score >= 1
                && score <= 5
                && Number.isFinite(target)
                && score <= target;
        };

        const getSupportScoreValidationState = (value, targetValue, options = {}) => {
            const validator = window.SupportScoreCalculator?.getSupportScoreValidationState;
            if (validator) return validator(value, targetValue, options);

            const rawValue = String(value ?? '').trim();
            const required = options.required ?? false;
            const valid = (!required && rawValue === '')
                || (rawValue !== '' && isCriterionScoreValid(rawValue, targetValue));

            return {
                valid,
                message: valid
                    ? ''
                    : `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย ${targetValue}`,
            };
        };

        const setScoreFieldValidity = (input, { required = false } = {}) => {
            if (!input) return true;

            const targetValue = input.dataset.supportTarget
                ?? input.dataset.supportEntryTarget
                ?? '';
            const state = getSupportScoreValidationState(input.value, targetValue, { required });
            const error = input.parentElement?.querySelector('[data-support-score-error]');

            input.classList.toggle('border-slate-300', state.valid);
            input.classList.toggle('border-red-500', !state.valid);
            input.classList.toggle('focus:border-red-500', !state.valid);
            input.classList.toggle('focus:ring-red-200', !state.valid);
            if (state.valid) {
                input.removeAttribute('aria-invalid');
            } else {
                input.setAttribute('aria-invalid', 'true');
            }
            if (error) {
                error.textContent = state.message;
                error.classList.toggle('hidden', state.valid);
            }

            return state.valid;
        };

        const updateEntryWeightedScore = (entry) => {
            const weight = entry.querySelector('[data-support-entry-weight]')?.value.trim() ?? '';
            const achievedScore = entry.querySelector('[data-support-entry-score]')?.value.trim() ?? '';
            const weighted = calculateEntryWeightedScore(weight, achievedScore);
            const display = entry.querySelector('[data-support-entry-weighted]');
            if (display) display.textContent = weighted === null ? '-' : weighted.toFixed(2);
            return weighted;
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
                } else if (item.dataset.supportAllowEntryWeight === '1') {
                    weighted = Array.from(item.querySelectorAll('[data-support-activity-entry]'))
                        .map(updateEntryWeightedScore)
                        .filter((value) => value !== null)
                        .reduce((total, value) => total + value, 0);
                    weighted = Math.round(weighted * 100) / 100;
                } else {
                    const existingWeighted = item.dataset.supportExistingWeighted;
                    if (existingWeighted !== '' && Number.isFinite(Number(existingWeighted))) {
                        weighted = Number(existingWeighted);
                    }
                }

                if (input) {
                    document.querySelectorAll(`[data-support-achieved-display="${id}"]`).forEach((display) => {
                        display.textContent = achieved === null ? '-' : achieved.toFixed(2);
                    });
                }
                document.querySelectorAll(`[data-support-weighted-display="${id}"]`).forEach((display) => {
                    display.textContent = weighted === null ? '-' : weighted.toFixed(2);
                });

                if (weighted !== null) rawTotal += weighted;
            });

            const cappedSupport = Math.min(rawTotal, 100);
            const supportSummary = document.getElementById('support-summary');
            if (supportSummary) supportSummary.textContent = cappedSupport.toFixed(2);

            const supportAchievementSummary = document.getElementById('support-achievement-summary');
            if (supportAchievementSummary) {
                const supportTargetLevelCount = Number(supportAchievementSummary.dataset.supportTargetLevelCount);
                const calculateSupportAchievement = window.SupportScoreCalculator?.calculateSupportAchievement
                    || ((total, levels) => Math.round((total / levels + Number.EPSILON) * 100) / 100);
                supportAchievementSummary.textContent = calculateSupportAchievement(
                    cappedSupport,
                    supportTargetLevelCount,
                ).toFixed(2);
            }

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

            const activity = item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน';
            const value = input?.value.trim() ?? '';
            const targetValue = input ? (input.dataset.supportTarget ?? '') : '';
            if (input && !isCriterionScoreValid(value, targetValue)) {
                errors.push(`ค่าคะแนนที่ได้ของ "${activity}" ต้องเป็นจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย ${targetValue}`);
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
            const scoreChanged = input
                && normalizeScore(value) !== normalizeScore(input.dataset.supportOriginalScore);
            if (item.dataset.supportRequireReason === '1' && scoreChanged && !reason?.value.trim()) {
                errors.push(`กรุณาระบุเหตุผลการแก้คะแนนของ "${activity}"`);
                rememberInvalid(reason || input);
            }


            syncActivityEditorValues(item);
            item.querySelectorAll('[data-support-activity-entry]').forEach((entry, entryIndex) => {
                const content = entry.querySelector('[data-support-activity-content]');
                if (!content) return;

                if (!activityTools().activityHtmlHasVisibleText(content.value)) {
                    errors.push(`กรุณากรอกข้อความกิจกรรม/โครงการรายการที่ ${entryIndex + 1} ของ "${activity}"`);
                    rememberInvalid(content);
                }

                const indicator = entry.querySelector('[data-support-entry-indicator]');
                if (indicator && !activityTools().activityHtmlHasVisibleText(indicator.value)) {
                    errors.push(`กรุณากรอกตัวชี้วัด/เกณฑ์การประเมินรายการที่ ${entryIndex + 1} ของ "${activity}"`);
                    rememberInvalid(indicator);
                }

                const weight = entry.querySelector('[data-support-entry-weight]');
                const achievedScore = entry.querySelector('[data-support-entry-score]');
                const decimalPattern = /^\d+(\.\d{1,2})?$/;
                if (weight && (!decimalPattern.test(weight.value.trim())
                    || Number(weight.value) <= 0 || Number(weight.value) > 100)) {
                    errors.push(`น้ำหนักรายการที่ ${entryIndex + 1} ของ "${activity}" ต้องมากกว่า 0 ไม่เกิน 100 และมีทศนิยมไม่เกิน 2 ตำแหน่ง`);
                    rememberInvalid(weight);
                }
                if (achievedScore && (
                    achievedScore.value.trim() === ''
                    || !isCriterionScoreValid(
                        achievedScore.value,
                        achievedScore.dataset.supportEntryTarget ?? '',
                    )
                )) {
                    const targetValue = achievedScore.dataset.supportEntryTarget ?? '';
                    errors.push(`ค่าคะแนนที่ได้รายการที่ ${entryIndex + 1} ของ "${activity}" ต้องเป็นจำนวนเต็ม 1–5 และไม่เกินระดับค่าเป้าหมาย ${targetValue}`);
                    rememberInvalid(achievedScore);
                }

                const originalContent = content.dataset.originalContent ?? '';
                const activityReason = entry.querySelector('[data-support-activity-reason]');
                const activityChanged = content.value !== originalContent
                    || (indicator && indicator.value !== (indicator.dataset.originalIndicator ?? ''))
                    || (weight && normalizeScore(weight.value) !== normalizeScore(weight.dataset.originalWeight))
                    || (achievedScore
                        && normalizeScore(achievedScore.value) !== normalizeScore(achievedScore.dataset.originalScore));
                if (item.dataset.supportActivityRole === 'reviewer'
                    && activityChanged
                    && !activityReason?.value.trim()) {
                    errors.push(`กรุณาระบุเหตุผลที่แก้ไขกิจกรรม/โครงการรายการที่ ${entryIndex + 1} ของ "${activity}"`);
                    rememberInvalid(activityReason || content);
                }
            });

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

        const createEvidenceRow = (criterionId, entryIndex = null) => {
            const row = document.createElement('div');
            row.className = 'support-evidence-row flex items-center gap-2';

            const input = document.createElement('input');
            input.type = 'url';
            input.name = entryIndex === null
                ? `support_list[${criterionId}][evidence_links][]`
                : activityTools().activityEvidenceFieldName(criterionId, entryIndex);
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

        const createActivityEvidenceSection = () => {
            const section = document.createElement('section');
            section.className = 'mt-4 border-t border-slate-200 pt-4';
            section.dataset.supportEvidenceSection = '';

            const headingRow = document.createElement('div');
            headingRow.className = 'flex items-center justify-between gap-3';

            const heading = document.createElement('h6');
            heading.className = 'text-sm font-semibold text-slate-700';
            heading.textContent = 'หลักฐาน';

            const addButton = document.createElement('button');
            addButton.type = 'button';
            addButton.dataset.addSupportEvidence = '';
            addButton.className = 'rounded-lg bg-amber-100 px-3 py-2 text-sm font-semibold text-amber-900 transition hover:bg-amber-200 focus:outline-none focus:ring-2 focus:ring-amber-400';
            addButton.textContent = '+ เพิ่มลิงก์หลักฐาน';

            const container = document.createElement('div');
            container.className = 'mt-2 space-y-2';
            container.dataset.supportEvidenceContainer = '';
            container.appendChild(createEvidenceRow('', 0));

            headingRow.append(heading, addButton);
            section.append(headingRow, container);
            return section;
        };

        const activityEditorOptions = {
            height: 250,
            toolbar: [
                ['style', ['style']],
                ['font', ['bold', 'italic', 'underline', 'clear']],
                ['color', ['color']],
                ['para', ['ul', 'ol', 'paragraph']],
                ['table', ['table']],
                ['insert', ['link', 'hr']],
                ['view', ['fullscreen', 'codeview', 'help']],
            ],
            placeholder: 'กิจกรรม/โครงการ/งาน',
            lang: 'th-TH',
        };

        const syncActivityEditorValues = (item) => {
            item?.querySelectorAll('.support-activity-richtext').forEach((textarea) => {
                if (!window.jQuery || typeof window.jQuery.fn?.summernote !== 'function') return;
                const $editor = window.jQuery(textarea);
                if ($editor.next('.note-editor').length > 0) {
                    textarea.value = $editor.summernote('code') || '';
                }
            });
        };

        const initializeActivityEditors = (item) => {
            if (!window.jQuery || typeof window.jQuery.fn?.summernote !== 'function') return;
            item?.querySelectorAll('.support-activity-richtext').forEach((textarea) => {
                const $editor = window.jQuery(textarea);
                if ($editor.next('.note-editor').length > 0 || $editor.data('summernoteInitialized') === true) return;

                $editor.summernote({
                    ...activityEditorOptions,
                    callbacks: {
                        onChange(contents) {
                            textarea.value = contents;
                        },
                    },
                });
                $editor.data('summernoteInitialized', true);
            });
        };

        const destroyActivityEditors = (item) => {
            if (!item) return;
            syncActivityEditorValues(item);
            item.querySelectorAll('.support-activity-richtext').forEach((textarea) => {
                if (window.jQuery && typeof window.jQuery.fn?.summernote === 'function') {
                    const $editor = window.jQuery(textarea);
                    if ($editor.next('.note-editor').length > 0) $editor.summernote('destroy');
                    $editor.removeData('summernoteInitialized');
                }
                textarea.textContent = textarea.value;
            });
        };

        const createActivityEntryRow = (item, indicatorItemId = '') => {
            const row = document.createElement('article');
            row.className = 'rounded-lg border border-slate-200 bg-slate-50 p-3';
            row.dataset.supportActivityEntry = '';
            row.dataset.supportIndicatorItemId = indicatorItemId;

            const indicatorId = document.createElement('input');
            indicatorId.type = 'hidden';
            indicatorId.value = indicatorItemId;
            indicatorId.dataset.supportActivityIndicatorId = '';

            const label = document.createElement('label');
            label.className = 'block text-sm font-semibold text-slate-700';
            const labelText = document.createElement('span');
            labelText.dataset.supportActivityEntryLabel = '';
            const textarea = document.createElement('textarea');
            textarea.rows = 6;
            textarea.className = 'support-activity-richtext mt-2 block w-full rounded-lg border border-slate-300 p-2.5';
            textarea.dataset.supportActivityContent = '';
            textarea.dataset.originalContent = '';
            label.append(labelText, textarea);

            if (item.dataset.supportAllowEntryIndicator === '1') {
                const indicatorLabel = document.createElement('label');
                indicatorLabel.className = 'mt-4 block text-sm font-semibold text-slate-700';
                indicatorLabel.append('ตัวชี้วัด/เกณฑ์การประเมิน');
                const indicator = document.createElement('textarea');
                indicator.rows = 6;
                indicator.className = 'support-activity-richtext mt-2 block w-full rounded-lg border border-slate-300 p-2.5';
                indicator.dataset.supportEntryIndicator = '';
                indicator.dataset.originalIndicator = '';
                indicatorLabel.appendChild(indicator);
                row.append(indicatorLabel);
            }

            if (item.dataset.supportAllowEntryWeight === '1') {
                const scoreGrid = document.createElement('div');
                scoreGrid.className = 'mt-4 grid gap-4 sm:grid-cols-3';
                [
                    ['น้ำหนัก', 'supportEntryWeight'],
                    ['ค่าคะแนนที่ได้', 'supportEntryScore'],
                ].forEach(([text, datasetKey]) => {
                    const scoreLabel = document.createElement('label');
                    scoreLabel.className = 'block text-sm font-semibold text-slate-700';
                    scoreLabel.append(text);
                    const scoreInput = document.createElement('input');
                    scoreInput.type = 'number';
                    scoreInput.dataset[datasetKey] = '';
                    scoreInput.className = 'mt-2 block w-full rounded-lg border border-slate-300 bg-white px-3 py-2.5 text-slate-900';
                    if (datasetKey === 'supportEntryWeight') {
                        scoreInput.min = '0.01';
                        scoreInput.max = '100';
                        scoreInput.step = '0.01';
                        scoreInput.dataset.originalWeight = '';
                        scoreLabel.appendChild(scoreInput);
                    } else {
                        const targetValue = item.dataset.supportTarget ?? '';
                        scoreInput.min = '1';
                        scoreInput.max = String(Math.min(5, Number(targetValue)));
                        scoreInput.step = '1';
                        scoreInput.dataset.supportEntryTarget = targetValue;
                        scoreInput.dataset.originalScore = '';

                        const help = document.createElement('span');
                        help.dataset.supportScoreHelp = '';
                        help.className = 'mt-1 block text-xs font-normal text-slate-500';
                        help.textContent = `กรอกเฉพาะจำนวนเต็มตั้งแต่ 1–5 และต้องไม่เกินระดับค่าเป้าหมาย ${targetValue}`;
                        const error = document.createElement('span');
                        error.dataset.supportScoreError = '';
                        error.className = 'mt-1 hidden text-xs font-normal text-red-600';
                        error.setAttribute('aria-live', 'polite');
                        scoreLabel.append(scoreInput, help, error);
                    }
                    scoreGrid.appendChild(scoreLabel);
                });
                const weightedCard = document.createElement('div');
                weightedCard.className = 'rounded-lg border border-amber-200 bg-amber-50 px-4 py-3';
                const weightedLabel = document.createElement('span');
                weightedLabel.className = 'text-xs font-semibold text-amber-700';
                weightedLabel.textContent = 'คะแนนถ่วงน้ำหนัก';
                const weightedDisplay = document.createElement('div');
                weightedDisplay.className = 'mt-2 text-xl font-bold tabular-nums text-amber-950';
                weightedDisplay.dataset.supportEntryWeighted = '';
                weightedDisplay.textContent = '-';
                weightedCard.append(weightedLabel, weightedDisplay);
                scoreGrid.appendChild(weightedCard);
                row.append(scoreGrid);
            }

            const removeButton = document.createElement('button');
            removeButton.type = 'button';
            removeButton.dataset.removeSupportActivity = '';
            removeButton.className = 'mt-2 rounded-lg bg-red-50 px-3 py-1.5 text-sm font-semibold text-red-700 transition hover:bg-red-100';
            removeButton.textContent = 'ลบรายการ';

            row.prepend(indicatorId, label);
            row.append(removeButton, createActivityEvidenceSection());
            return row;
        };

        const reindexActivityEntries = (item) => {
            const criterionId = item.dataset.supportId;
            const rows = Array.from(item.querySelectorAll('[data-support-activity-entry]'));
            rows.forEach((row, index) => {
                const label = row.querySelector('[data-support-activity-entry-label]');
                if (label) label.textContent = `รายการ ${index + 1}`;

                const id = row.querySelector('[data-support-activity-id]');
                const indicatorId = row.querySelector('[data-support-activity-indicator-id]');
                const content = row.querySelector('[data-support-activity-content]');
                const indicator = row.querySelector('[data-support-entry-indicator]');
                const weight = row.querySelector('[data-support-entry-weight]');
                const achievedScore = row.querySelector('[data-support-entry-score]');
                const reason = row.querySelector('[data-support-activity-reason]');
                const evidenceInputs = row.querySelectorAll('[data-support-evidence-input]');
                if (id) id.name = activityTools().activityEntryFieldName(criterionId, index, 'id');
                if (indicatorId) {
                    indicatorId.name = activityTools().activityEntryFieldName(
                        criterionId,
                        index,
                        'support_indicator_item_id',
                    );
                }
                if (content) content.name = activityTools().activityEntryFieldName(criterionId, index, 'content');
                if (indicator) indicator.name = activityTools().activityEntryFieldName(criterionId, index, 'indicator');
                if (weight) weight.name = activityTools().activityEntryFieldName(criterionId, index, 'weight');
                if (achievedScore) {
                    achievedScore.name = activityTools().activityEntryFieldName(
                        criterionId,
                        index,
                        'achieved_score',
                    );
                    const help = achievedScore.parentElement?.querySelector('[data-support-score-help]');
                    const error = achievedScore.parentElement?.querySelector('[data-support-score-error]');
                    const helpId = `support-entry-score-help-${criterionId}-${index}`;
                    const errorId = `support-entry-score-error-${criterionId}-${index}`;
                    if (help) help.id = helpId;
                    if (error) error.id = errorId;
                    achievedScore.setAttribute('aria-describedby', `${helpId} ${errorId}`);
                }
                if (reason) reason.name = activityTools().activityEntryFieldName(criterionId, index, 'modification_reason');
                evidenceInputs.forEach((evidenceInput) => {
                    evidenceInput.name = activityTools().activityEvidenceFieldName(criterionId, index);
                });
            });

            const groups = Array.from(item.querySelectorAll('[data-support-activity-group]'));
            if (groups.length > 0) {
                groups.forEach((group) => {
                    const groupRows = group.querySelectorAll('[data-support-activity-entry]');
                    group.querySelector('[data-support-activity-empty]')
                        ?.classList.toggle('hidden', groupRows.length > 0);
                });
            } else {
                item.querySelector('[data-support-activity-empty]')
                    ?.classList.toggle('hidden', rows.length > 0);
            }
        };

        const snapshotActivityEntries = (item) => {
            const section = item.querySelector('[data-support-activity-section]');
            return section?.cloneNode(true) || null;
        };

        const restoreActivityEntries = (item, snapshot) => {
            const section = item.querySelector('[data-support-activity-section]');
            if (!section || !snapshot) return;
            const restored = snapshot.cloneNode(true);
            section.replaceWith(restored);
            reindexActivityEntries(item);
        };

        const updateActivityDisplays = (item) => {
            const id = item.dataset.supportId;
            const contentFields = Array.from(item.querySelectorAll('[data-support-activity-content]'));
            if (contentFields.length === 0) return;
            const contents = contentFields
                .map((textarea) => ({
                    html: textarea.value,
                    indicatorItemId: textarea.closest('[data-support-activity-entry]')
                        ?.querySelector('[data-support-activity-indicator-id]')?.value || '',
                }))
                .filter((entry) => activityTools().activityHtmlHasVisibleText(entry.html));

            document.querySelectorAll(`[data-support-activity-list="${id}"]`).forEach((container) => {
                if (item.dataset.supportGrouped === '1') {
                    container.querySelectorAll('[data-support-display-group]').forEach((group) => {
                        const groupEntries = contents.filter(
                            (entry) => String(entry.indicatorItemId) === group.dataset.supportDisplayGroup,
                        );
                        const target = group.querySelector('[data-support-display-group-entries]');
                        if (!target) return;
                        activityTools().renderActivityEntryList(target, groupEntries, {
                            emptyText: 'ยังไม่มีโครงการในข้อนี้',
                        });
                    });
                    return;
                }

                activityTools().renderActivityEntryList(container, contents, {
                    emptyText: 'ยังไม่มีกิจกรรม/โครงการเพิ่มเติม',
                });
            });
        };

        const updateEntryValueDisplays = (item) => {
            const id = item.dataset.supportId;
            const entries = Array.from(item.querySelectorAll('[data-support-activity-entry]'));
            const fields = [
                {
                    selector: '[data-support-entry-indicator]',
                    target: `[data-support-entry-indicator-list="${id}"]`,
                    format: (value) => activityTools().activityHtmlPlainText(value),
                },
                {
                    selector: '[data-support-entry-weight]',
                    target: `[data-support-entry-weight-list="${id}"]`,
                    format: normalizeScore,
                },
                {
                    selector: '[data-support-entry-score]',
                    target: `[data-support-entry-score-list="${id}"]`,
                    format: normalizeScore,
                },
            ];

            fields.forEach(({ selector, target, format }) => {
                document.querySelectorAll(target).forEach((container) => {
                    if (container.closest('[data-support-entry-row]')) return;
                    const groupId = container.dataset.supportEntryGroup;
                    const matchingEntries = groupId
                        ? entries.filter((entry) => (
                            entry.querySelector('[data-support-activity-indicator-id]')?.value === groupId
                        ))
                        : entries;
                    const values = matchingEntries.map(
                        (entry) => format(entry.querySelector(selector)?.value ?? ''),
                    );
                    container.replaceChildren();
                    (values.length > 0 ? values : ['-']).forEach((value) => {
                        const listItem = document.createElement('li');
                        listItem.textContent = value || '-';
                        if (values.length === 0) listItem.className = 'text-slate-400';
                        container.appendChild(listItem);
                    });
                });
            });
        };

        const syncDesktopEntryRows = (item) => {
            const id = item.dataset.supportId;
            const entries = Array.from(item.querySelectorAll('[data-support-activity-entry]'));
            const template = Array.from(document.querySelectorAll('[data-support-entry-row-template]'))
                .find((candidate) => candidate.dataset.supportEntryRowTemplate === String(id));
            const stateAnchor = document.querySelector(`[data-support-entry-row-end="${id}"]`);
            const currentRows = Array.from(document.querySelectorAll(`[data-support-entry-row="${id}"]`));
            if (currentRows.length === 0 || !template || !stateAnchor) return;

            const state = activityTools().reconcileActivityEntryRows(
                currentRows,
                entries.length,
                (index) => {
                    const fragment = template.content.cloneNode(true);
                    const row = fragment.querySelector('tr');
                    if (!row) throw new Error('Missing support entry row template');
                    row.dataset.supportEntryRow = String(id);
                    row.dataset.supportEntryIndex = String(index);
                    stateAnchor.before(row);
                    return row;
                },
            );

            state.rows.forEach((row, index) => {
                const entry = entries[index] || null;
                row.dataset.supportEntryIndex = String(index);
                row.toggleAttribute('data-support-entry-empty', !entry);

                row.querySelectorAll('[data-support-entry-cell]').forEach((cell) => {
                    cell.classList.toggle('border-t', index > 0);
                    cell.classList.toggle('border-slate-100', index > 0);
                });

                const number = row.querySelector('[data-support-entry-number]');
                if (number) {
                    number.textContent = String(index + 1);
                    number.classList.toggle('hidden', !entry);
                }

                const activityDisplay = row.querySelector('[data-support-entry-activity-value]');
                const indicatorDisplay = row.querySelector('[data-support-entry-indicator-list]');
                const weightDisplay = row.querySelector('[data-support-entry-weight-list]');
                const scoreDisplay = row.querySelector('[data-support-entry-score-list]');

                if (activityDisplay) {
                    activityDisplay.textContent = entry
                        ? activityTools().activityHtmlPlainText(
                            entry.querySelector('[data-support-activity-content]')?.value ?? '',
                        ) || '-'
                        : 'ยังไม่มีกิจกรรม/โครงการเพิ่มเติม';
                    activityDisplay.classList.toggle('text-slate-400', !entry);
                    activityDisplay.classList.toggle('text-amber-800', Boolean(entry));
                }
                if (indicatorDisplay) {
                    indicatorDisplay.textContent = entry
                        ? activityTools().activityHtmlPlainText(
                            entry.querySelector('[data-support-entry-indicator]')?.value ?? '',
                        ) || '-'
                        : '-';
                }
                if (weightDisplay) {
                    weightDisplay.textContent = entry
                        ? normalizeScore(entry.querySelector('[data-support-entry-weight]')?.value ?? '') || '-'
                        : '-';
                }
                if (scoreDisplay) {
                    scoreDisplay.textContent = entry
                        ? normalizeScore(entry.querySelector('[data-support-entry-score]')?.value ?? '') || '-'
                        : '-';
                }
            });

            state.rows[0].querySelectorAll('[data-support-shared-cell]').forEach((cell) => {
                cell.rowSpan = state.rowCount;
            });
        };

        const modal = document.querySelector('[data-support-modal]');
        const modalBody = modal?.querySelector('[data-support-modal-body]');
        const editorStore = document.querySelector('[data-support-editor-store]');
        const supportHistoryModal = document.getElementById('support-history-modal');
        const supportHistoryList = supportHistoryModal?.querySelector('[data-support-history-list]');
        let activeItem = null;
        let activeSnapshot = null;
        let previouslyFocusedElement = null;
        let previousBodyOverflow = '';
        let supportHistoryTrigger = null;
        let supportHistoryPreviousOverflow = '';

        const updateModalScoreValidity = (item) => {
            if (!item) return true;

            const criterionValid = setScoreFieldValidity(item.querySelector('[data-support-score]'));
            const activityValidities = Array.from(item.querySelectorAll('[data-support-entry-score]'))
                .map((input) => setScoreFieldValidity(input, { required: true }));
            const allValid = criterionValid && activityValidities.every(Boolean);
            const saveButton = modal?.querySelector('[data-support-modal-save]');
            if (saveButton) saveButton.disabled = !allValid;

            return allValid;
        };

        const scoreHistoryValue = (value) => value === null || value === undefined || value === ''
            ? 'ไม่มีคะแนน'
            : value;

        const openSupportHistoryModal = (criterionId, trigger) => {
            const payloadNode = Array.from(document.querySelectorAll('[data-support-history-payload]'))
                .find((node) => node.dataset.supportHistoryPayload === String(criterionId));
            if (!supportHistoryModal || !supportHistoryList || !payloadNode) return;

            let histories = [];
            try {
                histories = JSON.parse(payloadNode.textContent || '[]');
            } catch {
                return;
            }

            supportHistoryList.replaceChildren();
            histories.forEach((history) => {
                const article = document.createElement('article');
                article.className = 'rounded-lg bg-slate-50 p-3 text-sm text-slate-700';

                [
                    `ค่าคะแนน: ${scoreHistoryValue(history.previous_achieved_score)} → ${scoreHistoryValue(history.new_achieved_score)}`,
                    `คะแนนถ่วงน้ำหนัก: ${scoreHistoryValue(history.previous_weighted_score)} → ${scoreHistoryValue(history.new_weighted_score)}`,
                    `เหตุผล: ${history.reason || '-'}`,
                    `แก้ไขโดย ${history.modified_by_name || '-'}${history.modified_by_role ? ` (${history.modified_by_role})` : ''} · ${history.created_at || '-'}`,
                ].forEach((value) => {
                    const line = document.createElement('p');
                    line.textContent = value;
                    article.appendChild(line);
                });
                supportHistoryList.appendChild(article);
            });

            supportHistoryTrigger = trigger;
            supportHistoryPreviousOverflow = document.body.style.overflow;
            document.body.style.overflow = 'hidden';
            supportHistoryModal.classList.remove('hidden');
            supportHistoryModal.classList.add('flex');
            supportHistoryModal.querySelector('[data-support-history-close]')?.focus();
        };

        const closeSupportHistoryModal = () => {
            if (!supportHistoryModal) return;
            supportHistoryModal.classList.add('hidden');
            supportHistoryModal.classList.remove('flex');
            document.body.style.overflow = supportHistoryPreviousOverflow;
            supportHistoryTrigger?.focus();
            supportHistoryTrigger = null;
        };

        const trapSupportHistoryModalFocus = (event) => {
            if (!supportHistoryModal || event.key !== 'Tab') return;
            const focusable = Array.from(supportHistoryModal.querySelectorAll(
                'button:not([disabled]), [href], input:not([disabled]), textarea:not([disabled]), [tabindex]:not([tabindex="-1"])',
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

        const snapshotSupportItem = (item) => ({
            score: item.querySelector('[data-support-score]')?.value ?? '',
            reason: item.querySelector('[data-support-reason]')?.value ?? '',
            evidenceLinks: Array.from(item.querySelectorAll('[data-support-evidence-input]'))
                .filter((input) => !input.closest('[data-support-activity-entry]'))
                .map((input) => input.value),
            activityEntries: snapshotActivityEntries(item),
        });

        const restoreSupportItem = (item, snapshot) => {
            const score = item.querySelector('[data-support-score]');
            const reason = item.querySelector('[data-support-reason]');
            if (score) score.value = snapshot.score;
            if (reason) reason.value = snapshot.reason;

            const container = Array.from(item.querySelectorAll('[data-support-evidence-container]'))
                .find((candidate) => !candidate.closest('[data-support-activity-entry]'));
            if (container) {
                const evidenceLinks = snapshot.evidenceLinks.length > 0 ? snapshot.evidenceLinks : [''];
                container.replaceChildren(...evidenceLinks.map((link) => {
                    const row = createEvidenceRow(item.dataset.supportId);
                    const input = row.querySelector('[data-support-evidence-input]');
                    if (input) input.value = link;
                    return row;
                }));
            }
            restoreActivityEntries(item, snapshot.activityEntries);
        };

        const updateSupportRow = (item) => {
            const id = item.dataset.supportId;
            const score = item.querySelector('[data-support-score]')?.value.trim() || '';
            const activityEntries = Array.from(item.querySelectorAll('[data-support-activity-entry]'))
                .map((entry) => {
                    const inputs = Array.from(entry.querySelectorAll('[data-support-evidence-input]'));
                    const links = inputs.length > 0
                        ? inputs.map((input) => input.value.trim()).filter(Boolean)
                        : Array.from(entry.querySelectorAll('[data-support-evidence-section] a[href]'))
                            .map((link) => link.getAttribute('href'))
                            .filter(Boolean);

                    return {
                        id: entry.dataset.supportActivityEntryId || null,
                        evidence_links: links,
                    };
                });
            const activityEvidenceGroups = activityTools().activityEvidenceGroups(activityEntries);
            const criterionEvidenceInputs = Array.from(item.querySelectorAll('[data-support-evidence-input]'))
                .filter((input) => !input.closest('[data-support-activity-entry]'));
            const criterionEvidenceLinks = criterionEvidenceInputs.length > 0
                ? criterionEvidenceInputs.map((input) => input.value.trim()).filter(Boolean)
                : Array.from(item.querySelectorAll('[data-support-evidence-section] a[href]'))
                    .filter((link) => !link.closest('[data-support-activity-entry]'))
                    .map((link) => link.getAttribute('href'))
                    .filter(Boolean);
            const evidenceLinks = activityEntries.length > 0
                ? activityEvidenceGroups.flatMap((group) => group.links)
                : criterionEvidenceLinks;

            document.querySelectorAll(`[data-support-evidence-list="${id}"]`).forEach((container) => {
                container.replaceChildren();
                if (evidenceLinks.length === 0) {
                    const empty = document.createElement('span');
                    empty.className = 'text-slate-400';
                    empty.textContent = 'ไม่มีหลักฐาน';
                    container.appendChild(empty);
                    return;
                }

                const appendAnchor = (target, evidenceUrl, label = 'เปิดดู', ariaLabel = label) => {
                    const anchor = document.createElement('a');
                    anchor.href = evidenceUrl;
                    anchor.target = '_blank';
                    anchor.rel = 'noopener noreferrer';
                    anchor.className = 'inline-flex max-w-full items-center justify-center gap-1 whitespace-nowrap rounded-lg border border-blue-200 bg-blue-50 px-2 py-1.5 text-xs font-semibold leading-tight text-blue-700 shadow-sm transition hover:border-blue-300 hover:bg-blue-100 hover:text-blue-800 focus:outline-none focus:ring-2 focus:ring-blue-400 focus:ring-offset-1';
                    anchor.textContent = `🔗 ${label} ↗`;
                    anchor.setAttribute('aria-label', ariaLabel);
                    target.appendChild(anchor);
                };

                if (activityEntries.length > 0) {
                    activityEvidenceGroups.forEach((group) => {
                        const groupContainer = document.createElement('div');
                        groupContainer.className = 'space-y-1';
                        groupContainer.dataset.supportActivityEvidenceList = group.id;

                        if (activityEvidenceGroups.length > 1) {
                            const label = document.createElement('span');
                            label.className = 'block text-xs font-semibold text-slate-500';
                            label.textContent = group.label;
                            groupContainer.appendChild(label);
                        }
                        group.links.forEach((evidenceUrl, evidenceIndex) => appendAnchor(
                            groupContainer,
                            evidenceUrl,
                            group.links.length > 1 ? `ไฟล์ ${evidenceIndex + 1}` : 'เปิดดู',
                            `เปิดหลักฐาน ${evidenceIndex + 1} สำหรับ${group.label}`,
                        ));
                        container.appendChild(groupContainer);
                    });
                    return;
                }

                criterionEvidenceLinks.forEach((evidenceUrl, evidenceIndex) => appendAnchor(
                    container,
                    evidenceUrl,
                    criterionEvidenceLinks.length > 1 ? `ไฟล์ ${evidenceIndex + 1}` : 'เปิดดู',
                    `เปิดหลักฐาน ${evidenceIndex + 1}`,
                ));
            });

            document.querySelectorAll(`[data-support-manage-open="${id}"]`).forEach((button) => {
                const label = score !== '' || evidenceLinks.length > 0 ? 'แก้ไขข้อมูล' : 'กรอกข้อมูล';
                button.textContent = label;
                button.setAttribute('aria-label', `${label}สำหรับ ${item.dataset.supportActivity || 'เกณฑ์สายสนับสนุน'}`);
            });
            updateActivityDisplays(item);
            updateEntryValueDisplays(item);
            syncDesktopEntryRows(item);
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
            destroyActivityEditors(item);
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
            initializeActivityEditors(item);
            updateModalScoreValidity(item);

            const target = section === 'evidence'
                ? item.querySelector('[data-support-evidence-input], [data-support-evidence-section] a, [data-add-support-evidence]')
                : item.querySelector('[data-support-score], [data-support-activity-content], [data-support-evidence-section] a');
            window.requestAnimationFrame(() => target?.focus());
        };

        document.addEventListener('input', (event) => {
            if (event.target.closest('[data-support-score], [data-support-entry-weight], [data-support-entry-score]')) {
                window.recalculateSupportScores();
            }
            if (activeItem && event.target.closest('[data-support-score], [data-support-entry-score]')) {
                updateModalScoreValidity(activeItem);
            }
        });

        document.addEventListener('click', (event) => {
            const historyButton = event.target.closest('[data-support-history-open]');
            if (historyButton) {
                event.preventDefault();
                openSupportHistoryModal(historyButton.dataset.supportHistoryOpen, historyButton);
                return;
            }

            if (event.target.closest('[data-support-history-close]')) {
                event.preventDefault();
                closeSupportHistoryModal();
                return;
            }

            const manageButton = event.target.closest('[data-support-manage-open]');
            if (manageButton) {
                openSupportModal(manageButton.dataset.supportManageOpen, 'score');
                return;
            }

            if (event.target.closest('[data-support-modal-cancel]')) {
                closeSupportModal({ restore: true });
                return;
            }

            const addActivityButton = event.target.closest('[data-add-support-activity]');
            if (addActivityButton) {
                const item = addActivityButton.closest('[data-support-item]');
                const group = addActivityButton.closest('[data-support-activity-group]');
                const container = group?.querySelector('[data-support-activity-container]')
                    || item?.querySelector('[data-support-activity-container]');
                if (!item || !container || item.dataset.supportActivityRole !== 'evaluatee') return;
                const row = createActivityEntryRow(
                    item,
                    addActivityButton.dataset.supportIndicatorItemId || '',
                );
                container.appendChild(row);
                reindexActivityEntries(item);
                initializeActivityEditors(row);
                updateModalScoreValidity(item);
                window.requestAnimationFrame(() => row.querySelector('.note-editable, textarea')?.focus());
                return;
            }

            const removeActivityButton = event.target.closest('[data-remove-support-activity]');
            if (removeActivityButton) {
                const item = removeActivityButton.closest('[data-support-item]');
                const row = removeActivityButton.closest('[data-support-activity-entry]');
                if (!item || !row || item.dataset.supportActivityRole !== 'evaluatee') return;
                destroyActivityEditors(row);
                row.remove();
                reindexActivityEntries(item);
                window.recalculateSupportScores();
                updateModalScoreValidity(item);
                return;
            }

            const addButton = event.target.closest('[data-add-support-evidence]');
            if (addButton) {
                const item = addButton.closest('[data-support-item]');
                const entry = addButton.closest('[data-support-activity-entry]');
                const section = addButton.closest('[data-support-evidence-section]');
                const container = section?.querySelector('[data-support-evidence-container]');
                if (!item || !container) return;

                const entryIndex = entry
                    ? Array.from(item.querySelectorAll('[data-support-activity-entry]')).indexOf(entry)
                    : null;
                const row = createEvidenceRow(item.dataset.supportId, entryIndex);
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

        supportHistoryModal?.addEventListener('click', (event) => {
            if (event.target === supportHistoryModal) closeSupportHistoryModal();
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
            if (!supportHistoryModal?.classList.contains('hidden')) {
                if (event.key === 'Escape') closeSupportHistoryModal();
                if (event.key === 'Tab') trapSupportHistoryModalFocus(event);
                return;
            }
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
