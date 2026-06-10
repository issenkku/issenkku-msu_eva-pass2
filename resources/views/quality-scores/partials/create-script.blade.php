@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script>
        let selectedUsers = [];
        let selectedCriterias = [];
        let userCounter = 0;

        $(document).ready(function () {
            $('#user_select').select2({ placeholder: 'เลือกผู้ใช้งานหลายคน...', allowClear: true, width: '100%' });
            $('#quality_sub_criteria_select').select2({ placeholder: 'เลือกเกณฑ์การประเมินหลายข้อ...', allowClear: true, width: '100%' });

            $('#user_select').on('change', function () {
                const selectedValues = $(this).val() || [];
                selectedUsers = [];
                selectedValues.forEach((userId) => {
                    const option = $(this).find(`option[value="${userId}"]`);
                    selectedUsers.push({ id: userId, name: option.data('name'), email: option.data('email'), counter: userCounter++ });
                });
                $('#scoreTypeContainer').toggle(selectedUsers.length > 0);
                $('#commonScoreContainer').toggle(selectedUsers.length > 0);
                updateSelectedUsersUI();
                updateSubmitButton();
            });

            $('#quality_sub_criteria_select').on('change', function () {
                const selectedValues = $(this).val() || [];
                selectedCriterias = [];
                selectedValues.forEach((criteriaId) => {
                    const option = $(this).find(`option[value="${criteriaId}"]`);
                    const optgroup = option.closest('optgroup');
                    selectedCriterias.push({ id: criteriaId, name: option.text(), mainCriteriaName: optgroup.length > 0 ? optgroup.attr('label') : '' });
                });
                $('#selectedCriteriaContainer').toggle(selectedCriterias.length > 0);
                updateSelectedCriteriaUI();
                updateSelectedUsersUI();
                updateSubmitButton();
            });

            $('input[name="score_type"]').on('change', function () {
                updateSelectedUsersUI();
                updateSubmitButton();
            });

            $('#common_score').on('input', function () {
                if ($('input[name="score_type"]:checked').val() === 'same') {
                    $('.score-input').val($(this).val());
                }
                updateSubmitButton();
            });

            const reportSelect = document.getElementById('report_id');
            if (reportSelect.value) {
                loadCriteriasByReport(reportSelect.value);
            }
            updateSubmitButton();
        });

        function loadCriteriasByReport(reportId) {
            const criteriaSelect = $('#quality_sub_criteria_select');
            if (!reportId) {
                criteriaSelect.empty().prop('disabled', true).trigger('change');
                updateSubmitButton();
                return;
            }

            criteriaSelect.empty().append('<option value="">-- กำลังโหลด... --</option>').prop('disabled', true);

            fetch(`{{ route('quality-scores.get-criteria-by-report') }}?report_id=${reportId}`)
                .then((response) => response.json())
                .then((data) => {
                    criteriaSelect.empty();
                    if (data.criterias && data.criterias.length > 0) {
                        const groupedCriterias = {};
                        data.criterias.forEach((criteria) => {
                            if (!groupedCriterias[criteria.main_criteria_name]) {
                                groupedCriterias[criteria.main_criteria_name] = [];
                            }
                            groupedCriterias[criteria.main_criteria_name].push(criteria);
                        });
                        Object.keys(groupedCriterias).forEach((mainCriteriaName) => {
                            const optgroup = $('<optgroup>').attr('label', mainCriteriaName);
                            groupedCriterias[mainCriteriaName].forEach((criteria) => {
                                optgroup.append($('<option>').attr('value', criteria.id).text(criteria.name));
                            });
                            criteriaSelect.append(optgroup);
                        });
                        criteriaSelect.prop('disabled', false);
                    } else {
                        criteriaSelect.append('<option value="">-- ไม่มีเกณฑ์การประเมินในรายงานนี้ --</option>');
                    }
                    selectedCriterias = [];
                    updateSelectedCriteriaUI();
                    updateSubmitButton();
                })
                .catch((error) => {
                    console.error('Error:', error);
                    criteriaSelect.empty().append('<option value="">-- เกิดข้อผิดพลาดในการโหลด --</option>');
                });
        }

        function removeCriteria(criteriaId) {
            const currentValues = $('#quality_sub_criteria_select').val() || [];
            $('#quality_sub_criteria_select').val(currentValues.filter((id) => id !== criteriaId)).trigger('change');
        }

        function removeUser(userId) {
            const currentValues = $('#user_select').val() || [];
            $('#user_select').val(currentValues.filter((id) => id !== userId)).trigger('change');
        }

        function updateSelectedCriteriaUI() {
            const container = document.getElementById('selectedCriteriaList');
            const noCriteriaMessage = document.getElementById('noCriteriaMessage');
            if (selectedCriterias.length === 0) {
                noCriteriaMessage.style.display = 'block';
                container.querySelectorAll('.quality-score-row').forEach((row) => row.remove());
                return;
            }
            noCriteriaMessage.style.display = 'none';
            container.querySelectorAll('.quality-score-row').forEach((row) => row.remove());
            selectedCriterias.forEach((criteria) => {
                const row = document.createElement('div');
                row.className = 'quality-score-row';
                row.innerHTML = `<div class="quality-score-row-info"><div class="quality-score-row-name">${criteria.name}</div><div class="quality-score-row-meta">หมวดหลัก: ${criteria.mainCriteriaName}</div></div><button type="button" class="quality-score-remove-button" onclick="removeCriteria('${criteria.id}')" title="ลบเกณฑ์"><i class="fas fa-times"></i></button>`;
                container.appendChild(row);
            });
        }

        function updateSelectedUsersUI() {
            const container = document.getElementById('selectedUsersContainer');
            const noUsersMessage = document.getElementById('noUsersMessage');
            const scoreType = $('input[name="score_type"]:checked').val();
            const commonScore = $('#common_score').val();

            if (selectedUsers.length === 0 || selectedCriterias.length === 0) {
                noUsersMessage.style.display = 'block';
                noUsersMessage.textContent = selectedUsers.length === 0 ? 'ยังไม่ได้เลือกผู้ใช้งาน กรุณาเลือกผู้ใช้งานจากรายการด้านบน' : 'ยังไม่ได้เลือกเกณฑ์การประเมิน กรุณาเลือกเกณฑ์การประเมินก่อน';
                container.querySelectorAll('.quality-user-score-row').forEach((row) => row.remove());
                return;
            }

            noUsersMessage.style.display = 'none';
            container.querySelectorAll('.quality-user-score-row').forEach((row) => row.remove());

            selectedUsers.forEach((user, userIndex) => {
                selectedCriterias.forEach((criteria, criteriaIndex) => {
                    const index = userIndex * selectedCriterias.length + criteriaIndex;
                    const scoreInputId = `quality_score_${index}`;
                    const scoreInputHtml = scoreType === 'same'
                        ? `<div class="quality-score-input-group"><label for="${scoreInputId}" class="quality-score-label" style="margin-bottom: 4px; font-size: 0.8rem;">คะแนน</label><input id="${scoreInputId}" type="number" name="scores[${index}]" class="quality-score-control score-input" min="0" max="100" step="0.1" value="${commonScore}" readonly style="background-color: #f8f9fa;"><input type="hidden" name="users[${index}]" value="${user.id}"><input type="hidden" name="criterias[${index}]" value="${criteria.id}"></div>`
                        : `<div class="quality-score-input-group"><label for="${scoreInputId}" class="quality-score-label" style="margin-bottom: 4px; font-size: 0.8rem;">คะแนน</label><input id="${scoreInputId}" type="number" name="scores[${index}]" class="quality-score-control score-input" min="0" max="100" step="0.1" placeholder="0.0" onchange="updateSubmitButton()"><input type="hidden" name="users[${index}]" value="${user.id}"><input type="hidden" name="criterias[${index}]" value="${criteria.id}"></div>`;

                    const row = document.createElement('div');
                    row.className = 'quality-user-score-row';
                    row.innerHTML = `<div class="quality-user-info"><div class="quality-user-name">${user.name}</div><div class="quality-user-email">${user.email}</div><div class="quality-score-user-criteria">${criteria.name}</div></div>${scoreInputHtml}<div class="d-flex flex-column gap-1"><button type="button" class="quality-score-remove-button" onclick="removeUser('${user.id}')" title="ลบผู้ใช้งาน"><i class="fas fa-user-minus"></i></button><button type="button" class="quality-score-remove-button" onclick="removeCriteria('${criteria.id}')" title="ลบเกณฑ์"><i class="fas fa-minus"></i></button></div>`;
                    container.appendChild(row);
                });
            });
        }

        function updateSubmitButton() {
            const submitBtn = document.getElementById('submitBtn');
            const reportSelect = document.getElementById('report_id');
            const scoreType = $('input[name="score_type"]:checked').val();
            let scoresValid = false;

            if (scoreType === 'same') {
                const commonScore = $('#common_score').val();
                scoresValid = commonScore && parseFloat(commonScore) >= 0 && parseFloat(commonScore) <= 100;
            } else {
                const scoreInputs = document.querySelectorAll('.score-input');
                scoresValid = scoreInputs.length > 0;
                scoreInputs.forEach((input) => {
                    if (!input.value || parseFloat(input.value) < 0 || parseFloat(input.value) > 100) {
                        scoresValid = false;
                    }
                });
            }

            submitBtn.disabled = !(selectedUsers.length > 0 && selectedCriterias.length > 0 && reportSelect.value && scoresValid);
        }

        document.getElementById('report_id').addEventListener('change', function () {
            loadCriteriasByReport(this.value);
        });

        document.getElementById('qualityScoreForm').addEventListener('submit', function (event) {
            if (selectedUsers.length === 0) {
                event.preventDefault();
                alert('กรุณาเลือกผู้ใช้งานอย่างน้อย 1 คน');
                return false;
            }
            if (selectedCriterias.length === 0) {
                event.preventDefault();
                alert('กรุณาเลือกเกณฑ์การประเมินอย่างน้อย 1 ข้อ');
                return false;
            }

            const scoreType = $('input[name="score_type"]:checked').val();
            let scoresValid = true;
            if (scoreType === 'same') {
                const commonScore = $('#common_score').val();
                if (!commonScore || parseFloat(commonScore) < 0 || parseFloat(commonScore) > 100) {
                    scoresValid = false;
                }
            } else {
                document.querySelectorAll('.score-input').forEach((input) => {
                    if (!input.value || parseFloat(input.value) < 0 || parseFloat(input.value) > 100) {
                        scoresValid = false;
                    }
                });
            }

            if (!scoresValid) {
                event.preventDefault();
                alert('กรุณาระบุคะแนนที่ถูกต้อง (0-100)');
                return false;
            }
        });
    </script>
@endpush
