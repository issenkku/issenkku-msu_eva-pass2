        // Helper สำหรับสลับการแสดงผลของเกณฑ์แต่ละประเภท
        function handleCriteriaTypeChange(evaluationBlock) {
            const quantityContainer = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = evaluationBlock.querySelector('.quality_main_criterias_container');
            const supportContainer = evaluationBlock.querySelector('.support_criterias_container');
            const quantityCheckbox = evaluationBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = evaluationBlock.querySelector('.quality_criteria_type');
            const supportCheckbox = evaluationBlock.querySelector('.support_criteria_type');

            quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
            qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
            supportContainer.classList.toggle('hidden', !supportCheckbox.checked);
            updatePermanentQuantityDeleteButton(evaluationBlock);
        }
