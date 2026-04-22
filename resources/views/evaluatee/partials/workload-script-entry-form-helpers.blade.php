    function updateFormFields() {
        const formId = getActiveFormId();
        formFieldBlocks.forEach(function (block) {
            const isActive = block.dataset.formId === formId;
            block.style.display = isActive ? 'block' : 'none';
            block.querySelectorAll('input, select, textarea').forEach(function (input) {
                input.disabled = !isActive;
                if (input.type !== 'hidden') {
                    input.required = isActive;
                }
            });
        });

        if (!itemSelect) {
            return;
        }

        const options = Array.from(itemSelect.options);
        options.forEach(function (opt) {
            if (!opt.value) {
                opt.hidden = false;
                opt.disabled = false;
                return;
            }
            const matchForm = !formId || opt.dataset.formId === formId;
            const matchGroup = !activeGroupId || opt.dataset.groupId === activeGroupId;
            const match = matchForm && matchGroup;
            opt.hidden = !match;
            opt.disabled = !match;
        });

        if (itemSelect.value) {
            const selected = itemSelect.selectedOptions[0];
            if (selected && (!selected.dataset.formId || selected.dataset.formId !== formId || (activeGroupId && selected.dataset.groupId !== activeGroupId))) {
                itemSelect.value = '';
            }
        }
        updateSelectedItemField();
        updateCreditsFromSubject();
        updateWorkloadSubmitState();
    }

    function updateSelectedItemField() {
        if (!hiddenItemField) {
            return;
        }
        if (!itemSelect || !itemSelect.value) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        const selected = itemSelect.selectedOptions[0];
        if (!selected) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        const variableName = selected.dataset.variableName || (selected.dataset.sequence ? 'item_' + selected.dataset.sequence : '');
        if (!variableName) {
            hiddenItemField.removeAttribute('name');
            hiddenItemField.value = '';
            return;
        }
        hiddenItemField.setAttribute('name', 'field_values[' + variableName + ']');
        hiddenItemField.value = selected.dataset.score || '';
    }

    function setFormMode(mode, entryId) {
        if (!workloadForm) {
            return;
        }
        const storeUrl = workloadForm.dataset.storeUrl || workloadForm.getAttribute('action');
        const updateUrlTemplate = workloadForm.dataset.updateUrl || '';

        if (mode === 'edit' && entryId) {
            if (updateUrlTemplate) {
                workloadForm.setAttribute('action', updateUrlTemplate.replace('__id__', entryId));
            }
            if (methodField) {
                methodField.value = 'PUT';
                methodField.setAttribute('name', '_method');
            }
            if (modalTitle) {
                modalTitle.textContent = 'แก้ไขข้อมูลภาระงาน';
            }
            return;
        }

        workloadForm.setAttribute('action', storeUrl);
        if (methodField) {
            methodField.value = '';
            methodField.removeAttribute('name');
        }
        if (modalTitle) {
            modalTitle.textContent = 'เพิ่มข้อมูลภาระงาน';
        }
    }

    function fillFields(values, formId, clearAll) {
        const container = detailFieldsContainer || workloadForm;
        if (!container) {
            return;
        }
        const scope = formId
            ? container.querySelectorAll('.workload-form-fields[data-form-id="' + formId + '"]')
            : container.querySelectorAll('.workload-form-fields');
        const blocks = scope.length ? Array.from(scope) : [container];
        const normalized = {};
        if (values && typeof values === 'object') {
            Object.keys(values).forEach(function (key) {
                const raw = String(key);
                const keys = [raw, raw.toLowerCase(), raw.trim(), raw.trim().replace(/\s+/g, '')];
                keys.forEach(function (k) {
                    normalized[k] = values[key];
                });
            });
        }
        const hasExplicitValues = Object.keys(normalized).length > 0;
        const escapeCss = function (value) {
            if (window.CSS && typeof window.CSS.escape === 'function') {
                return window.CSS.escape(value);
            }
            return value.replace(/([\\!"#$%&'()*+,.\/:;<=>?@\[\\\]^`{|}~])/g, '\\$1');
        };
        blocks.forEach(function (block) {
            const inputs = Array.from(block.querySelectorAll('input[name^="field_values["]')).filter(function (input) {
                return input.id !== 'selectedItemField';
            });
            if (clearAll) {
                inputs.forEach(function (input) {
                    input.value = hasExplicitValues ? '' : (input.dataset.defaultValue || '');
                });
            }
            inputs.forEach(function (input) {
                const match = input.getAttribute('name').match(/^field_values\\[(.+)\\]$/);
                const key = match ? String(match[1]) : '';
                if (key && Object.prototype.hasOwnProperty.call(normalized, key)) {
                    input.value = normalized[key];
                }
            });
            if (values && typeof values === 'object') {
                Object.keys(values).forEach(function (key) {
                    const selector = 'input[name="' + escapeCss('field_values[' + key + ']') + '"]';
                    block.querySelectorAll(selector).forEach(function (input) {
                        input.value = values[key];
                    });
                });
            }
        });
    }

    function setEvidenceLinks(links) {
        if (!evidenceContainer) {
            return;
        }
        evidenceContainer.innerHTML = '';
        const values = Array.isArray(links) && links.length ? links : [''];
        values.forEach(function (link) {
            const row = document.createElement('div');
            row.className = 'workload-evidence-row';
            row.innerHTML = `
                <input type="text" class="workload-modal-input" name="evidence_links[]" placeholder="ใส่ลิงก์หลักฐานสำหรับรายการนี้" />
                <button type="button" class="workload-evidence-remove-btn" title="ลบลิงก์">ลบ</button>
            `;
            const input = row.querySelector('input');
            if (input) {
                input.value = link || '';
            }
            evidenceContainer.appendChild(row);
        });

        const rows = evidenceContainer.querySelectorAll('.workload-evidence-row');
        rows.forEach(function (row) {
            const removeBtn = row.querySelector('.workload-evidence-remove-btn');
            if (removeBtn) {
                removeBtn.disabled = rows.length === 1;
            }
        });
    }

    function parseDatasetJson(value) {
        if (!value) {
            return null;
        }
        let rawValue = value;
        if (typeof rawValue === 'string') {
            rawValue = rawValue.trim();
            if ((rawValue.startsWith("'") && rawValue.endsWith("'")) || (rawValue.startsWith('"') && rawValue.endsWith('"'))) {
                rawValue = rawValue.slice(1, -1);
            }
        }
        try {
            return JSON.parse(rawValue);
        } catch (error) {
            const textarea = document.createElement('textarea');
            textarea.innerHTML = rawValue;
            const decoded = textarea.value;
            let cleaned = decoded.trim();
            if ((cleaned.startsWith("'") && cleaned.endsWith("'")) || (cleaned.startsWith('"') && cleaned.endsWith('"'))) {
                cleaned = cleaned.slice(1, -1);
            }
            try {
                return JSON.parse(cleaned);
            } catch (innerError) {
                return null;
            }
        }
    }

    function selectDefaultForForm(formId) {
        if (formId) {
            setActiveFormId(formId);
        } else if (formSelect && !formSelect.value) {
            const firstForm = Array.from(formSelect.options).find(function (opt) {
                return opt.value && !opt.disabled;
            });
            if (firstForm) {
                setActiveFormId(firstForm.value);
            }
        } else if (!formSelect && itemSelect) {
            const firstItem = Array.from(itemSelect.options).find(function (opt) {
                return opt.value && opt.dataset.formId;
            });
            if (firstItem) {
                setActiveFormId(firstItem.dataset.formId);
            }
        }

        if (itemSelect) {
            itemSelect.value = '';
        }

        updateFormFields();
        updateSubjectRequirementState();
    }

    function getActiveRequiredDetailInputs() {
        const formId = getActiveFormId();
        if (!detailFieldsContainer || !formId) {
            return [];
        }

        const activeBlock = detailFieldsContainer.querySelector('.workload-form-fields[data-form-id="' + formId + '"]');
        if (!activeBlock) {
            return [];
        }

        return Array.from(activeBlock.querySelectorAll('input[name^="field_values["], select[name^="field_values["], textarea[name^="field_values["]')).filter(function (input) {
            return !input.disabled && input.type !== 'hidden';
        });
    }

    function isFilledInput(input) {
        if (!input || input.disabled) {
            return true;
        }

        if (input.tagName === 'SELECT') {
            return input.value !== '';
        }

        if (input.type === 'number') {
            return input.value !== '' && !Number.isNaN(Number(input.value));
        }

        return input.value.trim() !== '';
    }

    function getMissingWorkloadFields(markInvalid) {
        const shouldMarkInvalid = !!markInvalid;
        const missing = [];

        if (itemSelect && !itemSelect.disabled && itemSelect.value === '') {
            if (shouldMarkInvalid) {
                itemSelect.classList.add('is-invalid');
            }
            missing.push('ภาระงาน');
        } else if (itemSelect) {
            itemSelect.classList.remove('is-invalid');
        }

        if (requiresSubject() && subjectIdField && !subjectIdField.disabled && subjectIdField.value === '') {
            missing.push('รายวิชา');
        }

        // ตรวจ field dynamic ในฟอร์มย่อยของภาระงานที่กำลังเลือกอยู่
        getActiveRequiredDetailInputs().forEach(function (input) {
            if (isFilledInput(input)) {
                input.classList.remove('is-invalid');
                return;
            }

            if (shouldMarkInvalid) {
                input.classList.add('is-invalid');
            }

            const field = input.closest('.workload-modal-subfield');
            const label = field ? field.querySelector('.workload-modal-sub-label') : null;
            const labelText = label ? label.textContent.trim() : (input.getAttribute('name') || 'รายละเอียด');
            if (!missing.includes(labelText)) {
                missing.push(labelText);
            }
        });

        return missing;
    }

    function updateWorkloadSubmitState() {
        if (!workloadSubmitButton) {
            return;
        }

        workloadSubmitButton.disabled = false;
    }
