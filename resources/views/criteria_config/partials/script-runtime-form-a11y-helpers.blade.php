        let runtimeFieldIdCounter = 0;

        function ensureRuntimeFormFieldIdentifiers(formId, root = document) {
            const form = document.getElementById(formId);
            if (!form) {
                return;
            }

            const scope = root && root.querySelectorAll ? root : form;
            scope.querySelectorAll('input, select, textarea').forEach(function(field) {
                if (field.id || field.name || field.dataset.autoFieldId) {
                    return;
                }

                runtimeFieldIdCounter += 1;
                field.id = `runtime-form-field-${runtimeFieldIdCounter}`;
                field.dataset.autoFieldId = field.id;
            });

            ensureUniqueFormFieldIds(form);
            ensureRuntimeLabelAssociations(formId, form);
        }

        function ensureUniqueFormFieldIds(form) {
            const seenIds = new Set();

            form.querySelectorAll('input[id], select[id], textarea[id]').forEach(function(field) {
                const currentId = field.id;

                if (!seenIds.has(currentId)) {
                    seenIds.add(currentId);
                    return;
                }

                runtimeFieldIdCounter += 1;
                const nextId = `${currentId}-${runtimeFieldIdCounter}`;
                field.id = nextId;
                field.dataset.autoFieldId = nextId;
                seenIds.add(nextId);
            });
        }

        function ensureRuntimeLabelAssociations(formId, root = document) {
            const form = document.getElementById(formId);
            if (!form) {
                return;
            }

            const scope = root && root.querySelectorAll ? root : form;

            scope.querySelectorAll('label').forEach(function(label) {
                if (label.control) {
                    return;
                }

                let field = null;
                const forId = label.getAttribute('for');

                if (forId && window.CSS && typeof window.CSS.escape === 'function') {
                    field = form.querySelector(`#${CSS.escape(forId)}`);
                } else if (forId) {
                    field = form.querySelector(`#${forId}`);
                }

                if (!field) {
                    field = label.querySelector('input:not([type="hidden"]), select, textarea');
                }

                if (!field && label.parentElement) {
                    field = label.parentElement.querySelector('input:not([type="hidden"]), select, textarea');
                }

                if (field) {
                    if (!field.id) {
                        runtimeFieldIdCounter += 1;
                        field.id = `runtime-form-field-${runtimeFieldIdCounter}`;
                        field.dataset.autoFieldId = field.id;
                    }

                    label.setAttribute('for', field.id);
                    return;
                }

                const replacement = document.createElement('div');
                Array.from(label.attributes).forEach(function(attribute) {
                    if (attribute.name !== 'for') {
                        replacement.setAttribute(attribute.name, attribute.value);
                    }
                });
                replacement.innerHTML = label.innerHTML;
                label.replaceWith(replacement);
            });
        }

        function observeRuntimeFormFields(formId) {
            const form = document.getElementById(formId);
            if (!form || !window.MutationObserver) {
                return;
            }

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            ensureRuntimeFormFieldIdentifiers(formId, node);
                            ensureRuntimeLabelAssociations(formId, node);
                        }
                    });
                });
            });

            observer.observe(form, {
                childList: true,
                subtree: true,
            });

            ensureRuntimeFormFieldIdentifiers(formId, form);
            ensureRuntimeLabelAssociations(formId, form);
        }
