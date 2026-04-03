        document.addEventListener('click', function(e) {
            if (e.target.closest('.delete_category_btn')) {
                const block = e.target.closest('.category_block');
                const container = document.getElementById('categories_container');
                if (confirm('เธ•เนเธญเธเธเธฒเธฃเธฅเธเธซเธกเธงเธ”เธซเธกเธนเนเธเธตเนเนเธเนเธซเธฃเธทเธญเนเธกเน?')) {
            if (container.querySelectorAll('.category_block').length > 1) {
                block.remove();
                updateCategorySequence(container);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเธซเธกเธงเธ”เธซเธกเธนเนเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
                }
            }

            if (e.target.closest('.delete_eval_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
            if (container.querySelectorAll('.evaluation_list_block').length > 1) {
                block.remove();
                updateEvalSequence(container);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเธฃเธฒเธขเธเธฒเธฃเธเธฃเธฐเน€เธกเธดเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
            }

            if (e.target.closest('.delete_quant_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
            if (container.querySelectorAll('.quant_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQuantMainSequence(container);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเน€เธเธ“เธ‘เนเธเธฃเธดเธกเธฒเธ“เธซเธฅเธฑเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
            }

            if (e.target.closest('.delete_quant_sub_btn')) {
                const block = e.target.closest('.quant_sub_criteria_block');
                const container = block.closest('.quant_sub_criteria_container');
            if (container.querySelectorAll('.quant_sub_criteria_block').length > 1) {
                block.remove();
                updateQuantSubSequence(container);
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเน€เธเธ“เธ‘เนเธเธฃเธดเธกเธฒเธ“เธขเนเธญเธขเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
            }

            if (e.target.closest('.delete_qual_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
            if (container.querySelectorAll('.qual_criteria_block').length > 1) {
                // Clean up Summernote instances before removing block
                $(block).find('.richtext-editor').each(function() {
                    if ($(this).hasClass('note-editor')) {
                        $(this).summernote('destroy');
                    }
                });
                block.remove();
                updateQualMainSequence(container);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเน€เธเธ“เธ‘เนเธเธธเธ“เธ เธฒเธเธซเธฅเธฑเธเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
            }

            if (e.target.closest('.delete_qual_sub_btn')) {
                const block = e.target.closest('.qual_sub_criteria_block');
                const container = block.closest('.qual_sub_criterias_container');
            if (container.querySelectorAll('.qual_sub_criteria_block').length > 1) {
                block.remove();
                updateQualSubSequence(container);
                markDirty();
            } else {
                showValidationErrorModal('เธ•เนเธญเธเธกเธตเน€เธเธ“เธ‘เนเธเธธเธ“เธ เธฒเธเธขเนเธญเธขเธญเธขเนเธฒเธเธเนเธญเธข 1 เธฃเธฒเธขเธเธฒเธฃ');
            }
            }

            if (e.target.closest('.move_category_up_btn')) {
                const block = e.target.closest('.category_block');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('category_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                    markDirty();
                }
            }

            if (e.target.closest('.move_category_down_btn')) {
                const block = e.target.closest('.category_block');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('category_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                    updateCategorySequence(document.getElementById('categories_container'));
                    markDirty();
                }
            }

            if (e.target.closest('.move_eval_up_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('evaluation_list_block')) {
                    container.insertBefore(block, previous);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                    markDirty();
                }
            }

            if (e.target.closest('.move_eval_down_btn')) {
                const block = e.target.closest('.evaluation_list_block');
                const container = block.closest('.evaluation_lists_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('evaluation_list_block')) {
                    container.insertBefore(next, block);
                    updateEvalSequence(container);
                    updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                    markDirty();
                }
            }

            if (e.target.closest('.move_quant_up_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_quant_down_btn')) {
                const block = e.target.closest('.quant_criteria_block');
                const container = block.closest('.quantity_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('quant_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                    updateQuantMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_qual_up_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const previous = block.previousElementSibling;
                if (previous && previous.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(block, previous);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('.move_qual_down_btn')) {
                const block = e.target.closest('.qual_criteria_block');
                const container = block.closest('.quality_main_criterias_container');
                const next = block.nextElementSibling;
                if (next && next.classList.contains('qual_criteria_block')) {
                    block.parentNode.insertBefore(next, block);
                    updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                    updateQualMainSequence(container);
                    markDirty();
                }
            }

            if (e.target.closest('#add_category_btn')) {
                let newBlock = cloneAndClear('.category_block');
                document.getElementById('categories_container').appendChild(newBlock);
                updateButtonStates('.category_block', '.move_category_up_btn', '.move_category_down_btn');
                updateCategorySequence(document.getElementById('categories_container'));
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_evaluation_list_btn')) {
                let parent = e.target.closest('.category_block').querySelector('.evaluation_lists_container');
                let newBlock = cloneAndClear('.evaluation_list_block');
                parent.appendChild(newBlock);
                updateButtonStates('.evaluation_list_block', '.move_eval_up_btn', '.move_eval_down_btn');
                updateEvalSequence(parent);
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quantity_main_criterias_container');
                let newBlock = cloneAndClear('.quant_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.quant_criteria_block', '.move_quant_up_btn', '.move_quant_down_btn');
                updateQuantMainSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'เธเธฃเธธเธ“เธฒเนเธชเนเธเธณเธญเธเธดเธเธฒเธขเน€เธเธดเนเธกเน€เธ•เธดเธก...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_quant_sub_criteria_btn')) {
                let parent = e.target.closest('.quant_criteria_block').querySelector(
                    '.quant_sub_criteria_container');
                let newBlock = cloneAndClear('.quant_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQuantSubSequence(parent);
                markDirty();
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_criteria_btn')) {
                let parent = e.target.closest('.evaluation_list_block').querySelector(
                    '.quality_main_criterias_container');
                let newBlock = cloneAndClear('.qual_criteria_block');
                parent.appendChild(newBlock);
                updateButtonStates('.qual_criteria_block', '.move_qual_up_btn', '.move_qual_down_btn');
                updateQualMainSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors
                setTimeout(function() {
                    $(newBlock).find('.richtext-editor').summernote({
                        height: 250,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'เธเธฃเธธเธ“เธฒเนเธชเนเธเธณเธญเธเธดเธเธฒเธขเน€เธเธดเนเธกเน€เธ•เธดเธก...',
                        lang: 'th-TH',
                        callbacks: {
                            onChange: function(contents, $editable) {
                                $(this).val(contents);
                            }
                        }
                    });
                }, 100);
                
                newBlock.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }

            if (e.target.closest('.add_qual_sub_criteria_btn')) {
                let parent = e.target.closest('.qual_criteria_block').querySelector(
                    '.qual_sub_criterias_container');
                let newBlock = cloneAndClear('.qual_sub_criteria_block');
                parent.appendChild(newBlock);
                updateQualSubSequence(parent);
                markDirty();
                
                // Initialize Summernote for new rich text editors in the new block
                setTimeout(function() {
                    $(newBlock).find('.qual_sub_description.richtext-editor').summernote({
                        height: 200,
                        toolbar: [
                            ['style', ['style']],
                            ['font', ['bold', 'italic', 'underline', 'clear']],
                            ['fontname', ['fontname']],
                            ['color', ['color']],
                            ['para', ['ul', 'ol', 'paragraph']],
                            ['table', ['table']],
                            ['insert', ['link', 'picture']],
                            ['view', ['fullscreen', 'codeview', 'help']]
                        ],
                        placeholder: 'เนเธชเนเธเธณเธญเธเธดเธเธฒเธขเธเธฒเธฃเนเธซเนเธเธฐเนเธเธ',
