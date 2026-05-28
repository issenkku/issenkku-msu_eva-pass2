        function populateForm(data) {
            document.getElementById('version_name').value = data.version_name || '';
            currentReportDataId = null;
            
            if (data.report_datas && data.report_datas.length > 0) {
                const reportData = data.report_datas[0];
                currentReportDataId = reportData.report_data_id || reportData.id || null;
                document.getElementById('report_title').value = reportData.report_title || '';
                document.getElementById('report_description').value = reportData.report_description || '';
                document.getElementById('assessment_type').value = reportData.assessment_type || '';
                document.getElementById('comment').value = reportData.comment || '';
            }

            const categoriesContainer = document.getElementById('categories_container');
            const loadingMessage = categoriesContainer.querySelector('.flex.justify-center');
            if (loadingMessage) {
                loadingMessage.remove();
            }

            categoriesContainer.querySelectorAll('.category_block:not([style*="display: none"])').forEach((block) => block.remove());

            if (data.categories && data.categories.length > 0) {
                const uniqueCategories = dedupeByKey(
                    data.categories,
                    (category) => String(category.categorie_id || category.id || `${category.sequence}|${category.main_categories}|${category.sub_categories}`)
                );

                uniqueCategories.forEach((categoryData) => {
                    const evalLists = dedupeByKey(
                        categoryData.evaluation_lists || [],
                        (evaluation) => String(evaluation.evaluation_id || evaluation.id || `${evaluation.sequence}|${evaluation.name}`)
                    ).map((evaluation) => {
                        const quantityMain = dedupeByKey(
                            evaluation.quantity_main_criterias || [],
                            (quantity) => String(quantity.quantity_main_criteria_id || quantity.id || quantity.name)
                        ).map((quantity) => ({
                            ...quantity,
                            quantity_sub_criterias: dedupeByKey(
                                quantity.quantity_sub_criterias || [],
                                (subCriteria) => String(subCriteria.quantity_sub_criteria_id || subCriteria.id || `${subCriteria.sequence}|${subCriteria.name}`)
                            ),
                        }));

                        const qualityMain = dedupeByKey(
                            evaluation.quality_main_criterias || [],
                            (quality) => String(quality.quality_main_criteria_id || quality.id || `${quality.sequence}|${quality.name}`)
                        ).map((quality) => ({
                            ...quality,
                            quality_sub_criterias: dedupeByKey(
                                quality.quality_sub_criterias || [],
                                (subCriteria) => String(subCriteria.quality_sub_criteria_id || subCriteria.id || `${subCriteria.sequence}|${subCriteria.name}`)
                            ),
                        }));

                        return {
                            ...evaluation,
                            quantity_main_criterias: quantityMain,
                            quality_main_criterias: qualityMain,
                        };
                    });

                    const categoryBlock = createCategoryFromData({
                        ...categoryData,
                        evaluation_lists: evalLists,
                    });
                    categoriesContainer.appendChild(categoryBlock);
                });
            } else {
                handleAddCategory();
            }

            updateSequences();
            updateButtonStates('.category_block:not([style*="display: none"])', '.move_category_up_btn', '.move_category_down_btn');
        }

        function createCategoryFromData(categoryData) {
            const template = document.querySelector('.category_block');
            const newBlock = template.cloneNode(true);
            newBlock.style.display = 'block';
            newBlock.dataset.categoryId = categoryData.categorie_id || categoryData.id || '';

            const categoryIdInput = newBlock.querySelector('.category_id_value');
            if (categoryIdInput) {
                categoryIdInput.value = categoryData.categorie_id || categoryData.id || '';
            }

            newBlock.querySelector('.main_categories').value = categoryData.main_categories || '';
            newBlock.querySelector('.sub_categories').value = categoryData.sub_categories || '';

            const evaluationContainer = newBlock.querySelector('.evaluation_lists_container');
            const evalTemplate = evaluationContainer.querySelector('.evaluation_list_block');
            if (evalTemplate) {
                evalTemplate.remove();
            }

            if (categoryData.evaluation_lists && categoryData.evaluation_lists.length > 0) {
                categoryData.evaluation_lists.forEach((evalData) => {
                    const evalBlock = createEvaluationFromData(evalData);
                    evaluationContainer.appendChild(evalBlock);
                });
            }

            return newBlock;
        }

        function createEvaluationFromData(evalData) {
            const template = document.querySelector('.evaluation_list_block');
            const newBlock = template.cloneNode(true);
            newBlock.dataset.evaluationId = evalData.evaluation_id || evalData.id || '';

            const evaluationIdInput = newBlock.querySelector('.evaluation_id_value');
            if (evaluationIdInput) {
                evaluationIdInput.value = evalData.evaluation_id || evalData.id || '';
            }

            newBlock.querySelector('.eval_name').value = evalData.name || '';
            newBlock.querySelector('.sum_score').value = evalData.sum_score || '';
            newBlock.querySelector('.annotation').value = evalData.annotation || '';

            const hasQuantity = evalData.quantity_main_criterias && evalData.quantity_main_criterias.length > 0;
            const hasQuality = evalData.quality_main_criterias && evalData.quality_main_criterias.length > 0;

            const quantityCheckbox = newBlock.querySelector('.quantity_criteria_type');
            const qualityCheckbox = newBlock.querySelector('.quality_criteria_type');
            const quantityContainer = newBlock.querySelector('.quantity_main_criterias_container');
            const qualityContainer = newBlock.querySelector('.quality_main_criterias_container');

            quantityCheckbox.checked = hasQuantity;
            qualityCheckbox.checked = hasQuality;
            quantityContainer.classList.toggle('hidden', !hasQuantity);
            qualityContainer.classList.toggle('hidden', !hasQuality);

            if (hasQuantity) {
                populateQuantityCriteria(quantityContainer, evalData.quantity_main_criterias);
            }

            if (hasQuality) {
                populateQualityCriteria(qualityContainer, evalData.quality_main_criterias);
            }

            return newBlock;
        }

        function populateQuantityCriteria(container, quantityData) {
            container.querySelectorAll('.quant_criteria_block').forEach((block) => block.remove());

            quantityData.forEach((quantMain) => {
                const template = document.querySelector('.quant_criteria_block');
                const newBlock = template.cloneNode(true);
                newBlock.dataset.quantityMainId = quantMain.quantity_main_criteria_id || quantMain.id || '';

                const quantityMainIdInput = newBlock.querySelector('.quantity_main_id_value');
                if (quantityMainIdInput) {
                    quantityMainIdInput.value = quantMain.quantity_main_criteria_id || quantMain.id || '';
                }

                newBlock.querySelector('.quant_name').value = quantMain.name || '';

                const tooltipsTextarea = newBlock.querySelector('.quant_tooltips');
                tooltipsTextarea.value = quantMain.tooltips || '';

                if (quantMain.formulas && quantMain.formulas.length > 0) {
                    newBlock.querySelector('.quant_formula').value = quantMain.formulas[0].condition || 'D = A x C / B';
                }

                const subContainer = newBlock.querySelector('.quant_sub_criteria_container');
                subContainer.querySelectorAll('.quant_sub_criteria_block').forEach((block) => block.remove());

                if (quantMain.quantity_sub_criterias && quantMain.quantity_sub_criterias.length > 0) {
                    quantMain.quantity_sub_criterias.forEach((subData) => {
                        const subBlockTemplate = document.querySelector('.quant_sub_criteria_block');
                        const subBlock = subBlockTemplate.cloneNode(true);
                        subBlock.querySelector('.quant_sub_name').value = subData.name || '';

                        const quantSubIdInput = subBlock.querySelector('.quant_sub_criteria_id');
                        if (quantSubIdInput) {
                            quantSubIdInput.value = subData.quantity_sub_criteria_id || subData.id || '';
                        }

                        const quantRequireEvidence = subBlock.querySelector('.quant_require_evidence');
                        if (quantRequireEvidence) {
                            quantRequireEvidence.checked = Boolean(subData.require_evidence);
                        }

                        subBlock.querySelector('.score_a').value = subData.score_a || '';
                        subBlock.querySelector('.score_b').value = subData.score_b || '';
                        subContainer.appendChild(subBlock);
                    });
                }

                container.appendChild(newBlock);
            });
        }

        function populateQualityCriteria(container, qualityData) {
            container.querySelectorAll('.qual_criteria_block').forEach((block) => block.remove());

            qualityData.forEach((qualMain) => {
                const template = document.querySelector('.qual_criteria_block');
                const newBlock = template.cloneNode(true);
                newBlock.dataset.qualityMainId = qualMain.quality_main_criteria_id || qualMain.id || '';

                const qualityMainIdInput = newBlock.querySelector('.quality_main_id_value');
                if (qualityMainIdInput) {
                    qualityMainIdInput.value = qualMain.quality_main_criteria_id || qualMain.id || '';
                }

                newBlock.querySelector('.qual_name').value = qualMain.name || '';
                newBlock.querySelector('.qual_ratio').value = qualMain.ratio || '';

                const qualRequireEvidence = newBlock.querySelector('.qual_require_evidence');
                if (qualRequireEvidence) {
                    qualRequireEvidence.checked = Boolean(qualMain.require_evidence);
                }

                const qualAllowMultiple = newBlock.querySelector('.qual_allow_multiple');
                if (qualAllowMultiple) {
                    qualAllowMultiple.checked = Boolean(qualMain.allow_multiple);
                }

                const tooltipsTextarea = newBlock.querySelector('.qual_tooltips');
                tooltipsTextarea.value = qualMain.tooltips || '';

                const subContainer = newBlock.querySelector('.qual_sub_criterias_container');
                subContainer.querySelectorAll('.qual_sub_criteria_block').forEach((block) => block.remove());

                if (qualMain.quality_sub_criterias && qualMain.quality_sub_criterias.length > 0) {
                    qualMain.quality_sub_criterias.forEach((subData) => {
                        const subBlockTemplate = document.querySelector('.qual_sub_criteria_block');
                        const subBlock = subBlockTemplate.cloneNode(true);
                        subBlock.dataset.qualitySubId = subData.quality_sub_criteria_id || subData.id || '';
                        subBlock.querySelector('.qual_sub_name').value = subData.name || '';
                        subBlock.querySelector('.num_score').value = subData.num_score ?? '';

                        const descTextarea = subBlock.querySelector('.qual_sub_description');
                        descTextarea.value = subData.description || '';

                        subContainer.appendChild(subBlock);
                    });
                }

                container.appendChild(newBlock);
            });
        }
