{{-- สคริปต์จัดการฟอร์มและการลากจัดลำดับของ workload --}}
<script>
        document.addEventListener('DOMContentLoaded', function() {
            const params = new URLSearchParams(window.location.search);
            const quantSubCriteriaId = params.get('quant_sub_criteria_id');
            const backLink = document.getElementById('workload-back-link');

            const updateBackLink = (id) => {
                if (!backLink || !id) {
                    return;
                }
                backLink.href = `/criteria-config/${encodeURIComponent(id)}/edit`;
            };
            const navList = document.getElementById('workload-nav-list');
            const sectionBadge = document.getElementById('workload-section-badge');
            const sectionTitle = document.getElementById('workload-section-title');
            const saveButton = document.getElementById('workload-save');
            const resetButton = document.getElementById('workload-reset');
            const floatingSaveButton = document.getElementById('floating_save_button');
            const floatingSaveSubmit = document.getElementById('floating_save_submit');
            const mainCard = document.querySelector('.workload-card');
            // อ้างอิง DOM หลักที่ใช้บ่อย
            const mainContainer = document.querySelector('.workload-card-list');
            const pageActions = document.querySelector('.page-actions');
            let activeDragState = null;
            let isDirty = false;
            let isSubmitting = false;
            const unsavedChangesMessage = 'มีข้อมูลที่แก้ไขแล้วยังไม่ได้บันทึก กรุณาบันทึกก่อนออกจากหน้านี้';

            const updateFloatingSaveButton = () => {
                if (!floatingSaveButton) {
                    return;
                }
                floatingSaveButton.classList.toggle('hidden', !isDirty || isSubmitting);
            };

            const setDirtyState = (nextState) => {
                isDirty = Boolean(nextState);
                updateFloatingSaveButton();
            };

            const markDirty = () => {
                if (isSubmitting) {
                    return;
                }
                setDirtyState(true);
            };

            const resetDirtyState = () => {
                setDirtyState(false);
            };

            const shouldBlockNavigation = (targetUrl = '') => {
                if (!isDirty || isSubmitting) {
                    return false;
                }

                const normalizedTarget = String(targetUrl || '').trim();
                if (!normalizedTarget || normalizedTarget.startsWith('#') || normalizedTarget.startsWith('javascript:')) {
                    return false;
                }

                return true;
            };

            const confirmUnsavedNavigation = (targetUrl = '') => {
                if (!shouldBlockNavigation(targetUrl)) {
                    return true;
                }

                alert(unsavedChangesMessage);
                return false;
            };

            if (floatingSaveSubmit && saveButton) {
                floatingSaveSubmit.addEventListener('click', () => {
                    saveButton.click();
                });
            }

            document.addEventListener('input', (event) => {
                if (event.target.closest('.workload-page')) {
                    markDirty();
                }
            });

            document.addEventListener('change', (event) => {
                if (event.target.closest('.workload-page')) {
                    markDirty();
                }
            });

            document.addEventListener('click', (event) => {
                const link = event.target.closest('a[href]');
                if (!link || !document.body.contains(link)) {
                    return;
                }

                const href = link.getAttribute('href') || '';
                if (!shouldBlockNavigation(href)) {
                    return;
                }

                event.preventDefault();
                alert(unsavedChangesMessage);
            }, true);

            window.addEventListener('beforeunload', (event) => {
                if (!shouldBlockNavigation(window.location.href)) {
                    return;
                }

                event.preventDefault();
                event.returnValue = unsavedChangesMessage;
            });

            if (!quantSubCriteriaId) {
                return;
            }

            const getDirectMatches = (container, selector) => {
                if (!container) {
                    return [];
                }
                return Array.from(container.children).filter((child) => child.matches(selector));
            };

            const clearDragOverState = (container, selector) => {
                getDirectMatches(container, selector).forEach((item) => item.classList.remove('is-drag-over'));
            };

            const getDragAfterElement = (container, selector, pointerY, draggingItem) => {
                const items = getDirectMatches(container, selector)
                    .filter((item) => item !== draggingItem);

                let closest = {
                    offset: Number.NEGATIVE_INFINITY,
                    element: null,
                };

                items.forEach((item) => {
                    const rect = item.getBoundingClientRect();
                    const offset = pointerY - rect.top - rect.height / 2;
                    if (offset < 0 && offset > closest.offset) {
                        closest = {
                            offset,
                            element: item,
                        };
                    }
                });

                return closest.element;
            };

            const bindSortableContainer = (container, options) => {
                if (!container || container.dataset.sortableBound === 'true') {
                    return;
                }

                container.dataset.sortableBound = 'true';

                container.addEventListener('dragover', (event) => {
                    if (!activeDragState || activeDragState.container !== container) {
                        return;
                    }

                    event.preventDefault();

                    const afterElement = getDragAfterElement(
                        container,
                        options.itemSelector,
                        event.clientY,
                        activeDragState.item
                    );

                    clearDragOverState(container, options.itemSelector);
                    if (afterElement) {
                        afterElement.classList.add('is-drag-over');
                        container.insertBefore(activeDragState.item, afterElement);
                        return;
                    }

                    const anchor = options.getAnchor ? options.getAnchor(container) : null;
                    if (anchor) {
                        container.insertBefore(activeDragState.item, anchor);
                    } else {
                        container.appendChild(activeDragState.item);
                    }
                });

                container.addEventListener('drop', (event) => {
                    if (!activeDragState || activeDragState.container !== container) {
                        return;
                    }

                    event.preventDefault();
                    clearDragOverState(container, options.itemSelector);
                    if (typeof options.onUpdate === 'function') {
                        options.onUpdate();
                    }
                    markDirty();
                });

                container.addEventListener('dragleave', (event) => {
                    if (!container.contains(event.relatedTarget)) {
                        clearDragOverState(container, options.itemSelector);
                    }
                });
            };

            const bindDragHandle = (item, handle, config) => {
                if (!item || !handle || handle.dataset.dragBound === 'true') {
                    return;
                }

                handle.dataset.dragBound = 'true';
                handle.setAttribute('draggable', 'true');

                handle.addEventListener('dragstart', (event) => {
                    activeDragState = {
                        item,
                        container: config.container,
                    };
                    item.classList.add('is-dragging');
                    if (event.dataTransfer) {
                        event.dataTransfer.effectAllowed = 'move';
                        event.dataTransfer.setData('text/plain', '');
                    }
                });

                handle.addEventListener('dragend', () => {
                    item.classList.remove('is-dragging');
                    clearDragOverState(config.container, config.itemSelector);

                    if (typeof config.onUpdate === 'function') {
                        config.onUpdate();
                    }
                    markDirty();
                    activeDragState = null;
                });
            };

            const initMainCardSort = (card) => {
                if (!mainContainer || !card) {
                    return;
                }

                bindSortableContainer(mainContainer, {
                    itemSelector: '.workload-card',
                    onUpdate: () => {
                        updateMainSequences();
                    },
                });

                bindDragHandle(card, card.querySelector('.card-head .workload-drag-handle'), {
                    container: mainContainer,
                    itemSelector: '.workload-card',
                    onUpdate: () => {
                        updateMainSequences();
                    },
                });
            };

            const initSubCardSort = (mainCardEl, subCardEl) => {
                const subBlock = mainCardEl ? mainCardEl.querySelector('.sub-block') : null;
                if (!subBlock || !subCardEl) {
                    return;
                }

                bindSortableContainer(subBlock, {
                    itemSelector: '.workload-sub-card',
                    getAnchor: (container) => container.querySelector('.sub-footer'),
                    onUpdate: () => {
                        updateSubSequences(mainCardEl);
                    },
                });

                bindDragHandle(subCardEl, subCardEl.querySelector('.sub-card-head .workload-drag-handle'), {
                    container: subBlock,
                    itemSelector: '.workload-sub-card',
                    onUpdate: () => {
                        updateSubSequences(mainCardEl);
                    },
                });
            };

            const initItemRowSort = (subCardEl) => {
                const subitemTable = subCardEl ? subCardEl.querySelector('.subitem-table') : null;
                const api = subCardEl ? subCardEl.__workload : null;
                if (!subitemTable || !api) {
                    return;
                }

                bindSortableContainer(subitemTable, {
                    itemSelector: '.workload-item-row',
                    getAnchor: (container) => container.querySelector('.subitem-actions'),
                    onUpdate: () => {
                        api.updateItemSequence(subCardEl);
                    },
                });

                subCardEl.querySelectorAll('.workload-item-row').forEach((row) => {
                    bindDragHandle(row, row.querySelector('.workload-item-drag-handle'), {
                        container: subitemTable,
                        itemSelector: '.workload-item-row',
                        onUpdate: () => {
                            api.updateItemSequence(subCardEl);
                        },
                    });
                });
            };

            // ฟังก์ชันย่อย: renderNav
            const renderNav = (items, activeId) => {
                if (!navList) {
                    return;
                }

                navList.innerHTML = '';
                items.forEach((item) => {
                    const button = document.createElement('button');
                    button.className = `nav-item2${item.id === activeId ? ' is-active' : ''}`;
                    button.type = 'button';
                    button.addEventListener('click', () => {
                        if (!confirmUnsavedNavigation(`/workload-config?quant_sub_criteria_id=${encodeURIComponent(item.id)}`)) {
                            return;
                        }
                        window.location.href =
                            `/workload-config?quant_sub_criteria_id=${encodeURIComponent(item.id)}`;
                    });

                    const text = document.createElement('span');
                    text.className = 'nav-text';
                    text.textContent = item.name || `รายการ ${item.sequence || ''}`.trim();

                    button.appendChild(text);
                    navList.appendChild(button);
                });

                if (sectionTitle) {
                    const activeButton = navList.querySelector('.nav-item2.is-active .nav-text');
                    if (activeButton) {
                        sectionTitle.textContent = activeButton.textContent.trim();
                    }
                }
                if (sectionBadge) {
                    const activeIndex = items.findIndex((item) => item.id === activeId);
                    if (activeIndex >= 0) {
                        sectionBadge.textContent = items[activeIndex].sequence || (activeIndex + 1);
                    }
                }
                updateBackLink(activeId);
            };

            // ฟังก์ชันย่อย: insertToken
            const insertToken = (textarea, token) => {
                if (!textarea) {
                    return;
                }
                const start = textarea.selectionStart ?? textarea.value.length;
                const end = textarea.selectionEnd ?? textarea.value.length;
                const before = textarea.value.slice(0, start);
                const after = textarea.value.slice(end);
                textarea.value = `${before}${token}${after}`;
                const nextPos = start + token.length;
                textarea.setSelectionRange(nextPos, nextPos);
                textarea.focus();
                markDirty();
            };

            // ฟังก์ชันย่อย: toggleCollapsed
            const toggleCollapsed = (card, collapsed) => {
                if (!card) {
                    return;
                }
                if (collapsed) {
                    card.classList.add('is-collapsed');
                } else {
                    card.classList.remove('is-collapsed');
                }
                const toggleButton = card.querySelector('.workload-collapse-toggle');
                const toggleIcon = toggleButton ? toggleButton.querySelector('span') : null;
                if (toggleButton) {
                    const isCollapsed = card.classList.contains('is-collapsed');
                    toggleButton.title = isCollapsed ? 'ขยาย' : 'ยุบ';
                    toggleButton.setAttribute('aria-label', isCollapsed ? 'ขยาย' : 'ยุบ');
                }
                if (toggleIcon) {
                    toggleIcon.textContent = card.classList.contains('is-collapsed') ? '˄' : '˅';
                }
            };

            // ฟังก์ชันย่อย: bindCardActions
            const bindCardActions = (card, actionsSelector, options = {}) => {
                if (!card) {
                    return;
                }
                const actions = actionsSelector ? card.querySelector(actionsSelector) : null;
                const scope = actions || card;
                const header = actions ? actions.parentElement : card;
                const collapseToggleBtn = scope.querySelector('.workload-collapse-toggle');
                const deleteBtn = scope.querySelector('.icon-btn.is-danger, .delete_category_btn');

                if (collapseToggleBtn && collapseToggleBtn.dataset.bound !== 'true') {
                    collapseToggleBtn.dataset.bound = 'true';
                    collapseToggleBtn.addEventListener('click', () => {
                        toggleCollapsed(card, !card.classList.contains('is-collapsed'));
                    });
                }
                if (header && header.dataset.expandBound !== 'true') {
                    header.dataset.expandBound = 'true';
                    header.addEventListener('click', (event) => {
                        if (!card.classList.contains('is-collapsed')) {
                            return;
                        }
                        if (event.target.closest('button, a, input, textarea, select, label')) {
                            return;
                        }
                        toggleCollapsed(card, false);
                    });
                }
                toggleCollapsed(card, card.classList.contains('is-collapsed'));
                if (deleteBtn && typeof options.onDelete === 'function' && deleteBtn.dataset.bound !== 'true') {
                    deleteBtn.dataset.bound = 'true';
                    deleteBtn.addEventListener('click', (event) => {
                        event.preventDefault();
                        event.stopPropagation();
                        event.stopImmediatePropagation();
                        if (options.confirm !== false) {
                            const message = options.confirmMessage || 'ยืนยันการลบรายการนี้หรือไม่?';
                            if (!window.confirm(message)) {
                                return;
                            }
                        }
                        const target = options.closestSelector
                            ? deleteBtn.closest(options.closestSelector)
                            : card;
                        options.onDelete(target || card);
                    });
                }
            };

            // ฟังก์ชันย่อย: ensurePageActionsPosition
            const ensurePageActionsPosition = () => {
                if (!mainContainer || !pageActions) {
                    return;
                }
                const content = pageActions.parentElement;
                if (!content || mainContainer.parentElement !== content) {
                    return;
                }
                if (pageActions.previousElementSibling !== mainContainer) {
                    content.insertBefore(mainContainer, pageActions);
                }
                if (content.lastElementChild !== pageActions) {
                    content.appendChild(pageActions);
                }
            };

            // อัปเดตลำดับของหมวดหลักทั้งหมด
            // ฟังก์ชันย่อย: updateMainSequences
            const updateMainSequences = () => {
                const cards = mainContainer
                    ? mainContainer.querySelectorAll('.workload-card')
                    : document.querySelectorAll('.workload-card');
                cards.forEach((card, index) => {
                    const display = card.querySelector('.workload-main-sequence-display');
                    const input = card.querySelector('.workload-main-sequence');
                    if (display) {
                        display.textContent = index + 1;
                    }
                    if (input) {
                        input.value = index + 1;
                    }
                    updateSubSequences(card);
                });
                ensurePageActionsPosition();
            };

            // ฟังก์ชันย่อย: updateSubSequences
            const updateSubSequences = (scope) => {
                const root = scope || document;
                const mainCardEl = root.classList && root.classList.contains('workload-card')
                    ? root
                    : root.closest ? root.closest('.workload-card') : null;
                const mainSequenceInput = mainCardEl ? mainCardEl.querySelector('.workload-main-sequence') : null;
                const mainSequence = mainSequenceInput ? mainSequenceInput.value : '';
                root.querySelectorAll('.workload-sub-card').forEach((card, index) => {
                    const display = card.querySelector('.workload-sub-sequence-display');
                    const input = card.querySelector('.workload-sub-sequence');
                    if (display) {
                        display.textContent = mainSequence ? `${mainSequence}.${index + 1}` : `${index + 1}`;
                    }
                    if (input) {
                        input.value = index + 1;
                    }
                    if (card.__workload && typeof card.__workload.updateItemSequence === 'function') {
                        card.__workload.updateItemSequence(card);
                    }
                });
            };

            // ฟังก์ชันย่อย: initSubCard
            const syncMainCardSummary = (card) => {
                if (!card) {
                    return;
                }
                const input = card.querySelector('.workload-main-category');
                const summary = card.querySelector('.workload-main-summary');
                if (!summary) {
                    return;
                }
                summary.textContent = input && input.value.trim()
                    ? input.value.trim()
                    : 'หมวดหมู่หลัก';
            };

            const syncSubCardSummary = (card) => {
                if (!card) {
                    return;
                }
                const input = card.querySelector('.workload-sub-category');
                const summary = card.querySelector('.workload-sub-summary');
                if (!summary) {
                    return;
                }
                summary.textContent = input && input.value.trim()
                    ? input.value.trim()
                    : 'หมวดย่อย';
            };

            const initSubCard = (card) => {
                if (card.dataset.initialized === 'true') {
                    return;
                }
                bindCardActions(card, '.sub-card-head .card-actions', {
                    closestSelector: '.workload-sub-card',
                    onDelete: (target) => {
                        target.remove();
                        const parentCard = target.closest('.workload-card');
                        updateSubSequences(parentCard || document);
                        markDirty();
                    },
                });
                const formulaList = card.querySelector('.workload-formula-list');
                const formulaText = card.querySelector('.workload-formula-text');
                const addItemButton = card.querySelector('.workload-add-item');
                const addVariableButton = card.querySelector('.workload-add-variable');
                const cancelVariableEditButton = card.querySelector('.workload-cancel-variable-edit');
                const variableLabelInput = card.querySelector('.workload-variable-label');
                const variableNoteInput = card.querySelector('.workload-variable-note');
                const variableDefaultValueInput = card.querySelector('.workload-variable-default-value');
                const variableTypeSelect = card.querySelector('.workload-variable-type');
                const variableChips = card.querySelector('.workload-variable-chips');
                const subitemTable = card.querySelector('.subitem-table');
                const subCategoryInput = card.querySelector('.workload-sub-category');
                let editingFormulaRow = null;

                syncSubCardSummary(card);
                if (subCategoryInput && subCategoryInput.dataset.summaryBound !== 'true') {
                    subCategoryInput.dataset.summaryBound = 'true';
                    subCategoryInput.addEventListener('input', () => {
                        syncSubCardSummary(card);
                    });
                }

                const resetVariableForm = () => {
                    if (variableLabelInput) {
                        variableLabelInput.value = '';
                    }
                    if (variableNoteInput) {
                        variableNoteInput.value = '';
                    }
                    if (variableDefaultValueInput) {
                        variableDefaultValueInput.value = '';
                    }
                    if (variableTypeSelect) {
                        variableTypeSelect.value = '';
                    }
                    if (addVariableButton) {
                        addVariableButton.textContent = 'เพิ่มตัวแปร';
                    }
                    if (cancelVariableEditButton) {
                        cancelVariableEditButton.style.display = 'none';
                    }
                    if (editingFormulaRow) {
                        editingFormulaRow.classList.remove('is-editing');
                    }
                    editingFormulaRow = null;
                };

                const enterVariableEditMode = (row) => {
                    if (!row) {
                        return;
                    }
                    if (editingFormulaRow) {
                        editingFormulaRow.classList.remove('is-editing');
                    }
                    editingFormulaRow = row;
                    editingFormulaRow.classList.add('is-editing');

                    if (variableLabelInput) {
                        variableLabelInput.value = row.querySelector('.formula-item-label')?.textContent.trim() || '';
                    }
                    if (variableNoteInput) {
                        variableNoteInput.value = row.dataset.note || '';
                    }
                    if (variableDefaultValueInput) {
                        variableDefaultValueInput.value = row.dataset.defaultValue || '';
                    }
                    if (variableTypeSelect) {
                        variableTypeSelect.value = row.dataset.fieldType || '';
                    }
                    if (addVariableButton) {
                        addVariableButton.textContent = 'อัปเดตตัวแปร';
                    }
                    if (cancelVariableEditButton) {
                        cancelVariableEditButton.style.display = '';
                    }
                };

                // ฟังก์ชันย่อย: syncVariableChips
                const syncVariableChips = () => {
                    if (!variableChips) {
                        return;
                    }
                    variableChips.innerHTML = '';

                    const creditsRow = document.createElement('div');
                    creditsRow.className = 'workload-variable-chip-row';
                    variableChips.appendChild(creditsRow);

                    const workloadRow = document.createElement('div');
                    workloadRow.className = 'workload-variable-chip-row';
                    variableChips.appendChild(workloadRow);

                    const builtInVariables = [
                        { label: 'หน่วยกิตรวม', value: 'credits' },
                        { label: 'หน่วยกิตบรรยาย', value: 'lecture_credits' },
                        { label: 'หน่วยกิตปฏิบัติ', value: 'lab_credits' },
                        { label: 'หน่วยกิตศึกษาด้วยตนเอง', value: 'self_study_credits' },
                    ];

                    builtInVariables.forEach((variable) => {
                        const chip = document.createElement('button');
                        chip.className = 'chip';
                        chip.type = 'button';
                        chip.textContent = variable.label;
                        chip.dataset.value = variable.value;
                        chip.addEventListener('click', () => {
                            if (formulaText) {
                                insertToken(formulaText, variable.value);
                                markDirty();
                            }
                        });
                        creditsRow.appendChild(chip);
                    });

                    const itemRows = card.querySelectorAll('.workload-item-row');
                    if (!itemRows.length) {
                        if (creditsRow.children.length > 0) {
                            return;
                        }
                        const emptyChip = document.createElement('button');
                        emptyChip.className = 'chip chip-muted';
                        emptyChip.type = 'button';
                        emptyChip.disabled = true;
                        emptyChip.textContent = 'ยังไม่มีตัวแปร';
                        workloadRow.appendChild(emptyChip);
                        return;
                    }

                    const valueText = 'item_star';
                    const valueLabel = 'ค่าภารงาน';
                    const chip = document.createElement('button');
                    chip.className = 'chip';
                    chip.type = 'button';
                    chip.textContent = valueLabel;
                    chip.dataset.value = valueText;
                    chip.addEventListener('click', () => {
                        if (formulaText) {
                            insertToken(formulaText, valueText);
                            markDirty();
                        }
                    });
                    workloadRow.appendChild(chip);
                };

                // ฟังก์ชันย่อย: addFormulaItem
                const addFormulaItem = (label, variableName, fieldType, note = '', defaultValue = '') => {
                    if (!formulaList) {
                        return;
                    }
                    const row = document.createElement('div');
                    row.className = 'formula-item';
                    row.dataset.fieldType = fieldType || 'input';
                    row.dataset.note = note || '';
                    row.dataset.defaultValue = defaultValue || '';

                    const meta = document.createElement('div');
                    meta.className = 'formula-item-meta';

                    const labelSpan = document.createElement('span');
                    labelSpan.textContent = label || 'ตัวแปร';

                    labelSpan.className = 'formula-item-label';
                    meta.appendChild(labelSpan);

                    if (note) {
                        const noteSpan = document.createElement('span');
                        noteSpan.className = 'formula-item-note';
                        noteSpan.textContent = note;
                        meta.appendChild(noteSpan);
                    }

                    if (defaultValue !== '') {
                        const defaultValueSpan = document.createElement('span');
                        defaultValueSpan.className = 'formula-item-default';
                        defaultValueSpan.textContent = `ค่าเริ่มต้น: ${defaultValue}`;
                        meta.appendChild(defaultValueSpan);
                    }

                    const valueSpan = document.createElement('span');
                    valueSpan.className = 'formula-value';
                    valueSpan.textContent = variableName || '';

                    const editButton = document.createElement('button');
                    editButton.className = 'icon-btn workload-variable-edit';
                    editButton.type = 'button';
                    editButton.textContent = '✎';
                    editButton.addEventListener('click', (event) => {
                        event.stopPropagation();
                        enterVariableEditMode(row);
                    });

                    const button = document.createElement('button');
                    button.className = 'icon-btn is-danger workload-variable-remove';
                    button.type = 'button';
                    button.textContent = '×';
                    button.addEventListener('click', () => {
                        if (editingFormulaRow === row) {
                            resetVariableForm();
                        }
                        row.remove();
                        syncVariableChips();
                        markDirty();
                    });

                    row.appendChild(meta);
                    row.appendChild(valueSpan);
                    row.appendChild(editButton);
                    row.appendChild(button);
                    row.addEventListener('click', (event) => {
                        if (event.target.closest('.workload-variable-remove') || event.target.closest('.workload-variable-edit')) {
                            return;
                        }
                        if (formulaText) {
                            const token = valueSpan.textContent.trim();
                            insertToken(formulaText, token);
                            markDirty();
                        }
                    });
                    formulaList.appendChild(row);
                    syncVariableChips();
                };

                // ฟังก์ชันย่อย: updateItemSequence
                const updateItemSequence = (scope) => {
                    scope.querySelectorAll('.workload-item-row').forEach((row, index) => {
                        const label = row.querySelector('.subitem-sequence-text') || row.querySelector('.subitem-label');
                        if (label) {
                            label.textContent = index + 1;
                        }
                    });
                    syncVariableChips();
                    initItemRowSort(card);
                };

                // ฟังก์ชันย่อย: getNextItemSequence
                const getNextItemSequence = () => {
                    const existingSequences = new Set();
                    if (formulaList) {
                        formulaList.querySelectorAll('.formula-item').forEach((row) => {
                            const valueEl = row.querySelector('.formula-value');
                            const valueText = valueEl ? valueEl.textContent.trim() : '';
                            const match = valueText.match(/^item_(\d+)$/i);
                            if (match) {
                                existingSequences.add(Number(match[1]));
                            }
                        });
                    }

                    const itemRows = card.querySelectorAll('.workload-item-row');
                    for (let i = 0; i < itemRows.length; i++) {
                        const sequence = i + 1;
                        if (!existingSequences.has(sequence)) {
                            return sequence;
                        }
                    }

                    return existingSequences.size + 1;
                };

                // ฟังก์ชันย่อย: getNextVariableIndex
                const getNextVariableIndex = (prefix) => {
                    let maxIndex = 0;
                    if (!formulaList) {
                        return 1;
                    }
                    const pattern = new RegExp(`^${prefix}_(\\d+)$`, 'i');
                    formulaList.querySelectorAll('.formula-item').forEach((row) => {
                        const valueEl = row.querySelector('.formula-value');
                        const valueText = valueEl ? valueEl.textContent.trim() : '';
                        const match = valueText.match(pattern);
                        if (match) {
                            const num = Number(match[1]);
                            if (!Number.isNaN(num) && num > maxIndex) {
                                maxIndex = num;
                            }
                        }
                    });
                    return maxIndex + 1;
                };

                // ฟังก์ชันย่อย: attachItemRowHandlers
                if (subitemTable && subitemTable.dataset.removeBound !== 'true') {
                    subitemTable.dataset.removeBound = 'true';
                    subitemTable.addEventListener('click', (event) => {
                        const removeBtn = event.target.closest('.workload-item-remove');
                        if (!removeBtn || !subitemTable.contains(removeBtn)) {
                            return;
                        }
                        const row = removeBtn.closest('.workload-item-row');
                        if (!row) {
                            return;
                        }
                        const message = 'Confirm deleting this item?';
                        if (!window.confirm(message)) {
                            return;
                        }
                        row.remove();
                        updateItemSequence(card);
                        markDirty();
                    });
                }

                if (addItemButton && subitemTable) {
                    addItemButton.addEventListener('click', () => {
                        const firstRow = card.querySelector('.workload-item-row');
                        if (!firstRow) {
                            return;
                        }
                        const newRow = firstRow.cloneNode(true);
                        newRow.querySelectorAll('input').forEach((input) => {
                            input.value = '';
                        });
                        subitemTable.insertBefore(newRow, subitemTable.querySelector('.subitem-actions'));
                        updateItemSequence(card);
                        markDirty();
                    });
                }

                if (addVariableButton) {
                    addVariableButton.addEventListener('click', () => {
                        const currentLabelInput = card.querySelector('.workload-variable-label');
                        const currentNoteInput = card.querySelector('.workload-variable-note');
                        const currentDefaultValueInput = card.querySelector('.workload-variable-default-value');
                        const currentTypeSelect = card.querySelector('.workload-variable-type');
                        const label = currentLabelInput ? currentLabelInput.value.trim() : '';
                        const note = currentNoteInput ? currentNoteInput.value.trim() : '';
                        const defaultValue = currentDefaultValueInput ? currentDefaultValueInput.value.trim() : '';
                        let fieldType = currentTypeSelect ? currentTypeSelect.value : '';
                        if (!label) {
                            alert('กรุณากรอกชื่อตัวแปร');
                            return;
                        }
                        if (!fieldType) {
                            fieldType = 'number';
                        }

                        if (editingFormulaRow) {
                            const labelEl = editingFormulaRow.querySelector('.formula-item-label');
                            const noteEl = editingFormulaRow.querySelector('.formula-item-note');
                            const defaultValueEl = editingFormulaRow.querySelector('.formula-item-default');
                            if (labelEl) {
                                labelEl.textContent = label;
                            }
                            editingFormulaRow.dataset.fieldType = fieldType;
                            editingFormulaRow.dataset.note = note;
                            editingFormulaRow.dataset.defaultValue = defaultValue;

                            if (note) {
                                if (noteEl) {
                                    noteEl.textContent = note;
                                } else {
                                    const newNoteEl = document.createElement('span');
                                    newNoteEl.className = 'formula-item-note';
                                    newNoteEl.textContent = note;
                                    editingFormulaRow.querySelector('.formula-item-meta')?.appendChild(newNoteEl);
                                }
                            } else if (noteEl) {
                                noteEl.remove();
                            }

                            if (defaultValue !== '') {
                                if (defaultValueEl) {
                                    defaultValueEl.textContent = `ค่าเริ่มต้น: ${defaultValue}`;
                                } else {
                                    const newDefaultValueEl = document.createElement('span');
                                    newDefaultValueEl.className = 'formula-item-default';
                                    newDefaultValueEl.textContent = `ค่าเริ่มต้น: ${defaultValue}`;
                                    editingFormulaRow.querySelector('.formula-item-meta')?.appendChild(newDefaultValueEl);
                                }
                            } else if (defaultValueEl) {
                                defaultValueEl.remove();
                            }

                            resetVariableForm();
                            markDirty();
                            return;
                        }

                        const variableName = fieldType === 'number'
                            ? `num_${getNextVariableIndex('num')}`
                            : fieldType === 'item'
                                ? `item_${getNextItemSequence()}`
                                : `text_${getNextVariableIndex('text')}`;
                        addFormulaItem(label, variableName, fieldType, note, defaultValue);
                        resetVariableForm();
                        markDirty();
                    });
                }

                if (cancelVariableEditButton) {
                    cancelVariableEditButton.addEventListener('click', () => {
                        resetVariableForm();
                    });
                }

                card.querySelectorAll('.workload-variable-remove').forEach((btn) => {
                    btn.addEventListener('click', () => {
                        const row = btn.closest('.formula-item');
                        if (row) {
                            row.remove();
                            syncVariableChips();
                            markDirty();
                        }
                    });
                });

                card.querySelectorAll('.formula-toolbar .chip').forEach((chip) => {
                    chip.addEventListener('click', () => {
                        if (!formulaText) {
                            return;
                        }
                        const token = chip.textContent.trim();
                        insertToken(formulaText, token);
                        markDirty();
                    });
                });

                updateItemSequence(card);
                syncVariableChips();
                initItemRowSort(card);

                card.__workload = {
                    addFormulaItem,
                    syncVariableChips,
                    updateItemSequence,
                };
                card.dataset.initialized = 'true';
            };

            // ฟังก์ชันย่อย: sanitizeClonedSubCard
            const sanitizeClonedSubCard = (card, resetInit = false) => {
                card.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
                card.querySelectorAll('[data-bound]').forEach((node) => delete node.dataset.bound);
                card.querySelectorAll('[data-remove-bound]').forEach((node) => delete node.dataset.removeBound);
                card.querySelectorAll('[data-drag-bound]').forEach((node) => delete node.dataset.dragBound);
                card.querySelectorAll('[data-sortable-bound]').forEach((node) => delete node.dataset.sortableBound);
                card.querySelectorAll('[data-expand-bound]').forEach((node) => delete node.dataset.expandBound);
                if (resetInit) {
                    card.dataset.initialized = 'false';
                }
                card.dataset.itemId = '';
                card.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                card.querySelectorAll('textarea').forEach((textarea) => {
                    textarea.value = '';
                });
                const formulaList = card.querySelector('.formula-list');
                if (formulaList) {
                    formulaList.innerHTML = '';
                }
                const variableChips = card.querySelector('.workload-variable-chips');
                if (variableChips) {
                    variableChips.innerHTML = '';
                    const emptyChip = document.createElement('button');
                    emptyChip.className = 'chip chip-muted';
                    emptyChip.type = 'button';
                    emptyChip.disabled = true;
                    emptyChip.textContent = 'ยังไม่มีตัวแปร';
                    variableChips.appendChild(emptyChip);
                }
                syncSubCardSummary(card);
            };

            // ฟังก์ชันย่อย: sanitizeClonedMainCard
            const sanitizeClonedMainCard = (card, resetInit = false) => {
                if (!card) {
                    return;
                }
                card.classList.remove('is-collapsed');
                card.querySelectorAll('[id]').forEach((node) => node.removeAttribute('id'));
                card.querySelectorAll('[data-bound]').forEach((node) => delete node.dataset.bound);
                card.querySelectorAll('[data-remove-bound]').forEach((node) => delete node.dataset.removeBound);
                card.querySelectorAll('[data-drag-bound]').forEach((node) => delete node.dataset.dragBound);
                card.querySelectorAll('[data-sortable-bound]').forEach((node) => delete node.dataset.sortableBound);
                card.querySelectorAll('[data-expand-bound]').forEach((node) => delete node.dataset.expandBound);
                if (resetInit) {
                    card.dataset.mainInitialized = 'false';
                }
                card.dataset.groupId = '';
                card.querySelectorAll('input').forEach((input) => {
                    input.value = '';
                });
                card.querySelectorAll('textarea').forEach((textarea) => {
                    textarea.value = '';
                });
                card.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                    if (index === 0) {
                        sanitizeClonedSubCard(subCard, true);
                    } else {
                        subCard.remove();
                    }
                });
                syncMainCardSummary(card);
            };

            // ฟังก์ชันย่อย: addMainCard
            const addMainCard = () => {
                if (!mainContainer) {
                    return;
                }
                const template = document.querySelector('.workload-card');
                if (!template) {
                    return;
                }
                const clone = template.cloneNode(true);
                sanitizeClonedMainCard(clone, true);
                if (mainContainer) {
                    mainContainer.appendChild(clone);
                }
                initMainCard(clone);
                updateMainSequences();
                updateSubSequences(clone);
                markDirty();
            };

            // ผูกพฤติกรรมให้การ์ดหมวดหลัก
            // ฟังก์ชันย่อย: initMainCard
            const initMainCard = (card) => {
                if (!card) {
                    return;
                }
                if (card.dataset.mainInitialized === 'true') {
                    return;
                }
                bindCardActions(card, '.card-head .card-actions', {
                    closestSelector: '.workload-card',
                    onDelete: (target) => {
                        const cards = mainContainer
                            ? mainContainer.querySelectorAll('.workload-card')
                            : document.querySelectorAll('.workload-card');
                        if (cards.length > 1) {
                            target.remove();
                        } else {
                            sanitizeClonedMainCard(target);
                        }
                        updateMainSequences();
                        updateSubSequences(target);
                        toggleCollapsed(target, false);
                        markDirty();
                    },
                });
                initMainCardSort(card);

                const subBlock = card.querySelector('.sub-block');
                const subFooter = card.querySelector('.sub-footer');
                const addSubButton = card.querySelector('.workload-add-sub');
                const addMainButton = card.querySelector('.workload-add-main');
                const mainNameInput = card.querySelector('.workload-main-category');

                syncMainCardSummary(card);
                if (mainNameInput && mainNameInput.dataset.summaryBound !== 'true') {
                    mainNameInput.dataset.summaryBound = 'true';
                    mainNameInput.addEventListener('input', () => {
                        syncMainCardSummary(card);
                    });
                }

                card.querySelectorAll('.workload-sub-card').forEach((subCard) => {
                    initSubCard(subCard);
                    initSubCardSort(card, subCard);
                });

                if (addSubButton) {
                    addSubButton.addEventListener('click', () => {
                        if (!subBlock || !subFooter) {
                            return;
                        }
                        const firstCard = subBlock.querySelector('.workload-sub-card');
                        if (!firstCard) {
                            return;
                        }
                        const clone = firstCard.cloneNode(true);
                        sanitizeClonedSubCard(clone, true);
                        subBlock.insertBefore(clone, subFooter);
                        initSubCard(clone);
                        initSubCardSort(card, clone);
                        updateSubSequences(card);
                        markDirty();
                    });
                }

                if (addMainButton) {
                    addMainButton.addEventListener('click', () => {
                        addMainCard();
                    });
                }

                card.dataset.mainInitialized = 'true';
            };

            document.querySelectorAll('.workload-card').forEach((card) => {
                initMainCard(card);
            });
            updateMainSequences();
            if (mainCard) {
                updateSubSequences(mainCard);
            }

            // เติมข้อมูลหมวดย่อยและรายการภาระงานจาก API
            // ฟังก์ชันย่อย: populateItems
            const populateItems = (card, items) => {
                const subBlock = card ? card.querySelector('.sub-block') : null;
                const subFooter = card ? card.querySelector('.sub-footer') : null;
                if (!subBlock || !subFooter || !Array.isArray(items)) {
                    return;
                }
                const template = subBlock.querySelector('.workload-sub-card');
                if (!template) {
                    return;
                }
                subBlock.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                    if (index > 0) {
                        subCard.remove();
                    }
                });

                items.forEach((block, index) => {
                    const subCard = index === 0 ? template : template.cloneNode(true);
                    sanitizeClonedSubCard(subCard, index > 0);
                    if (index > 0) {
                        subBlock.insertBefore(subCard, subFooter);
                    }
                    if (!subCard.__workload) {
                        initSubCard(subCard);
                    }
                    initSubCardSort(card, subCard);

                    if (block?.item?.id) {
                        subCard.dataset.itemId = block.item.id;
                    }
                    const seqDisplay = subCard.querySelector('.workload-sub-sequence-display');
                    const seqInput = subCard.querySelector('.workload-sub-sequence');
                    if (seqDisplay && block?.item?.sequence) {
                        seqDisplay.textContent = block.item.sequence;
                    }
                    if (seqInput && block?.item?.sequence) {
                        seqInput.value = block.item.sequence;
                    }
                    const subNameInput = subCard.querySelector('.workload-sub-category');
                    if (subNameInput && block?.item?.name) {
                        subNameInput.value = block.item.name;
                    }
                    syncSubCardSummary(subCard);

                    const form = block?.form;
                    const api = subCard.__workload;
                    const cardFormulaText = subCard.querySelector('.workload-formula-text');
                    const cardFormulaList = subCard.querySelector('.workload-formula-list');
                    const cardSubitemTable = subCard.querySelector('.subitem-table');
                    if (cardFormulaText && form?.formula_logic) {
                        cardFormulaText.value = form.formula_logic;
                    }
                    if (cardFormulaList && Array.isArray(form?.fields) && api?.addFormulaItem) {
                        cardFormulaList.innerHTML = '';
                        form.fields.forEach((field, idx) => {
                            api.addFormulaItem(
                                field.label || `ตัวแปร ${idx + 1}`,
                                field.variable_name || `input_${idx + 1}`,
                                field.field_type || 'input',
                                field.note || '',
                                field.default_value || ''
                            );
                        });
                        api.syncVariableChips();
                    }
                    if (cardSubitemTable && Array.isArray(form?.items)) {
                        const firstRow = subCard.querySelector('.workload-item-row');
                        if (firstRow) {
                            subCard.querySelectorAll('.workload-item-row').forEach((row, idx) => {
                                if (idx > 0) {
                                    row.remove();
                                }
                            });
                            form.items.forEach((item, idx) => {
                                const row = idx === 0 ? firstRow : firstRow.cloneNode(true);
                                row.querySelector('.workload-item-name').value = item.label || '';
                                const scoreValue = item.score ?? '';
                                row.querySelector('.workload-item-score').value = scoreValue === ''
                                    ? ''
                                    : Number(scoreValue);
                                if (idx > 0) {
                                    cardSubitemTable.insertBefore(row, cardSubitemTable.querySelector('.subitem-actions'));
                                }
                            });
                            api?.updateItemSequence(subCard);
                        }
                    }
                });
                updateSubSequences(card);
            };

            // เติมข้อมูลหมวดหลักจาก API
            // ฟังก์ชันย่อย: populateGroups
            const populateGroups = (groups) => {
                if (!mainContainer || !Array.isArray(groups)) {
                    return;
                }
                const template = document.querySelector('.workload-card');
                if (!template) {
                    return;
                }

                mainContainer.querySelectorAll('.workload-card').forEach((card, index) => {
                    if (index > 0) {
                        card.remove();
                    }
                });

                groups.forEach((groupBlock, index) => {
                    const card = index === 0 ? template : template.cloneNode(true);
                    sanitizeClonedMainCard(card, index > 0);
                    if (index > 0) {
                        mainContainer.appendChild(card);
                    }
                    initMainCard(card);

                    if (groupBlock?.group?.id) {
                        card.dataset.groupId = groupBlock.group.id;
                    }
                    const mainNameInput = card.querySelector('.workload-main-category');
                    if (mainNameInput && groupBlock?.group?.name) {
                        mainNameInput.value = groupBlock.group.name;
                    }
                    syncMainCardSummary(card);
                    const mainSeqDisplay = card.querySelector('.workload-main-sequence-display');
                    const mainSeqInput = card.querySelector('.workload-main-sequence');
                    if (mainSeqDisplay && groupBlock?.group?.sequence) {
                        mainSeqDisplay.textContent = groupBlock.group.sequence;
                    }
                    if (mainSeqInput && groupBlock?.group?.sequence) {
                        mainSeqInput.value = groupBlock.group.sequence;
                    }

                    populateItems(card, groupBlock.items || []);
                });

                updateMainSequences();
                resetDirtyState();
            };

            // โหลดข้อมูลหัวข้อ/เมนูซ้าย
            fetch(`/workload-quantity-sub-criterias?quant_sub_criteria_id=${encodeURIComponent(quantSubCriteriaId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then((response) => response.json())
                .then((data) => {
                    if (!data || !data.active) {
                        return;
                    }

                    renderNav(data.items || [], data.active.id);
                    if (data.active) {
                        updateBackLink(data.active.criteria_version_id || data.active.id);
                    }

                    const mainTitle = data.active.main_criteria_name || data.active.name || '';
                    if (sectionTitle && !sectionTitle.textContent && mainTitle) {
                        sectionTitle.textContent = mainTitle;
                    }
                })
                .catch(() => {
                    // Ignore load errors for now.
                });

            // โหลดโครงสร้างหมวดหลักและหมวดย่อย
            fetch(`/workload-sub-blocks?quant_sub_criteria_id=${encodeURIComponent(quantSubCriteriaId)}`, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                    credentials: 'same-origin',
                })
                .then((response) => response.json())
                .then((data) => {
                    if (data && Array.isArray(data.groups)) {
                        populateGroups(data.groups);
                    }
                })
                .catch(() => {
                    // Ignore load errors for now.
                });

            const toastEl = document.getElementById('workload-toast');
            const toastTextEl = document.getElementById('workload-toast-text');

            // แสดง toast แจ้งสถานะการทำงาน
            // ฟังก์ชันย่อย: showWorkloadToast
            const showWorkloadToast = (message, type = 'success') => {
                if (!toastEl || !toastTextEl) {
                    return;
                }
                toastTextEl.textContent = message;
                toastEl.classList.remove('is-danger');
                if (type === 'danger') {
                    toastEl.classList.add('is-danger');
                }
                toastEl.classList.add('is-visible');
                clearTimeout(showWorkloadToast._timer);
                showWorkloadToast._timer = setTimeout(() => {
                    toastEl.classList.remove('is-visible');
                }, 4000);
            };

            if (toastEl) {
                const closeBtn = toastEl.querySelector('.workload-toast-close');
                if (closeBtn) {
                    closeBtn.addEventListener('click', () => {
                        toastEl.classList.remove('is-visible');
                    });
                }
            }

            // ปุ่มรีเซ็ตค่า
            if (resetButton) {
                resetButton.addEventListener('click', () => {
                    if (!mainContainer) {
                        return;
                    }
                    const cards = mainContainer.querySelectorAll('.workload-card');
                    if (!cards.length) {
                        return;
                    }
                    cards.forEach((card, index) => {
                        if (index === 0) {
                            sanitizeClonedMainCard(card, true);
                            initMainCard(card);
                            updateSubSequences(card);
                        } else {
                            card.remove();
                        }
                    });
                    updateMainSequences();
                    markDirty();
                    showWorkloadToast('รีเซ็ตค่าเรียบร้อย');
                });
            }

            // ปุ่มบันทึกข้อมูล
            if (saveButton) {
                saveButton.addEventListener('click', () => {
                    isSubmitting = true;
                    updateFloatingSaveButton();
                    const groups = [];
                    document.querySelectorAll('.workload-card').forEach((card, groupIndex) => {
                        const items = [];
                        card.querySelectorAll('.workload-sub-card').forEach((subCard, index) => {
                            const formItems = [];
                            subCard.querySelectorAll('.workload-item-row').forEach((row, rowIndex) => {
                                const nameInput = row.querySelector('.workload-item-name');
                                const scoreInput = row.querySelector('.workload-item-score');
                                const label = nameInput ? nameInput.value.trim() : '';
                                const score = scoreInput ? scoreInput.value : '';
                                if (label) {
                                    formItems.push({
                                        label,
                                        score: score === '' ? null : Number(score),
                                        sequence: rowIndex + 1,
                                    });
                                }
                            });

                            const fields = [];
                            subCard.querySelectorAll('.formula-item').forEach((row) => {
                                const label = row.querySelector('.formula-item-label');
                                const value = row.querySelector('.formula-value');
                                fields.push({
                                    label: label ? label.textContent.trim() : '',
                                    variable_name: value ? value.textContent.trim() : '',
                                    field_type: row.dataset.fieldType || 'input',
                                    note: row.dataset.note || '',
                                    default_value: row.dataset.defaultValue || '',
                                });
                            });

                            const subNameInput = subCard.querySelector('.workload-sub-category');
                            const sequenceInput = subCard.querySelector('.workload-sub-sequence');
                            const formulaText = subCard.querySelector('.workload-formula-text');

                            items.push({
                                id: subCard.dataset.itemId ? Number(subCard.dataset.itemId) : null,
                                item_name: subNameInput ? subNameInput.value.trim() : '',
                                sequence: sequenceInput ? Number(sequenceInput.value || 0) : (index + 1),
                                formula_logic: formulaText ? formulaText.value.trim() : '',
                                fields,
                                form_items: formItems,
                            });
                        });

                        const mainNameInput = card.querySelector('.workload-main-category');
                        const mainSequenceInput = card.querySelector('.workload-main-sequence');

                        groups.push({
                            id: card.dataset.groupId ? Number(card.dataset.groupId) : null,
                            group_name: mainNameInput ? mainNameInput.value.trim() : '',
                            sequence: mainSequenceInput
                                ? Number(mainSequenceInput.value || 0)
                                : (groupIndex + 1),
                            items,
                        });
                    });

                    fetch('/workload-config/save', {
                            method: 'POST',
                            headers: {
                                'Accept': 'application/json',
                                'Content-Type': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest',
                                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                            },
                            credentials: 'same-origin',
                            body: JSON.stringify({
                                quant_sub_criteria_id: quantSubCriteriaId,
                                groups,
                            }),
                        })
                        .then(async (response) => {
                            const data = await response.json().catch(() => ({}));
                            if (!response.ok) {
                                throw data;
                            }
                            return data;
                        })
                        .then((data) => {
                            isSubmitting = false;
                            updateFloatingSaveButton();
                            if (data && data.success !== false) {
                                resetDirtyState();
                            }
                            if (!data || data.success === false) {
                                isSubmitting = false;
                                updateFloatingSaveButton();
                                showWorkloadToast(
                                    data && data.message ? data.message : 'บันทึกไม่สำเร็จ',
                                    'danger'
                                );
                                return;
                            }
                            showWorkloadToast('บันทึกสำเร็จ');
                        })
                        .catch((error) => {
                            isSubmitting = false;
                            updateFloatingSaveButton();
                            if (error && error.errors && error.errors.formula_logic) {
                                showWorkloadToast(error.errors.formula_logic.join('\n'), 'danger');
                                return;
                            }
                            showWorkloadToast(
                                error && error.message ? error.message : 'บันทึกไม่สำเร็จ',
                                'danger'
                            );
                        });
                });
            }
        });
    </script>
