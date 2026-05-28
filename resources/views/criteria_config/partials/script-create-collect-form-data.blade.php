        let finalData = null;

        document.getElementById('jsonForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // ไม่ต้องตรวจสอบ version_name เพราะระบบจะสร้างให้อัตโนมัติ
            const reportTitle = document.querySelector('.report_title').value.trim();
            const reportDescription = document.querySelector('.report_description').value.trim();
            if (!reportTitle) {
                return;
            }

            // สร้าง version_name อัตโนมัติ
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `เกณฑ์ประเมินปี ${currentYear} ครั้งที่ AUTO`;
            
            // Save all Summernote content back to textareas before collecting data
            $('.richtext-editor').each(function() {
                if ($(this).hasClass('note-editor')) {
                    $(this).val($(this).summernote('code'));
                }
            });
            
            finalData = {
                version_name: versionName, // สร้างชื่อเวอร์ชันอัตโนมัติ
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
