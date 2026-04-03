    function sortItemOptionsAlphabetically() {
        if (!itemSelect) {
            return;
        }

        const options = Array.from(itemSelect.options);
        const placeholderOption = options.find(function (opt) {
            return !opt.value;
        }) || null;
        const sortedOptions = options
            .filter(function (opt) {
                return !!opt.value;
            })
            .sort(function (a, b) {
                return (a.textContent || '').trim().localeCompare((b.textContent || '').trim(), 'th', {
                    numeric: true,
                    sensitivity: 'base',
                });
            });

        itemSelect.innerHTML = '';
        if (placeholderOption) {
            itemSelect.appendChild(placeholderOption);
        }
        sortedOptions.forEach(function (opt) {
            itemSelect.appendChild(opt);
        });
    }

    if (itemSelect) {
        sortItemOptionsAlphabetically();
    }

    function getActiveFormId() {
        if (formSelect) {
            return formSelect.value;
        }
        if (formIdField) {
            return formIdField.value;
        }
        return '';
    }

    function setActiveFormId(formId) {
        if (formSelect) {
            return formSelect.value = formId;
        }
        if (formIdField) {
            formIdField.value = formId;
        }
    }

    function setActiveGroup(groupId, groupName) {
        activeGroupId = groupId || '';
        activeGroupName = groupName || '';
        if (groupLabelInput) {
            groupLabelInput.value = groupName || (activeGroupId ? '\u0e2b\u0e21\u0e27\u0e14\u0e22\u0e48\u0e2d\u0e22 #' + activeGroupId : '-');
        }
    }

    function requiresSubject() {
        return !!(workloadRequireSubjectFlag && workloadRequireSubjectFlag.value === '1');
    }

    function getSelectedSubjectOption() {
        if (!subjectIdField) {
            return null;
        }

        return subjectOptions.find(function (option) {
            return option.dataset.subjectId === subjectIdField.value;
        }) || null;
    }

    function openSubjectDropdown() {
        if (!subjectDropdown || !subjectTrigger) {
            return;
        }

        subjectDropdown.hidden = false;
        subjectTrigger.setAttribute('aria-expanded', 'true');
        if (subjectSearchInput) {
            subjectSearchInput.focus();
            subjectSearchInput.select();
        }
    }

    function closeSubjectDropdown() {
        if (!subjectDropdown || !subjectTrigger) {
            return;
        }

        subjectDropdown.hidden = true;
        subjectTrigger.setAttribute('aria-expanded', 'false');
    }

    function updateSubjectTriggerText() {
        if (!subjectTriggerText) {
            return;
        }

        const selectedOption = getSelectedSubjectOption();
        subjectTriggerText.textContent = selectedOption
            ? (selectedOption.textContent || '').trim()
            : '-- \u0e40\u0e25\u0e37\u0e2d\u0e01\u0e23\u0e32\u0e22\u0e27\u0e34\u0e0a\u0e32 --';
    }

    function filterSubjectOptions() {
        if (!subjectOptions.length) {
            return;
        }

        const keyword = (subjectSearchInput ? subjectSearchInput.value : '').trim().toLowerCase();
        let visibleCount = 0;

        subjectOptions.forEach(function (option) {
            const haystack = (option.dataset.search || '') + ' ' + ((option.textContent || '').trim().toLowerCase());
            const matched = keyword === '' || haystack.includes(keyword);
            option.hidden = !matched;
            if (matched) {
                visibleCount++;
            }
        });

        if (subjectEmptyState) {
            subjectEmptyState.hidden = visibleCount > 0;
        }
    }

    function clearSubjectSelection() {
        if (subjectIdField) {
            subjectIdField.value = '';
        }
        if (subjectSearchInput) {
            subjectSearchInput.value = '';
        }
        updateSubjectTriggerText();
        filterSubjectOptions();
    }

    function selectSubjectOption(option, keepOpen) {
        if (!option || !subjectIdField) {
            return;
        }

        subjectIdField.value = option.dataset.subjectId || '';
        updateSubjectTriggerText();
        updateCreditsFromSubject();
        if (!keepOpen) {
            closeSubjectDropdown();
        }
    }

    function updateSubjectRequirementState() {
        const enabled = requiresSubject();

        if (subjectSection) {
            subjectSection.style.display = enabled ? '' : 'none';
        }

        if (subjectAlertBox) {
            subjectAlertBox.style.display = enabled ? '' : 'none';
        }

        if (subjectTrigger) {
            subjectTrigger.disabled = !enabled;
        }

        if (subjectSearchInput) {
            subjectSearchInput.disabled = !enabled;
            if (!enabled) {
                subjectSearchInput.value = '';
            }
        }

        if (subjectClearBtn) {
            subjectClearBtn.disabled = !enabled;
        }

        if (subjectIdField) {
            subjectIdField.disabled = !enabled;
            if (!enabled) {
                subjectIdField.value = '';
            }
        }

        if (!enabled) {
            updateSubjectTriggerText();
            closeSubjectDropdown();
        }

        updateWorkloadSubmitState();
    }

    function getActiveCreditsInput() {
        const formId = getActiveFormId();
        let scope = null;
        if (detailFieldsContainer && formId) {
            scope = detailFieldsContainer.querySelector('.workload-form-fields[data-form-id="' + formId + '"]');
        }
        const container = scope || document;
        const explicit = Array.from(container.querySelectorAll('input[name="field_values[credits]"]'));
        const byName = Array.from(container.querySelectorAll('input[name^="field_values["]')).filter(function (input) {
            return /credit/i.test(input.getAttribute('name'));
        });
        const byLabel = Array.from(container.querySelectorAll('.workload-modal-subfield')).map(function (field) {
            const label = field.querySelector('.workload-modal-sub-label');
            if (!label) {
                return null;
            }
            const text = (label.textContent || '').trim();
            if (!text.includes('\u0e2b\u0e19\u0e48\u0e27\u0e22\u0e01\u0e34\u0e15')) {
                return null;
            }
            return field.querySelector('input');
        }).filter(Boolean);
        const candidates = explicit.concat(byName, byLabel);
        return candidates.find(function (input) {
            return !input.disabled;
        }) || candidates[0] || null;
    }

    function getActiveFormScope() {
        const formId = getActiveFormId();
        if (detailFieldsContainer && formId) {
            return detailFieldsContainer.querySelector('.workload-form-fields[data-form-id="' + formId + '"]');
        }
        return detailFieldsContainer || document;
    }

    function findSubjectCreditInput(candidates) {
        const scope = getActiveFormScope() || document;
        const candidateList = Array.isArray(candidates) ? candidates : [candidates];

        for (const candidate of candidateList) {
            const exact = scope.querySelector('input[name="field_values[' + candidate.name + ']"]');
            if (exact && !exact.disabled) {
                return exact;
            }
        }

        const fields = Array.from(scope.querySelectorAll('.workload-modal-subfield'));
        for (const field of fields) {
            const label = field.querySelector('.workload-modal-sub-label');
            const input = field.querySelector('input');
            if (!label || !input || input.disabled) {
                continue;
            }

            const text = (label.textContent || '').trim().toLowerCase();
            const matched = candidateList.some(function (candidate) {
                return candidate.labels.some(function (keyword) {
                    return text.includes(keyword);
                });
            });

            if (matched) {
                return input;
            }
        }

        return null;
    }

    function getCreditContextText() {
        const scope = getActiveFormScope() || document;
        const parts = [];

        if (activeGroupName) {
            parts.push(activeGroupName);
        } else if (groupLabelInput && groupLabelInput.value) {
            parts.push(groupLabelInput.value);
        }

        if (itemSelect) {
            const selectedItem = itemSelect.selectedOptions[0];
            if (selectedItem) {
                parts.push(selectedItem.textContent || '');
            }
        }

        scope.querySelectorAll('.workload-modal-sub-label, .workload-modal-sub-note').forEach(function (node) {
            parts.push(node.textContent || '');
        });

        return parts.join(' ').toLowerCase();
    }

    function getPreferredSubjectCreditValue(selected) {
        if (!selected || !selected.dataset) {
            return '';
        }

        const context = getCreditContextText();
        if (context.includes('\u0e1b\u0e0f\u0e34\u0e1a\u0e31\u0e15\u0e34') || context.includes('lab')) {
            return selected.dataset.labCredits ?? '';
        }
        if (context.includes('\u0e1a\u0e23\u0e23\u0e22\u0e32\u0e22') || context.includes('lecture')) {
            return selected.dataset.lectureCredits ?? '';
        }

        return selected.dataset.lectureCredits ?? '0';
    }

    function updateCreditsFromSubject() {
        if (!subjectIdField || !requiresSubject()) {
            return;
        }
        const selected = getSelectedSubjectOption();
        if (!selected) {
            return;
        }

        const mappings = [
            {
                value: selected.dataset ? selected.dataset.lectureCredits : '',
                candidates: [
                    { name: 'lecture_credits', labels: ['\u0e1a\u0e23\u0e23\u0e22\u0e32\u0e22', 'lecture'] },
                ],
            },
            {
                value: selected.dataset ? selected.dataset.labCredits : '',
                candidates: [
                    { name: 'lab_credits', labels: ['\u0e1b\u0e0f\u0e34\u0e1a\u0e31\u0e15\u0e34', 'lab'] },
                ],
            },
            {
                value: selected.dataset ? selected.dataset.selfStudyCredits : '',
                candidates: [
                    { name: 'self_study_credits', labels: ['\u0e28\u0e36\u0e01\u0e29\u0e32\u0e14\u0e49\u0e27\u0e22\u0e15\u0e19\u0e40\u0e2d\u0e07', 'self'] },
                ],
            },
            {
                value: getPreferredSubjectCreditValue(selected),
                candidates: [
                    { name: 'credits', labels: ['\u0e2b\u0e19\u0e48\u0e27\u0e22\u0e01\u0e34\u0e15', 'credit'] },
                ],
            },
        ];

        mappings.forEach(function (mapping) {
            if (mapping.value === undefined || mapping.value === null || mapping.value === '') {
                return;
            }

            const input = findSubjectCreditInput(mapping.candidates);
            if (!input) {
                return;
            }

            const wasAuto = input.dataset ? input.dataset.autofill === 'true' : false;
            const isEmpty = input.value === '' || input.value === '1';
            if (isEmpty || wasAuto) {
                input.value = mapping.value;
                if (input.dataset) {
                    input.dataset.autofill = 'true';
                }
            }
        });
    }
