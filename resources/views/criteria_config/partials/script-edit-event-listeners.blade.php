        function setupEventListeners() {
            // Event delegation for dynamic elements
            document.addEventListener('click', function(e) {
                // Category buttons
                if (e.target.closest('.delete_category_btn')) {
                    handleDeleteCategory(e.target.closest('.category_block'));
                    markDirty();
                }
                if (e.target.closest('.move_category_up_btn')) {
                    handleMoveCategoryUp(e.target.closest('.category_block'));
                    markDirty();
                }
                if (e.target.closest('.move_category_down_btn')) {
                    handleMoveCategoryDown(e.target.closest('.category_block'));
                    markDirty();
                }
                if (e.target.closest('#add_category_btn')) {
                    handleAddCategory();
                    markDirty();
                }

                // Evaluation List buttons
                if (e.target.closest('.delete_eval_btn')) {
                    handleDeleteEvaluation(e.target.closest('.evaluation_list_block'));
                    markDirty();
                }
                if (e.target.closest('.add_evaluation_list_btn')) {
                    handleAddEvaluation(e.target.closest('.category_block'));
                    markDirty();
                }

                // Quantity Criteria buttons
                if (e.target.closest('.delete_quant_btn')) {
                    handleDeleteQuantityCriteria(e.target.closest('.quant_criteria_block'));
                    markDirty();
                }
                if (e.target.closest('.add_quant_criteria_btn')) {
                    handleAddQuantityCriteria(e.target.closest('.evaluation_list_block'));
                    markDirty();
                }
                if (e.target.closest('.delete_quant_sub_btn')) {
                    handleDeleteQuantitySubCriteria(e.target.closest('.quant_sub_criteria_block'));
                    markDirty();
                }
                if (e.target.closest('.add_quant_sub_criteria_btn')) {
                    handleAddQuantitySubCriteria(e.target.closest('.quant_criteria_block'));
                    markDirty();
                }

                // Quality Criteria buttons
                if (e.target.closest('.delete_qual_btn')) {
                    handleDeleteQualityCriteria(e.target.closest('.qual_criteria_block'));
                    markDirty();
                }
                if (e.target.closest('.add_qual_criteria_btn')) {
                    handleAddQualityCriteria(e.target.closest('.evaluation_list_block'));
                    markDirty();
                }
                if (e.target.closest('.delete_qual_sub_btn')) {
                    handleDeleteQualitySubCriteria(e.target.closest('.qual_sub_criteria_block'));
                    markDirty();
                }
                if (e.target.closest('.add_qual_sub_criteria_btn')) {
                    handleAddQualitySubCriteria(e.target.closest('.qual_criteria_block'));
                    markDirty();
                }

                // Support Criteria buttons
                if (e.target.closest('.add_support_criteria_btn')) {
                    addSupportCriteria(e.target.closest('.evaluation_list_block'));
                }
                if (e.target.closest('.delete_support_criteria_btn')) {
                    deleteSupportCriteria(e.target.closest('.support_criteria_block'));
                }
            });

            // Criteria type checkboxes
            document.addEventListener('change', function(e) {
                if (e.target.classList.contains('criteria_type')) {
                    handleCriteriaTypeChange(e.target.closest('.evaluation_list_block'));
                }
            });
        }
