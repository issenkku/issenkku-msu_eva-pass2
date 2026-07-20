<script>
        let isDirty = false;
        let isSubmitting = false;
        const unsavedChangesMessage = 'มีข้อมูลที่แก้ไขแล้วยังไม่ได้บันทึก กรุณาบันทึกก่อนออกจากหน้านี้';

        function updateFloatingSaveButton() {
            const floatingButton = document.getElementById('floating_save_button');
            if (!floatingButton) {
                return;
            }

            floatingButton.classList.toggle('hidden', !isDirty || isSubmitting);
        }

        function setDirtyState(nextState) {
            isDirty = Boolean(nextState);
            updateFloatingSaveButton();
        }

        function markDirty() {
            if (isSubmitting) {
                return;
            }

            setDirtyState(true);
        }

        function resetDirtyState() {
            setDirtyState(false);
        }

        let runtimeFieldIdCounter = 0;

        function ensureRuntimeFormFieldIdentifiers(root = document) {
            const form = document.getElementById('jsonForm');
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

        function ensureRuntimeLabelAssociations(root = document) {
            const scope = root && root.querySelectorAll ? root : document;

            scope.querySelectorAll('label').forEach(function(label) {
                if (label.control) {
                    return;
                }

                let field = null;
                const forId = label.getAttribute('for');

                if (forId && window.CSS && typeof window.CSS.escape === 'function') {
                    field = document.getElementById(forId) || document.querySelector(`#${CSS.escape(forId)}`);
                } else if (forId) {
                    field = document.getElementById(forId);
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

        function observeRuntimeFormFields() {
            const form = document.getElementById('jsonForm');
            if (!form || !window.MutationObserver) {
                return;
            }

            const observer = new MutationObserver(function(mutations) {
                mutations.forEach(function(mutation) {
                    mutation.addedNodes.forEach(function(node) {
                        if (node.nodeType === Node.ELEMENT_NODE) {
                            ensureRuntimeFormFieldIdentifiers(node);
                            ensureRuntimeLabelAssociations(node);
                        }
                    });
                });
            });

            observer.observe(form, {
                childList: true,
                subtree: true,
            });

            ensureRuntimeFormFieldIdentifiers(form);
            ensureRuntimeLabelAssociations(document);
        }

        function shouldBlockNavigation(targetUrl = '') {
            if (!isDirty || isSubmitting) {
                return false;
            }

            if (!targetUrl) {
                return true;
            }

            const normalizedTarget = targetUrl.trim();
            if (!normalizedTarget || normalizedTarget.startsWith('#') || normalizedTarget.startsWith('javascript:')) {
                return false;
            }

            return true;
        }

        function setupUnsavedChangesProtection() {
            const form = document.getElementById('jsonForm');
            const floatingSubmitButton = document.getElementById('floating_save_submit');

            if (floatingSubmitButton && form) {
                floatingSubmitButton.addEventListener('click', function() {
                    form.requestSubmit();
                });
            }

            document.addEventListener('input', function(e) {
                if (e.target.closest('#jsonForm')) {
                    markDirty();
                }
            });

            document.addEventListener('change', function(e) {
                if (e.target.closest('#jsonForm')) {
                    markDirty();
                }
            });

            $(document).on('summernote.change', '.richtext-editor', function() {
                markDirty();
            });

            window.addEventListener('beforeunload', function(e) {
                if (!shouldBlockNavigation(window.location.href)) {
                    return;
                }

                e.preventDefault();
                e.returnValue = unsavedChangesMessage;
            });

            document.addEventListener('click', function(e) {
                const link = e.target.closest('a[href]');
                if (!link) {
                    return;
                }

                const href = link.getAttribute('href') || '';
                if (!shouldBlockNavigation(href)) {
                    return;
                }

                e.preventDefault();
                alert(unsavedChangesMessage);
            }, true);
        }

        // Initialize Summernote for rich text editors
        function initializeSummernote() {
            $('.richtext-editor').each(function() {
                const $editor = $(this);
                let placeholder = 'กรุณาใส่คำอธิบายเพิ่มเติม...';
                
                // Use specific placeholder for quality sub criteria description
                if ($editor.hasClass('qual_sub_description')) {
                    placeholder = 'ใส่คำอธิบายการให้คะแนน';
                }
                
                $editor.summernote({
                    height: 250,
                    toolbar: [
                        ['style', ['style']],
                        ['font', ['bold', 'italic', 'underline', 'clear']],
                        ['fontname', ['fontname']],
                        ['color', ['color']],
                        ['para', ['ul', 'ol', 'paragraph']],
                        ['table', ['table']],
                        ['insert', ['link', 'picture']],
                        ['view', ['fullscreen', 'codeview', 'help']]
                    ],
                    placeholder: placeholder,
                    lang: 'th-TH',
                    callbacks: {
                        onChange: function(contents, $editable) {
                            // Update the textarea value when content changes
                            $(this).val(contents);
                        }
                    }
                });

                const noteEditor = $editor.next('.note-editor').get(0);
                if (noteEditor) {
                    ensureRuntimeFormFieldIdentifiers(noteEditor);
                    ensureRuntimeLabelAssociations(noteEditor);
                }
            });
        }

        // Initialize Summernote when document is ready
        $(document).ready(function() {
            setupUnsavedChangesProtection();
            observeRuntimeFormFields();
            setTimeout(function() {
                initializeSummernote();
                ensureRuntimeFormFieldIdentifiers();
                ensureRuntimeLabelAssociations(document);
            }, 100);
        });

        function cloneAndClear(blockSelector) {
            let node = document.querySelector(blockSelector).cloneNode(true);
            
            // Destroy Summernote instances from cloned node
            $(node).find('.richtext-editor').each(function() {
                const $editor = $(this);
                
                // Remove Summernote wrapper if it exists
                if ($editor.parent().hasClass('note-editor')) {
                    // Get the original textarea
                    const content = $editor.summernote('code');
                    $editor.summernote('destroy');
                    $editor.val(''); // Clear content after destroying
                } else if ($editor.next().hasClass('note-editor')) {
                    // Handle case where editor wrapper is a sibling
                    $editor.next('.note-editor').remove();
                    $editor.val('');
                }
                
                // Remove any remaining note-editor wrappers
                $(this).siblings('.note-editor').remove();
                $(this).parent('.note-editor').children('textarea').unwrap();
                
                // Generate new unique ID for cloned editor
                const newId = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                this.id = newId;
                this.value = ''; // Clear content
                
                // Remove any Summernote classes
                $(this).removeClass('note-editor note-frame note-airframe');
            });
            
            node.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
            node.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
            node.querySelectorAll('.support_criteria_id').forEach(input => input.value = '');
            node.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
            node.querySelectorAll('textarea.quant_formula').forEach(textarea => textarea.value = 'D = A × C / B');
            node.querySelectorAll(
                '.evaluation_list_block:not(:first-child), .quant_criteria_block:not(:first-child), .qual_criteria_block:not(:first-child), .quant_sub_criteria_block:not(:first-child), .qual_sub_criteria_block:not(:first-child), .support_criteria_block:not(:first-child)'
            ).forEach(e => e.remove());

            if (blockSelector === '.evaluation_list_block') {
                const container = document.querySelector('.evaluation_lists_container');
                const index = container.querySelectorAll('.evaluation_list_block').length + 1;
                setSequenceInputValue(node.querySelector('.eval_sequence'), index);
                node.querySelector('.quantity_main_criterias_container').classList.add('hidden');
                node.querySelector('.quality_main_criterias_container').classList.add('hidden');
                node.querySelector('.support_criterias_container').classList.add('hidden');
            }
            if (blockSelector === '.category_block') {
                const container = document.getElementById('categories_container');
                const index = container.querySelectorAll('.category_block').length + 1;
                setSequenceInputValue(node.querySelector('.category_sequence'), index);
            }
            
            return node;
        }

        function updateButtonStates(containerSelector, upBtnSelector, downBtnSelector) {
            const items = document.querySelectorAll(containerSelector);
            items.forEach((item, index) => {
                const upBtn = item.querySelector(upBtnSelector);
                const downBtn = item.querySelector(downBtnSelector);
                upBtn.disabled = index === 0;
                downBtn.disabled = index === items.length - 1;
            });
        }

        function bindSequenceInputs() {}

        function setSequenceInputValue(input, value) {
            if (!input) {
                return;
            }

            input.textContent = String(value);
        }

        function getSequenceValue(block, selector, fallback) {
            const input = block.querySelector(selector);
            return (input?.textContent || '').trim() || String(fallback);
        }

        function parseSequenceValue(value) {
            return String(value || '')
                .split('.')
                .map((part) => {
                    const parsed = Number(part);
                    return Number.isFinite(parsed) ? parsed : Number.MAX_SAFE_INTEGER;
                });
        }

        function compareSequenceValues(left, right) {
            const leftParts = parseSequenceValue(left);
            const rightParts = parseSequenceValue(right);
            const maxLength = Math.max(leftParts.length, rightParts.length);

            for (let index = 0; index < maxLength; index++) {
                const leftPart = leftParts[index] ?? -1;
                const rightPart = rightParts[index] ?? -1;
                if (leftPart !== rightPart) {
                    return leftPart - rightPart;
                }
            }

            return String(left).localeCompare(String(right), undefined, { numeric: true });
        }

        function updateEvalSequence(container, categoryPrefix = '') {
            container.querySelectorAll('.evaluation_list_block').forEach((evalBlock, index) => {
                const evalPrefix = categoryPrefix ? `${categoryPrefix}.${index + 1}` : `${index + 1}`;
                setSequenceInputValue(evalBlock.querySelector('.eval_sequence'), evalPrefix);

                const quantityContainer = evalBlock.querySelector('.quantity_main_criterias_container');
                const qualityContainer = evalBlock.querySelector('.quality_main_criterias_container');
                const supportContainer = evalBlock.querySelector('.support_criterias_container');
                if (quantityContainer) {
                    updateQuantMainSequence(quantityContainer, evalPrefix);
                }
                if (qualityContainer) {
                    updateQualMainSequence(qualityContainer, evalPrefix);
                }
                if (supportContainer) {
                    updateSupportSequence(supportContainer, evalPrefix);
                }
            });
        }

        function updateCategorySequence(container) {
            container.querySelectorAll('.category_block').forEach((catBlock, index) => {
                const categoryPrefix = `${index + 1}`;
                setSequenceInputValue(catBlock.querySelector('.category_sequence'), categoryPrefix);

                const evalContainer = catBlock.querySelector('.evaluation_lists_container');
                if (evalContainer) {
                    updateEvalSequence(evalContainer, categoryPrefix);
                }
            });
        }

        function updateQuantMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.quant_criteria_block').forEach((block, idx) => {
                const quantPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_main_sequence'), quantPrefix);

                const subContainer = block.querySelector('.quant_sub_criteria_container');
                if (subContainer) {
                    updateQuantSubSequence(subContainer, quantPrefix);
                }
            });
        }

        function updateQuantSubSequence(container, quantPrefix = '') {
            container.querySelectorAll('.quant_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = quantPrefix ? `${quantPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.quant_sub_sequence'), subPrefix);
            });
            updateQuantSubSettingLinks();
        }

        function updateQuantSubSettingLinks() {
            const baseUrl = '/workload-config';
            document.querySelectorAll('.quant_sub_criteria_block').forEach(block => {
                const idInput = block.querySelector('.quant_sub_criteria_id');
                const link = block.querySelector('.quant_sub_setting_btn');
                if (!link) {
                    return;
                }
                const idValue = idInput ? idInput.value.trim() : '';
                link.setAttribute('href', idValue ? `${baseUrl}?quant_sub_criteria_id=${encodeURIComponent(idValue)}` : baseUrl);
            });
        }

        function updateQualMainSequence(container, evalPrefix = '') {
            container.querySelectorAll('.qual_criteria_block').forEach((block, idx) => {
                const qualPrefix = evalPrefix ? `${evalPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_main_sequence'), qualPrefix);

                const subContainer = block.querySelector('.qual_sub_criterias_container');
                if (subContainer) {
                    updateQualSubSequence(subContainer, qualPrefix);
                }
            });
        }

        function updateQualSubSequence(container, qualPrefix = '') {
            container.querySelectorAll('.qual_sub_criteria_block').forEach((block, idx) => {
                const subPrefix = qualPrefix ? `${qualPrefix}.${idx + 1}` : `${idx + 1}`;
                setSequenceInputValue(block.querySelector('.qual_sub_sequence'), subPrefix);
            });
        }

        function updateSupportSequence(container, evalPrefix = '') {
            container.querySelectorAll('.support_criteria_block').forEach((block, index) => {
                const supportPrefix = evalPrefix ? `${evalPrefix}.${index + 1}` : `${index + 1}`;
                setSequenceInputValue(block.querySelector('.support_sequence'), supportPrefix);
            });
        }

        let draggedBlock = null;

        function refreshOrderUI() {
            updateCategorySequence(document.getElementById('categories_container'));
            updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
            updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
            updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
            updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
            updateButtonStates('.support_criteria_block', '.move_support_up_btn', '.move_support_down_btn');
        }

        document.addEventListener('pointerdown', function (e) {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });

            const handle = e.target.closest('.drag_handle');
            if (!handle) {
                return;
            }

            const block = handle.closest('[data-draggable-level]');
            if (block) {
                block.dataset.dragArmed = 'true';
            }
        });

        document.addEventListener('pointerup', function () {
            document.querySelectorAll('[data-drag-armed="true"]').forEach((block) => {
                delete block.dataset.dragArmed;
            });
        });

        document.addEventListener('dragstart', function (e) {
            const block = e.target.closest('[data-draggable-level]');
            if (!block || block.dataset.dragArmed !== 'true') {
                e.preventDefault();
                return;
            }

            draggedBlock = block;
            block.classList.add('opacity-60');
            e.dataTransfer.effectAllowed = 'move';
            e.dataTransfer.setData('text/plain', block.dataset.draggableLevel || 'move');
        });

        document.addEventListener('dragover', function (e) {
            if (!draggedBlock) {
                return;
            }

            const target = e.target.closest('[data-draggable-level]');
            if (!target || target === draggedBlock) {
                return;
            }

            if (target.dataset.draggableLevel !== draggedBlock.dataset.draggableLevel || target.parentElement !== draggedBlock.parentElement) {
                return;
            }

            e.preventDefault();
            const rect = target.getBoundingClientRect();
            const insertAfter = (e.clientY - rect.top) > (rect.height / 2);
            target.parentElement.insertBefore(draggedBlock, insertAfter ? target.nextElementSibling : target);
            refreshOrderUI();
        });

        document.addEventListener('dragend', function () {
            if (draggedBlock) {
                draggedBlock.classList.remove('opacity-60');
                delete draggedBlock.dataset.dragArmed;
                markDirty();
            }
            draggedBlock = null;
        });

        function showLoading() {
            document.getElementById('loading_overlay').classList.remove('hidden');
        }

        function hideLoading() {
            document.getElementById('loading_overlay').classList.add('hidden');
        }

        function showConfirmModal(reportTitle) {
            document.getElementById('version_name_display').textContent = reportTitle || 'ไม่ระบุ';
            document.getElementById('confirm_modal').classList.remove('hidden');
        }

        // Modal-based alert for validation error
        function showValidationErrorModal(message) {
            let modal = document.getElementById('custom-alert-modal');
            if (!modal) {
                modal = document.createElement('div');
                modal.id = 'custom-alert-modal';
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.innerHTML = `
                    <div id="custom-alert-box" class="bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in">
                        <div class="flex justify-center mb-4">
                            <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
                                <svg class="w-7 h-7 text-red-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                            </span>
                        </div>
                        <div class="text-lg font-semibold mb-2 text-red-600">กรอกข้อมูลไม่ครบถ้วน</div>
                        <div class="mb-4 text-gray-700">${message}</div>
                        <button id="custom-alert-ok" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ตกลง</button>
                    </div>
                `;
                document.body.appendChild(modal);
            } else {
                modal.className = 'fixed inset-0 z-50 flex items-center justify-center';
                modal.style.background = 'rgba(0,0,0,0.6)';
                modal.querySelector('#custom-alert-box').className = `bg-white rounded-lg shadow-2xl max-w-sm w-full p-6 text-center animate-fade-in`;
                modal.querySelector('#custom-alert-box').innerHTML = `
                    <div class="flex justify-center mb-4">
                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-red-100">
                            <svg class=\"w-7 h-7 text-red-500\" fill=\"none\" stroke=\"currentColor\" stroke-width=\"2\" viewBox=\"0 0 24 24\"><path stroke-linecap=\"round\" stroke-linejoin=\"round\" d=\"M6 18L18 6M6 6l12 12\"/></svg>
                        </span>
                    </div>
                    <div class="text-lg font-semibold mb-2 text-red-600">กรอกข้อมูลไม่ครบถ้วน</div>
                    <div class="mb-4 text-gray-700">${message}</div>
                    <button id=\"custom-alert-ok\" class=\"mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none\">ตกลง</button>
                `;
                modal.style.display = '';
            }
            modal.querySelector('#custom-alert-ok').onclick = function() {
                modal.style.display = 'none';
            };
        }
        // // Validate required fields before showing confirm modal
        document.getElementById('jsonForm').addEventListener('submit', function(e) {
            // Prevent default submit for custom validation
            e.preventDefault();
            // Basic required fields (ไม่ต้องตรวจสอบ version_name อีกต่อไป)
            const reportTitle = document.getElementById('report_title').value.trim();
            const reportDescription = document.getElementById('report_description').value.trim();
            const assessmentType = document.getElementById('assessment_type').value.trim();
            let errorMsg = '';
            if (!reportTitle) errorMsg += 'กรุณากรอกชื่อเกณฑ์\n';
            if (!assessmentType) errorMsg += 'กรุณาเลือกประเภทการประเมิน\n';
            if (errorMsg) {
                showValidationErrorModal(errorMsg.replace(/\n/g, '<br>'));
                return false;
            }
            // Generate version_name automatically
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `${currentYear}_AUTO`;
            document.getElementById('version_name').value = "เกณฑ์เวอร์ชั่น" + versionName;

            // If valid, show confirm modal
            showConfirmModal(reportTitle); // Show report title instead of version name
        }, true);

        function hideConfirmModal() {
            document.getElementById('confirm_modal').classList.add('hidden');
        }

        function showSuccessModal() {
            isSubmitting = false;
            resetDirtyState();
            document.getElementById('success_modal').classList.remove('hidden');
            let countdown = 5;
            const countdownElement = document.getElementById('countdown');
            const interval = setInterval(() => {
                countdown--;
                countdownElement.textContent = countdown;
                if (countdown <= 0) {
                    clearInterval(interval);
                    window.location.href = "{{ route('criteria_config.index') }}";
                }
            }, 1000);
        }

        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete_category_btn')) {
                const block = e.target.closest('.category_block');
                const container = document.getElementById('categories_container');
                if (confirm('ต้องการลบหมวดหมู่นี้ใช่หรือไม่?')) {
            if (container.querySelectorAll('.category_block').length > 1) {
                block.remove();
                updateCategorySequence(container);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีหมวดหมู่การประเมินอย่างน้อย 1 รายการ');
            }
                }
            }

            if (e.target.closest('.delete_eval_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
            if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                block.remove();
                updateEvalSequence(container);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีรายการประเมินอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_quant_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
            if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQuantMainSequence(container);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์ปริมาณหลักอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_quant_sub_btn')) {
                const block = e.target.closest('.quant_sub_criteria_block');
                const container = block.closest('.quant_sub_criteria_container');
            if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                block.remove();
                updateQuantSubSequence(container);
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์ปริมาณย่อยอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_qual_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
            if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQualMainSequence(container);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์คุณภาพหลักอย่างน้อย 1 รายการ');
            }
            }

            if (e.target.closest('.delete_qual_sub_btn')) {
                const block = e.target.closest('.qual_sub_criteria_block');
                const container = block.closest('.qual_sub_criterias_container');
            if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                block.remove();
                updateQualSubSequence(container);
                markDirty();
            } else {
                showValidationErrorModal('ต้องมีเกณฑ์คุณภาพย่อยอย่างน้อย 1 รายการ');
            }

            if (e.target.closest('.delete_support_criteria_btn')) {
                const block = e.target.closest('.support_criteria_block');
                const container = block.closest('.support_criteria_items');
                if (container.querySelectorAll('.support_criteria_block').length > 1) {
                    block.remove();
                    refreshOrderUI();
                    markDirty();
                } else {
                    showValidationErrorModal('ต้องมีเกณฑ์สายสนับสนุนอย่างน้อย 1 รายการ');
                }
            }
            }

            if (e.target.closest('.move_category_up_btn')) {
                const block = e.target.closest('.category_block');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('category_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                    markDirty();
                }
            }

            if (e.target.closest('.move_category_down_btn')) {
                const block = e.target.closest('.category_block');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('category_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                    markDirty();
                }
            }

            if (e.target.closest('.move_eval_up_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('evaluation_list_block')) {
                    container.insertBefore(block, previous);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                    markDirty();
                }
            }

            if (e.target.closest('.move_eval_down_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('evaluation_list_block')) {
                    container.insertBefore(next, block);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                    markDirty();
                }
            }

            if (e.target.closest('.move_quant_up_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_quant_down_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_qual_up_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_qual_down_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_support_up_btn')) {
                const block = e.target.closest('.support_criteria_block');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('support_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    refreshOrderUI();
                    markDirty();
                }
            }

            if (e.target.closest('.move_support_down_btn')) {
                const block = e.target.closest('.support_criteria_block');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('support_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    refreshOrderUI();
                    markDirty();
                }
            }

            if (e.target.closest('#add_category_btn')) {
                let newBlock = cloneAndClear('.category_block');
                document.getElementById('categories_container').appendChild(newBlock);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                updateCategorySequence(document.getElementById('categories_container'));
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_evaluation_list_btn')) {
                let parent = e.target.closest('.category_block').querySelector('.evaluation_lists_container');
                let newBlock = cloneAndClear('.evaluation_list_block');
                parent.appendChild(newBlock);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                updateEvalSequence(parent);
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quantity_main_criterias_container');
                let newBlock = cloneAndClear('.quant_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                updateQuantMainSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                    ensureRuntimeFormFieldIdentifiers(newBlock);
                    ensureRuntimeLabelAssociations(newBlock);
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_sub_criteria_btn')) {
                let parent = e.target.closest('.quant_criteria_block').querySelector(
                    '.quant_sub_criteria_container');
                let newBlock = cloneAndClear('.quant_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQuantSubSequence(parent);
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quality_main_criterias_container');
                let newBlock = cloneAndClear('.qual_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                updateQualMainSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'กรุณาใส่คำอธิบายเพิ่มเติม...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                    ensureRuntimeFormFieldIdentifiers(newBlock);
                    ensureRuntimeLabelAssociations(newBlock);
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_sub_criteria_btn')) {
                let parent = e.target.closest('.qual_criteria_block').querySelector(
                    '.qual_sub_criterias_container');
                let newBlock = cloneAndClear('.qual_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQualSubSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors in the new block
                setTimeout(function() {
                    $(newBlock).find('.qual_sub_description.richtext-editor').summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'ใส่คำอธิบายการให้คะแนน',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                    ensureRuntimeFormFieldIdentifiers(newBlock);
                    ensureRuntimeLabelAssociations(newBlock);
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_support_criteria_btn')) {
                const evaluationBlock = e.target.closest('.evaluation_list_block');
                const container = evaluationBlock.querySelector('.support_criteria_items');
                const newBlock = cloneAndClear('.support_criteria_block');
                container.appendChild(newBlock);
                refreshOrderUI();
                markDirty();
                ensureRuntimeFormFieldIdentifiers(newBlock);
                ensureRuntimeLabelAssociations(newBlock);
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });

        document.addEventListener('change', function(e) {
            if (e.target.classList.contains('criteria_type')) {
                const evalBlock = e.target.closest('.evaluation_list_block');
                const quantityContainer = evalBlock.querySelector('.quantity_main_criterias_container');
                const qualityContainer = evalBlock.querySelector('.quality_main_criterias_container');
                const supportContainer = evalBlock.querySelector('.support_criterias_container');
                const quantityCheckbox = evalBlock.querySelector('.quantity_criteria_type');
                const qualityCheckbox = evalBlock.querySelector('.quality_criteria_type');
                const supportCheckbox = evalBlock.querySelector('.support_criteria_type');
                quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
                qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
                supportContainer.classList.toggle('hidden', !supportCheckbox.checked);
            }
        });

        document.getElementById('reset_form_btn').addEventListener('click', function() {
            showValidationErrorModal('ต้องการล้างข้อมูลทั้งหมดใช่หรือไม่? <br><br><button id="confirm-reset-btn" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">ยืนยัน</button>');
            setTimeout(() => {
                const confirmBtn = document.getElementById('confirm-reset-btn');
                if (confirmBtn) {
                    confirmBtn.onclick = function() {
                        document.getElementById('custom-alert-modal').style.display = 'none';
                        document.getElementById('jsonForm').reset();
                        document.querySelectorAll('.quantity_main_criterias_container, .quality_main_criterias_container, .support_criterias_container')
                            .forEach(container => container.classList.add('hidden'));
                        updateCategorySequence(document.getElementById('categories_container'));
                        document.querySelectorAll('.evaluation_lists_container').forEach(updateEvalSequence);
                        document.querySelectorAll('.quantity_main_criterias_container').forEach(updateQuantMainSequence);
                        document.querySelectorAll('.quant_sub_criteria_container').forEach(updateQuantSubSequence);
                        document.querySelectorAll('.quality_main_criterias_container').forEach(updateQualMainSequence);
                        document.querySelectorAll('.qual_sub_criterias_container').forEach(updateQualSubSequence);
                        document.querySelectorAll('.support_criterias_container').forEach(updateSupportSequence);
                    };
                }
            }, 100);
        });

        let finalData = null;

        document.getElementById('jsonForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // ไม่ต้องตรวจสอบ version_name เพราะจะ generate อัตโนมัติ
            const reportTitle = document.querySelector('.report_title').value.trim();
            const reportDescription = document.querySelector('.report_description').value.trim();
            if (!reportTitle) {
                return;
            }

            // Generate version_name อัตโนมัติ
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `เกณฑ์ประเมินปี ${currentYear} ครั้งที่ AUTO`;
            
            // Save all Summernote content back to textareas before collecting data
            $('.richtext-editor').each(function() {
                if ($(this).hasClass('note-editor')) {
                    $(this).val($(this).summernote('code'));
                }
            });
            
            finalData = {
                version_name: versionName, // สร้างชื่ออัตโนมัติ
                created_by: document.getElementById('auth-user-id')?.value || 1,
                report_datas: [],
                categories: []
            };

            let rd = document.querySelector('.report_datas_block');
            const assessmentType = rd.querySelector('.assessment_type').value || null;
            finalData.report_datas.push({
                report_title: reportTitle,
                report_description: reportDescription || null ,
                assessment_type: assessmentType,
                comment: rd.querySelector('.comment').value || null
            });

            Array.from(document.querySelectorAll('#categories_container .category_block'))
                .sort((left, right) => compareSequenceValues(
                    getSequenceValue(left, '.category_sequence', 1),
                    getSequenceValue(right, '.category_sequence', 1)
                ))
                .forEach((catBlock, catI) => {
                const mainCategories = catBlock.querySelector('.main_categories').value.trim();
                const subCategories = catBlock.querySelector('.sub_categories').value.trim();
                if (!mainCategories || !subCategories) {
                    return;
                }

                let category = {
                    main_categories: mainCategories,
                    sub_categories: subCategories,
                    sequence: catI + 1,
                    evaluation_lists: []
                };

                Array.from(catBlock.querySelectorAll('.evaluation_lists_container .evaluation_list_block'))
                    .sort((left, right) => compareSequenceValues(
                        getSequenceValue(left, '.eval_sequence', `${catI + 1}.1`),
                        getSequenceValue(right, '.eval_sequence', `${catI + 1}.1`)
                    ))
                    .forEach((
                    evalBlock, evalI) => {
                    const evalName = evalBlock.querySelector('.eval_name').value.trim();
                    const sumScore = evalBlock.querySelector('.sum_score').value;
                    if (!evalName || !sumScore) {
                        //     `กรุณากรอกชื่อรายการประเมินและคะแนนรวมสำหรับรายการที่ ${evalI + 1} ในหมวดหมู่ที่ ${catI + 1}`
                        return;
                    }

                    const quantityChecked = evalBlock.querySelector('.quantity_criteria_type')
                        .checked;
                    const qualityChecked = evalBlock.querySelector('.quality_criteria_type')
                        .checked;
                    const supportChecked = evalBlock.querySelector('.support_criteria_type')
                        .checked;

                    let evalList = {
                        name: evalName,
                        sum_score: Number(sumScore),
                        sequence: evalI + 1,
                        annotation: evalBlock.querySelector('.annotation').value || null,
                        quantity_main_criterias: [],
                        quality_main_criterias: []
                    };

                    if (quantityChecked) {
                        let valid = true;
                        Array.from(evalBlock.querySelectorAll(
                            '.quantity_main_criterias_container .quant_criteria_block'))
                            .sort((left, right) => compareSequenceValues(
                                getSequenceValue(left, '.quant_main_sequence', `${catI + 1}.${evalI + 1}.1`),
                                getSequenceValue(right, '.quant_main_sequence', `${catI + 1}.${evalI + 1}.1`)
                            ))
                            .forEach(
                            (qMain, qj) => {
                                const quantName = qMain.querySelector('.quant_name').value
                                    .trim();
                                // Get content from Summernote editor if available, otherwise from textarea
                                const tooltipsTextarea = qMain.querySelector('.quant_tooltips');
                                const quantTooltips = $(tooltipsTextarea).hasClass('note-editor')
                                    ? $(tooltipsTextarea).summernote('code') 
                                    : tooltipsTextarea.value.trim();
                                const quantFormula = qMain.querySelector('.quant_formula')?.value.trim() || '';
                                if (!quantName) {
                                    //     `กรุณากรอกชื่อเกณฑ์และคำอธิบายสำหรับเกณฑ์ปริมาณหลักที่ ${qj + 1} ในรายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                    valid = false;
                                    return;
                                }

                                let quantMain = {
                                    name: quantName,
                                    tooltips: quantTooltips || null,
                                    description: qMain.querySelector('.quant_description')?.value.trim() || '',
                                    sequence: qj + 1,
                                    formula: quantFormula,
                                    quantity_sub_criterias: []
                                };

                                Array.from(qMain.querySelectorAll(
                                    '.quant_sub_criteria_container .quant_sub_criteria_block'
                                ))
                                    .sort((left, right) => compareSequenceValues(
                                        getSequenceValue(left, '.quant_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`),
                                        getSequenceValue(right, '.quant_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`)
                                    ))
                                    .forEach((subQ, sk) => {
                                    const subName = subQ.querySelector(
                                        '.quant_sub_name').value.trim();
                                    const scoreA = subQ.querySelector('.score_a').value;
                                    const scoreB = subQ.querySelector('.score_b').value;
                                    if (!subName || !scoreA || !scoreB) {
                                        alert(
                                            `กรุณากรอกชื่อเกณฑ์ย่อย, คะแนน A, และคะแนน B สำหรับเกณฑ์ปริมาณย่อยที่ ${sk + 1} ในเกณฑ์ปริมาณหลักที่ ${qj + 1} รายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                        );
                                        valid = false;
                                        return;
                                    }

                                    const quantSubPayload = {
                                        name: subName,
                                        sequence: sk + 1,
                                        score_a: Number(scoreA),
                                        score_b: Number(scoreB),
                                        require_evidence: subQ.querySelector('.quant_require_evidence')?.checked || false,
                                    };
                                    quantMain.quantity_sub_criterias.push(quantSubPayload);
                                });

                                if (valid) {
                                    evalList.quantity_main_criterias.push(quantMain);
                                }
                            });
                        if (!valid) return;
                    }

                    if (qualityChecked) {
                        let valid = true;
                        Array.from(evalBlock.querySelectorAll(
                            '.quality_main_criterias_container .qual_criteria_block'))
                            .sort((left, right) => compareSequenceValues(
                                getSequenceValue(left, '.qual_main_sequence', `${catI + 1}.${evalI + 1}.1`),
                                getSequenceValue(right, '.qual_main_sequence', `${catI + 1}.${evalI + 1}.1`)
                            ))
                            .forEach((
                            qMain, qj) => {
                            const qualName = qMain.querySelector('.qual_name').value.trim();
                            const qualRatio = qMain.querySelector('.qual_ratio').value;
                            // Get content from Summernote editor if available, otherwise from textarea
                            const tooltipsTextarea = qMain.querySelector('.qual_tooltips');
                            const qualTooltips = $(tooltipsTextarea).hasClass('note-editor')
                                ? $(tooltipsTextarea).summernote('code') 
                                : tooltipsTextarea.value.trim();
                            if (!qualName || !qualRatio) {
                                alert(
                                    `กรุณากรอกชื่อเกณฑ์และสัดส่วนคะแนนสำหรับเกณฑ์คุณภาพหลักที่ ${qj + 1} ในรายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`
                                );
                                valid = false;
                                return;
                            }

                            let qualMain = {
                                name: qualName,
                                ratio: Number(qualRatio),
                                tooltips: qualTooltips || null,
                                sequence: qj + 1,
                                require_evidence: qMain.querySelector('.qual_require_evidence')?.checked || false,
                                allow_multiple: qMain.querySelector('.qual_allow_multiple')?.checked || false,
                                quality_sub_criterias: []
                            };

                            Array.from(qMain.querySelectorAll(
                                '.qual_sub_criterias_container .qual_sub_criteria_block'
                            ))
                                .sort((left, right) => compareSequenceValues(
                                    getSequenceValue(left, '.qual_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`),
                                    getSequenceValue(right, '.qual_sub_sequence', `${catI + 1}.${evalI + 1}.${qj + 1}.1`)
                                ))
                                .forEach((subQ, sk) => {
                                const subName = subQ.querySelector('.qual_sub_name')
                                    .value.trim();
                                const numScore = subQ.querySelector('.num_score')
                                    .value;
                                // Get content from Summernote editor if available, otherwise from textarea
                                const descriptionTextarea = subQ.querySelector('.qual_sub_description');
                                const subDescription = $(descriptionTextarea).hasClass('note-editor')
                                    ? $(descriptionTextarea).summernote('code') 
                                    : descriptionTextarea.value.trim() || '';
                                if (!subName || !numScore) {
                                    showValidationErrorModal(`กรุณากรอกชื่อเกณฑ์ย่อยและคะแนนสูงสุดสำหรับเกณฑ์คุณภาพย่อยที่ ${sk + 1} ในเกณฑ์คุณภาพหลักที่ ${qj + 1} รายการประเมินที่ ${evalI + 1} หมวดหมู่ที่ ${catI + 1}`);
                                    valid = false;
                                    return;
                                }

                                qualMain.quality_sub_criterias.push({
                                    name: subName,
                                    sequence: sk + 1,
                                    num_score: Number(numScore),
                                    description: subDescription
                                });
                            });

                            if (valid) {
                                evalList.quality_main_criterias.push(qualMain);
                            }
                        });
                        if (!valid) return;
                    }

                    if (supportChecked) {
                        let valid = true;
                        evalList.support_criterias = [];
                        Array.from(evalBlock.querySelectorAll('.support_criteria_block'))
                            .sort((left, right) => compareSequenceValues(
                                getSequenceValue(left, '.support_sequence', `${catI + 1}.${evalI + 1}.1`),
                                getSequenceValue(right, '.support_sequence', `${catI + 1}.${evalI + 1}.1`)
                            ))
                            .forEach((supportBlock, supportIndex) => {
                                const activityName = supportBlock.querySelector('.support_activity_name').value.trim();
                                const indicator = supportBlock.querySelector('.support_indicator').value.trim();
                                const targetValue = supportBlock.querySelector('.support_target_value').value;
                                const weight = supportBlock.querySelector('.support_weight').value;

                                if (!activityName || !indicator || targetValue === '' || weight === '') {
                                    showValidationErrorModal(`กรุณากรอกข้อมูลเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ให้ครบถ้วน`);
                                    valid = false;
                                    return;
                                }
                                if (Number(targetValue) < 0) {
                                    showValidationErrorModal(`ระดับค่าเป้าหมายของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องไม่ติดลบ`);
                                    valid = false;
                                    return;
                                }
                                if (Number(weight) <= 0 || Number(weight) > 100) {
                                    showValidationErrorModal(`น้ำหนักของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องมากกว่า 0 และไม่เกิน 100`);
                                    valid = false;
                                    return;
                                }

                                evalList.support_criterias.push({
                                    sequence: supportIndex + 1,
                                    activity_name: activityName,
                                    indicator,
                                    target_value: Number(targetValue),
                                    weight: Number(weight)
                                });
                            });
                        if (!valid) return;
                    }

                    category.evaluation_lists.push(evalList);
                });

                if (category.evaluation_lists.length === 0) {
                    showValidationErrorModal(`กรุณาเพิ่มรายการประเมินอย่างน้อย 1 รายการในหมวดหมู่ที่ ${catI + 1}`);
                    return;
                }

                finalData.categories.push(category);
            });

            if (finalData.categories.length === 0) {
                showValidationErrorModal('กรุณาเพิ่มหมวดหมู่การประเมินอย่างน้อย 1 หมวดหมู่');
                return;
            }

            showConfirmModal(reportTitle); // แสดง report_title แทน finalData.version_name
        });

        document.getElementById('confirm_submit_btn').addEventListener('click', async function handleSubmit() {
            hideConfirmModal();
            isSubmitting = true;
            updateFloatingSaveButton();
            showLoading();

            try {
                const response = await fetch("{{ route('report-structure.store') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('input[name="_token"]')?.value ||
                            document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(finalData)
                });

                const data = await response.json();

                hideLoading();

                if (response.ok && data.success) {
                    // Success case (HTTP 201)
                    showSuccessModal();
                } else if (response.status === 422) {
                    isSubmitting = false;
                    updateFloatingSaveButton();
                    // Validation error (HTTP 422)
                    let errorMessage = 'เกิดข้อผิดพลาดในการตรวจสอบข้อมูล:\n';

                    // Check for both 'error' and 'errors' to handle potential response variations
                    const errors = data.error || data.errors || {};

                    if (Object.keys(errors).length > 0) {
                        // Process validation errors if present
                        for (const [field, messages] of Object.entries(errors)) {
                            errorMessage +=
                                `${field}: ${Array.isArray(messages) ? messages.join(', ') : messages}\n`;
                        }
                    } else {
                        // Fallback if no specific errors are provided
                        errorMessage += data.message || 'ไม่พบรายละเอียดข้อผิดพลาด';
                    }

                    alert(errorMessage);
                } else {
                    isSubmitting = false;
                    updateFloatingSaveButton();
                    // Other errors (e.g., HTTP 500)
                    alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกข้อมูลได้'));
                }
            } catch (error) {
                // Network or unexpected errors
                isSubmitting = false;
                updateFloatingSaveButton();
                hideLoading();
                console.error('Fetch error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ: ' + error.message);
            }
        });
    </script>
