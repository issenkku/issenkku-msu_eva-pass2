        // Quantity criteria handlers
        function handleDeleteQuantityCriteria(quantBlock) {
            const container = quantBlock.closest('.quantity_main_criterias_container');
            if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(quantBlock).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                quantBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์ปริมาณหลักอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQuantityCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const newBlock = cloneAndClear('.quant_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleDeleteQuantitySubCriteria(subBlock) {
            const container = subBlock.closest('.quant_sub_criteria_container');
            if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                subBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์ปริมาณย่อยอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQuantitySubCriteria(quantBlock) {
            const container = quantBlock.querySelector('.quant_sub_criteria_container');
            const newBlock = cloneAndClear('.quant_sub_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
