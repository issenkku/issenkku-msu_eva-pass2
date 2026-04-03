        // Helper สำหรับสลับการแสดงผลของเกณฑ์ด้านปริมาณและคุณภาพ
        function handleCriteriaTypeChange(evaluationBlock) {
            const quantityContainer = evaluationBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = evaluationBlock.querySelector('.quality_main_criterias_container');
            const quantityCheckbox = evaluationBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = evaluationBlock.querySelector('.quality_criteria_type');

            quantityContainer.classList.toggle('hidden', !quantityCheckbox.checked);
            qualityContainer.classList.toggle('hidden', !qualityCheckbox.checked);
        }
