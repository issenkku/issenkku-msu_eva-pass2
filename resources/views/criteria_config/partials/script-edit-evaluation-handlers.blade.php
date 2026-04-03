        // Evaluation handlers
        function handleDeleteEvaluation(evaluationBlock) {
            const container = evaluationBlock.closest('.evaluation_lists_container');
            if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                evaluationBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีรายการประเมินอย่างน้อย 1 รายการ');
            }
        }

        function handleAddEvaluation(categoryBlock) {
            const container = categoryBlock.querySelector('.evaluation_lists_container');
            
            // Get the first evaluation block within THIS category as a template
            const template = container.querySelector('.evaluation_list_block');
            
            if (!template) {
                // Fallback: use the hidden template
                const hiddenTemplate = document.querySelector('.category_block[style*="display: none"] .evaluation_list_block');
                if (hiddenTemplate) {
                    const newBlock = hiddenTemplate.cloneNode(true);
                    clearIdentityAttributes(newBlock);
                    
                    // Clean up the cloned block
                    $(newBlock).find('.richtext-editor').each(function() {
                        $(this).next('.note-editor').remove();
                        $(this).removeClass('note-editor note-frame note-editable');
                        $(this).removeAttr('style');
                        $(this).show();
                        this.value = '';
                        this.id = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
                    });
                    
                    newBlock.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
                    newBlock.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
                    newBlock.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
                    newBlock.querySelector('.quantity_main_criterias_container')?.classList.add('hidden');
                    newBlock.querySelector('.quality_main_criterias_container')?.classList.add('hidden');
                    
                    container.appendChild(newBlock);
                    updateSequences();
                    newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
                return;
            }
            
            // Clone from existing evaluation block in this category
            const newBlock = template.cloneNode(true);
            clearIdentityAttributes(newBlock);
            
            // Clean up the cloned block
            $(newBlock).find('.richtext-editor').each(function() {
                const $editor = $(this);
                
                // Destroy any existing Summernote
                if ($editor.next('.note-editor').length > 0) {
                    try {
                        $editor.summernote('destroy');
                    } catch(e) {}
                }
                
                $editor.next('.note-editor').remove();
                $editor.removeClass('note-editor note-frame note-editable');
                $editor.removeAttr('style');
                $editor.show();
                this.value = '';
                this.id = 'editor_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
            });
            
            // Clear all inputs
            newBlock.querySelectorAll('input[type="checkbox"]').forEach(inp => inp.checked = false);
            newBlock.querySelectorAll('input:not([type="checkbox"])').forEach(inp => inp.value = '');
            newBlock.querySelectorAll('textarea:not(.quant_formula):not(.richtext-editor)').forEach(textarea => textarea.value = '');
            newBlock.querySelectorAll('textarea.quant_formula').forEach(textarea => textarea.value = 'D = A ร— C / B');
            
            // Hide criteria containers
            newBlock.querySelector('.quantity_main_criterias_container')?.classList.add('hidden');
            newBlock.querySelector('.quality_main_criterias_container')?.classList.add('hidden');
            
            // Remove extra blocks (keep only first of each type)
            newBlock.querySelectorAll('.quant_criteria_block:not(:first-child)').forEach(e => e.remove());
            newBlock.querySelectorAll('.qual_criteria_block:not(:first-child)').forEach(e => e.remove());
            
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleCriteriaTypeChange(evaluationBlock) {
            const quantityContainer = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = evaluationBlock.querySelector('.quality_main_criterias_container');
            const quantityCheckbox = evaluationBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = evaluationBlock.querySelector('.quality_criteria_type');
            
            quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
            qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
        }
