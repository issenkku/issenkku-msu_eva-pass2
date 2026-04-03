        // Quality criteria handlers
        function handleDeleteQualityCriteria(qualBlock) {
            const container = qualBlock.closest('.quality_main_criterias_container');
            if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(qualBlock).find('.richtext-editor').each(function() {
                    if ($(this).next('.note-editor').length > 0) {
                        $(this).summernote('destroy');
                    }
                });
                qualBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์คุณภาพหลักอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQualityCriteria(evaluationBlock) {
            const container = evaluationBlock.querySelector('.quality_main_criterias_container');
            const newBlock = cloneAndClear('.qual_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }

        function handleDeleteQualitySubCriteria(subBlock) {
            const container = subBlock.closest('.qual_sub_criterias_container');
            if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                subBlock.remove();
                updateSequences();
            } else {
                alert('ต้องมีเกณฑ์คุณภาพย่อยอย่างน้อย 1 รายการ');
            }
        }

        function handleAddQualitySubCriteria(qualBlock) {
            const container = qualBlock.querySelector('.qual_sub_criterias_container');
            const newBlock = cloneAndClear('.qual_sub_criteria_block');
            container.appendChild(newBlock);
            updateSequences();
            newBlock.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
