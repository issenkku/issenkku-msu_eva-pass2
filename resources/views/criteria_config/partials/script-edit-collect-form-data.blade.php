        function collectFormData() {
            const reportDataPayload = {
                report_title: document.getElementById('report_title').value.trim(),
                report_description: document.getElementById('report_description').value.trim(),
                assessment_type: document.getElementById('assessment_type').value.trim(),
                comment: document.getElementById('comment').value.trim() || null
            };
            if (currentReportDataId) {
                reportDataPayload.report_data_id = Number(currentReportDataId);
            }

            const formData = {
                version_name: document.getElementById('version_name').value.trim(),
                created_by: {{ Auth::user()->id }},
                report_datas: [reportDataPayload],
                categories: []
            };

            Array.from(document.querySelectorAll('.category_block:not([style*="display: none"])'))
                .sort((left, right) => compareSequenceValues(
                    getSequenceValue(left, '.category_sequence', 1),
                    getSequenceValue(right, '.category_sequence', 1)
                ))
                .forEach((catBlock, catIndex) => {
                    const mainCategories = catBlock.querySelector('.main_categories').value.trim();
                    const subCategories = catBlock.querySelector('.sub_categories').value.trim();

                    if (!mainCategories || !subCategories) {
                        throw new Error(`กรุณากรอกชื่อหมวดหมู่หลักและหมวดหมู่ย่อยสำหรับหมวดหมู่ที่ ${catIndex + 1}`);
                    }

                    const category = {
                        main_categories: mainCategories,
                        sub_categories: subCategories,
                        sequence: catIndex + 1,
                        evaluation_lists: []
                    };
                    const categoryId = catBlock.querySelector('.category_id_value')?.value || catBlock.dataset.categoryId;
                    if (categoryId) {
                        category.categorie_id = Number(categoryId);
                    }

                    Array.from(catBlock.querySelectorAll('.evaluation_list_block'))
                        .sort((left, right) => compareSequenceValues(
                            getSequenceValue(left, '.eval_sequence', `${catIndex + 1}.1`),
                            getSequenceValue(right, '.eval_sequence', `${catIndex + 1}.1`)
                        ))
                        .forEach((evalBlock, evalIndex) => {
                            const evalName = evalBlock.querySelector('.eval_name').value.trim();
                            const sumScore = evalBlock.querySelector('.sum_score').value;

                            if (!evalName || sumScore === '') {
                                throw new Error(`กรุณากรอกชื่อรายการและคะแนนรวมสำหรับรายการที่ ${evalIndex + 1} ในหมวดหมู่ที่ ${catIndex + 1}`);
                            }

                            const evalData = {
                                name: evalName,
                                sum_score: parseFloat(sumScore),
                                sequence: evalIndex + 1,
                                annotation: evalBlock.querySelector('.annotation').value.trim() || null,
                                quantity_main_criterias: [],
                                quality_main_criterias: []
                            };
                            const evaluationId = evalBlock.querySelector('.evaluation_id_value')?.value || evalBlock.dataset.evaluationId;
                            if (evaluationId) {
                                evalData.evaluation_id = Number(evaluationId);
                            }

                            if (evalBlock.querySelector('.quantity_criteria_type').checked) {
                                Array.from(evalBlock.querySelectorAll('.quant_criteria_block'))
                                    .sort((left, right) => compareSequenceValues(
                                        getSequenceValue(left, '.quant_main_sequence', `${catIndex + 1}.${evalIndex + 1}.1`),
                                        getSequenceValue(right, '.quant_main_sequence', `${catIndex + 1}.${evalIndex + 1}.1`)
                                    ))
                                    .forEach((quantBlock, quantIndex) => {
                                        const quantName = quantBlock.querySelector('.quant_name').value.trim();
                                        const tooltipsElement = quantBlock.querySelector('.quant_tooltips');
                                        const quantTooltips = getRichTextValue(tooltipsElement);
                                        const quantFormula = quantBlock.querySelector('.quant_formula').value.trim();

                                        if (!quantName) {
                                            throw new Error(`กรุณากรอกข้อมูลเกณฑ์ปริมาณหลักที่ ${quantIndex + 1}`);
                                        }

                                        const quantMain = {
                                            name: quantName,
                                            tooltips: quantTooltips || null,
                                            formula: quantFormula || 'D = A x C / B',
                                            quantity_sub_criterias: []
                                        };
                                        const quantityMainId = quantBlock.querySelector('.quantity_main_id_value')?.value || quantBlock.dataset.quantityMainId;
                                        if (quantityMainId) {
                                            quantMain.quantity_main_criteria_id = Number(quantityMainId);
                                        }

                                        Array.from(quantBlock.querySelectorAll('.quant_sub_criteria_block'))
                                            .sort((left, right) => compareSequenceValues(
                                                getSequenceValue(left, '.quant_sub_sequence', `${catIndex + 1}.${evalIndex + 1}.${quantIndex + 1}.1`),
                                                getSequenceValue(right, '.quant_sub_sequence', `${catIndex + 1}.${evalIndex + 1}.${quantIndex + 1}.1`)
                                            ))
                                            .forEach((subBlock, subIndex) => {
                                                const subName = subBlock.querySelector('.quant_sub_name').value.trim();
                                                const scoreA = subBlock.querySelector('.score_a').value;
                                                const scoreB = subBlock.querySelector('.score_b').value;

                                                if (!subName || scoreA === '' || scoreB === '') {
                                                    throw new Error(`กรุณากรอกข้อมูลเกณฑ์ปริมาณย่อยที่ ${subIndex + 1}`);
                                                }

                                                const quantSubPayload = {
                                                    name: subName,
                                                    sequence: subIndex + 1,
                                                    score_a: parseFloat(scoreA),
                                                    score_b: parseFloat(scoreB),
                                                    require_evidence: subBlock.querySelector('.quant_require_evidence')?.checked || false,
                                                };
                                                const quantSubIdInput = subBlock.querySelector('.quant_sub_criteria_id');
                                                if (quantSubIdInput && quantSubIdInput.value) {
                                                    quantSubPayload.quantity_sub_criteria_id = Number(quantSubIdInput.value);
                                                }
                                                quantMain.quantity_sub_criterias.push(quantSubPayload);
                                            });

                                        evalData.quantity_main_criterias.push(quantMain);
                                    });
                            }

                            if (evalBlock.querySelector('.quality_criteria_type').checked) {
                                Array.from(evalBlock.querySelectorAll('.qual_criteria_block'))
                                    .sort((left, right) => compareSequenceValues(
                                        getSequenceValue(left, '.qual_main_sequence', `${catIndex + 1}.${evalIndex + 1}.1`),
                                        getSequenceValue(right, '.qual_main_sequence', `${catIndex + 1}.${evalIndex + 1}.1`)
                                    ))
                                    .forEach((qualBlock, qualIndex) => {
                                        const qualName = qualBlock.querySelector('.qual_name').value.trim();
                                        const qualRatio = qualBlock.querySelector('.qual_ratio').value;
                                        const tooltipsElement = qualBlock.querySelector('.qual_tooltips');
                                        const qualTooltips = getRichTextValue(tooltipsElement);

                                        if (!qualName || qualRatio === '') {
                                            throw new Error(`กรุณากรอกชื่อเกณฑ์และสัดส่วนคะแนนสำหรับเกณฑ์คุณภาพหลักที่ ${qualIndex + 1}`);
                                        }

                                        const qualMain = {
                                            name: qualName,
                                            ratio: parseInt(qualRatio),
                                            tooltips: qualTooltips || null,
                                            sequence: qualIndex + 1,
                                            require_evidence: qualBlock.querySelector('.qual_require_evidence')?.checked || false,
                                            allow_multiple: qualBlock.querySelector('.qual_allow_multiple')?.checked || false,
                                            quality_sub_criterias: []
                                        };
                                        const qualityMainId = qualBlock.querySelector('.quality_main_id_value')?.value || qualBlock.dataset.qualityMainId;
                                        if (qualityMainId) {
                                            qualMain.quality_main_criteria_id = Number(qualityMainId);
                                        }

                                        Array.from(qualBlock.querySelectorAll('.qual_sub_criteria_block'))
                                            .sort((left, right) => compareSequenceValues(
                                                getSequenceValue(left, '.qual_sub_sequence', `${catIndex + 1}.${evalIndex + 1}.${qualIndex + 1}.1`),
                                                getSequenceValue(right, '.qual_sub_sequence', `${catIndex + 1}.${evalIndex + 1}.${qualIndex + 1}.1`)
                                            ))
                                            .forEach((subBlock, subIndex) => {
                                                const subName = subBlock.querySelector('.qual_sub_name').value.trim();
                                                const numScore = subBlock.querySelector('.num_score').value;
                                                const descriptionTextarea = subBlock.querySelector('.qual_sub_description');
                                                const subDescription = getRichTextValue(descriptionTextarea);

                                                if (!subName || numScore === '') {
                                                    throw new Error(`กรุณากรอกข้อมูลเกณฑ์คุณภาพย่อยที่ ${subIndex + 1}`);
                                                }

                                                const qualitySubPayload = {
                                                    name: subName,
                                                    sequence: subIndex + 1,
                                                    num_score: parseFloat(numScore),
                                                    description: subDescription
                                                };
                                                const qualitySubId = subBlock.dataset.qualitySubId;
                                                if (qualitySubId) {
                                                    qualitySubPayload.quality_sub_criteria_id = Number(qualitySubId);
                                                }
                                                qualMain.quality_sub_criterias.push(qualitySubPayload);
                                            });

                                        evalData.quality_main_criterias.push(qualMain);
                                    });
                            }

                            if (evalBlock.querySelector('.support_criteria_type').checked) {
                                evalData.support_criterias = [];
                                Array.from(evalBlock.querySelectorAll('.support_criteria_block'))
                                    .sort((left, right) => compareSequenceValues(
                                        getSequenceValue(left, '.support_sequence', `${catIndex + 1}.${evalIndex + 1}.1`),
                                        getSequenceValue(right, '.support_sequence', `${catIndex + 1}.${evalIndex + 1}.1`)
                                    ))
                                    .forEach((supportBlock, supportIndex) => {
                                        const activityName = getRichTextValue(supportBlock.querySelector('.support_activity_name'));
                                        const indicator = getRichTextValue(supportBlock.querySelector('.support_indicator'));
                                        const targetValue = supportBlock.querySelector('.support_target_value').value;
                                        const weight = supportBlock.querySelector('.support_weight').value;

                                        if (!hasVisibleRichText(activityName) || !hasVisibleRichText(indicator) || targetValue === '' || weight === '') {
                                            throw new Error(`กรุณากรอกข้อมูลเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ให้ครบถ้วน`);
                                        }
                                        if (Number(targetValue) < 0) {
                                            throw new Error(`ระดับค่าเป้าหมายของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องไม่ติดลบ`);
                                        }
                                        if (Number(weight) <= 0 || Number(weight) > 100) {
                                            throw new Error(`น้ำหนักของเกณฑ์สายสนับสนุนที่ ${supportIndex + 1} ต้องมากกว่า 0 และไม่เกิน 100`);
                                        }

                                        const supportPayload = {
                                            sequence: supportIndex + 1,
                                            activity_name: activityName,
                                            indicator,
                                            target_value: Number(targetValue),
                                            weight: Number(weight),
                                            require_evidence: supportBlock.querySelector('.support_require_evidence')?.checked || false,
                                        };
                                        const supportCriteriaId = supportBlock.querySelector('.support_criteria_id').value;
                                        if (supportCriteriaId) {
                                            supportPayload.support_criteria_id = Number(supportCriteriaId);
                                        }
                                        evalData.support_criterias.push(supportPayload);
                                    });
                            }

                            category.evaluation_lists.push(evalData);
                        });

                    formData.categories.push(category);
                });

            return formData;
        }
