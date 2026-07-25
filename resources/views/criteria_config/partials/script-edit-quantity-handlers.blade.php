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

        function updatePermanentQuantityDeleteButton(evaluationBlock) {
            const button = evaluationBlock.querySelector('.delete_all_quantity_criteria_btn');
            const quantityCheckbox = evaluationBlock.querySelector('.quantity_criteria_type');
            const evaluationId = evaluationBlock.querySelector('.evaluation_id_value')?.value;
            const hasSavedQuantity = Array.from(
                evaluationBlock.querySelectorAll('.quantity_main_id_value, .quant_sub_criteria_id')
            ).some((input) => Boolean(input.value));

            button?.classList.toggle(
                'hidden',
                quantityCheckbox.checked || !evaluationId || !hasSavedQuantity
            );
        }

        async function handleDeleteAllQuantityCriteria(evaluationBlock) {
            const button = evaluationBlock.querySelector('.delete_all_quantity_criteria_btn');
            const evaluationId = evaluationBlock.querySelector('.evaluation_id_value')?.value;
            const evaluationName = evaluationBlock.querySelector('.eval_name')?.value.trim()
                || 'รายการประเมินนี้';

            if (!evaluationId || evaluationBlock.querySelector('.quantity_criteria_type').checked) {
                updatePermanentQuantityDeleteButton(evaluationBlock);
                return;
            }

            const confirmed = confirm(
                `ต้องการลบข้อมูลเกณฑ์ปริมาณทั้งหมดของ “${evaluationName}” แบบถาวรใช่หรือไม่?\n\n` +
                'ข้อมูลที่ลบแล้วไม่สามารถกู้คืนได้'
            );
            if (!confirmed) {
                return;
            }

            button.disabled = true;

            try {
                const response = await fetch(
                    `/report-version/{{ $id ?? '' }}/evaluation-lists/${evaluationId}/quantity-criteria`,
                    {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        credentials: 'same-origin'
                    }
                );
                const data = await response.json();

                if (response.status === 409 && data.dependencies) {
                    alert(
                        `${data.message}\n\n` +
                        `แบบฟอร์มภาระงาน: ${data.dependencies.workload_forms}\n` +
                        `คะแนนปริมาณ: ${data.dependencies.quantity_scores}\n` +
                        `ประวัติคะแนนปริมาณ: ${data.dependencies.quantity_score_histories}`
                    );
                    return;
                }

                if (!response.ok) {
                    alert(data.message || 'ไม่สามารถลบข้อมูลเกณฑ์ปริมาณได้');
                    return;
                }

                const container = evaluationBlock.querySelector('.quantity_main_criterias_container');
                const cleanBlock = cloneAndClear('.quant_criteria_block');
                container.querySelectorAll('.quant_criteria_block').forEach((block) => {
                    $(block).find('.richtext-editor').each(function() {
                        if ($(this).next('.note-editor').length > 0) {
                            $(this).summernote('destroy');
                        }
                    });
                    block.remove();
                });
                container.appendChild(cleanBlock);
                updateSequences();
                updatePermanentQuantityDeleteButton(evaluationBlock);
                alert(data.message);
            } catch (error) {
                console.error('Error:', error);
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ ไม่สามารถลบข้อมูลเกณฑ์ปริมาณได้');
            } finally {
                button.disabled = false;
            }
        }
