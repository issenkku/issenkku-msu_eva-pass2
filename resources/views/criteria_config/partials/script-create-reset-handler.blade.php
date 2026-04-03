        document.getElementById('reset_form_btn').addEventListener('click', function() {
            showValidationErrorModal('เธ•เนเธญเธเธเธฒเธฃเธฅเนเธฒเธเธเนเธญเธกเธนเธฅเธ—เธฑเนเธเธซเธกเธ”เนเธเนเธซเธฃเธทเธญเนเธกเน? <br><br><button id="confirm-reset-btn" class="mt-2 px-6 py-2 bg-blue-500 text-white rounded hover:bg-blue-600 focus:outline-none">เธขเธทเธเธขเธฑเธ</button>');
            setTimeout(() => {
                const confirmBtn = document.getElementById('confirm-reset-btn');
                if (confirmBtn) {
                    confirmBtn.onclick = function() {
                        document.getElementById('custom-alert-modal').style.display = 'none';
                        document.getElementById('jsonForm').reset();
                        document.querySelectorAll('.quantity_main_criterias_container, .quality_main_criterias_container')
                            .forEach(container => container.classList.add('hidden'));
                        updateCategorySequence(document.getElementById('categories_container'));
                        document.querySelectorAll('.evaluation_lists_container').forEach(updateEvalSequence);
                        document.querySelectorAll('.quantity_main_criterias_container').forEach(updateQuantMainSequence);
                        document.querySelectorAll('.quant_sub_criteria_container').forEach(updateQuantSubSequence);
                        document.querySelectorAll('.quality_main_criterias_container').forEach(updateQualMainSequence);
                        document.querySelectorAll('.qual_sub_criterias_container').forEach(updateQualSubSequence);
                    };
                }
            }, 100);
        });
