        let finalData = null;

        document.getElementById('jsonForm').addEventListener('submit', function(event) {
            event.preventDefault();

            // เนเธกเนเธ•เนเธญเธเธ•เธฃเธงเธเธชเธญเธ version_name เน€เธเธฃเธฒเธฐเธเธฐ generate เธญเธฑเธ•เนเธเธกเธฑเธ•เธด
            const reportTitle = document.querySelector('.report_title').value.trim();
            const reportDescription = document.querySelector('.report_description').value.trim();
            if (!reportTitle) {
                //alert('เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเน€เธเธ“เธ‘เนเนเธฅเธฐเธฃเธฒเธขเธฅเธฐเน€เธญเธตเธขเธ”เน€เธเธ“เธ‘เน');
                return;
            }

            // Generate version_name เธญเธฑเธ•เนเธเธกเธฑเธ•เธด
            const currentYear = new Date().getFullYear() + 543; // Convert to Buddhist Era
            const versionName = `เน€เธเธ“เธ‘เนเธเธฃเธฐเน€เธกเธดเธเธเธต ${currentYear} เธเธฃเธฑเนเธเธ—เธตเน AUTO`;
            
            // Save all Summernote content back to textareas before collecting data
            $('.richtext-editor').each(function() {
                if ($(this).hasClass('note-editor')) {
                    $(this).val($(this).summernote('code'));
                }
            });
            
            finalData = {
                version_name: versionName, // เธชเธฃเนเธฒเธเธเธทเนเธญเธญเธฑเธ•เนเธเธกเธฑเธ•เธด
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
                    //alert(`เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธซเธกเธงเธ”เธซเธกเธนเนเธซเธฅเธฑเธเนเธฅเธฐเธซเธกเธงเธ”เธซเธกเธนเนเธขเนเธญเธขเธชเธณเธซเธฃเธฑเธเธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`);
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
                        // alert(
                        //     `เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเนเธฅเธฐเธเธฐเนเธเธเธฃเธงเธกเธชเธณเธซเธฃเธฑเธเธฃเธฒเธขเธเธฒเธฃเธ—เธตเน ${evalI + 1} เนเธเธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`
                        // );
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
                                    // alert(
                                    //     `เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเน€เธเธ“เธ‘เนเนเธฅเธฐเธเธณเธญเธเธดเธเธฒเธขเธชเธณเธซเธฃเธฑเธเน€เธเธ“เธ‘เนเธเธฃเธดเธกเธฒเธ“เธซเธฅเธฑเธเธ—เธตเน ${qj + 1} เนเธเธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธ—เธตเน ${evalI + 1} เธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`
                                    // );
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
                                            `เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเน€เธเธ“เธ‘เนเธขเนเธญเธข, เธเธฐเนเธเธ A, เนเธฅเธฐเธเธฐเนเธเธ B เธชเธณเธซเธฃเธฑเธเน€เธเธ“เธ‘เนเธเธฃเธดเธกเธฒเธ“เธขเนเธญเธขเธ—เธตเน ${sk + 1} เนเธเน€เธเธ“เธ‘เนเธเธฃเธดเธกเธฒเธ“เธซเธฅเธฑเธเธ—เธตเน ${qj + 1} เธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธ—เธตเน ${evalI + 1} เธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`
                                        );
                                        valid = false;
                                        return;
                                    }

                                    quantMain.quantity_sub_criterias.push({
                                        name: subName,
                                        sequence: sk + 1,
                                        score_a: Number(scoreA),
                                        score_b: Number(scoreB),
                                        require_evidence: subQ.querySelector('.quant_require_evidence')?.checked || false,
                                        require_subject: subQ.querySelector('.quant_require_subject')?.checked || false
                                    });
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
                                    `เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเน€เธเธ“เธ‘เนเนเธฅเธฐเธชเธฑเธ”เธชเนเธงเธเธเธฐเนเธเธเธชเธณเธซเธฃเธฑเธเน€เธเธ“เธ‘เนเธเธธเธ“เธ เธฒเธเธซเธฅเธฑเธเธ—เธตเน ${qj + 1} เนเธเธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธ—เธตเน ${evalI + 1} เธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`
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
                                    showValidationErrorModal(`เธเธฃเธธเธ“เธฒเธเธฃเธญเธเธเธทเนเธญเน€เธเธ“เธ‘เนเธขเนเธญเธขเนเธฅเธฐเธเธฐเนเธเธเธชเธนเธเธชเธธเธ”เธชเธณเธซเธฃเธฑเธเน€เธเธ“เธ‘เนเธเธธเธ“เธ เธฒเธเธขเนเธญเธขเธ—เธตเน ${sk + 1} เนเธเน€เธเธ“เธ‘เนเธเธธเธ“เธ เธฒเธเธซเธฅเธฑเธเธ—เธตเน ${qj + 1} เธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธ—เธตเน ${evalI + 1} เธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`);
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
                    showValidationErrorModal(`เธเธฃเธธเธ“เธฒเน€เธเธดเนเธกเธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃเนเธเธซเธกเธงเธ”เธซเธกเธนเนเธ—เธตเน ${catI + 1}`);
                    return;
                }

                finalData.categories.push(category);
            });

            if (finalData.categories.length === 0) {
                showValidationErrorModal('เธเธฃเธธเธ“เธฒเน€เธเธดเนเธกเธซเธกเธงเธ”เธซเธกเธนเนเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธซเธกเธงเธ”เธซเธกเธนเน');
                return;
            }

            showConfirmModal(reportTitle); // เนเธชเธ”เธ report_title เนเธ—เธ finalData.version_name
        });
