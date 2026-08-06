<script>
    document.addEventListener('DOMContentLoaded', function () {
        const formSelect = document.querySelector('select[name="workload_form_id"]');
        const formIdField = document.getElementById('workloadFormIdField');
        const itemSelect = document.getElementById('workloadFormItemSelect');
        const hiddenItemField = document.getElementById('selectedItemField');
        const formFieldBlocks = document.querySelectorAll('.workload-form-fields');
        const workloadModalEl = document.getElementById('workloadAddModal');
        const workloadForm = document.getElementById('workloadEntryForm');
        const workloadSubmitButton = workloadForm ? workloadForm.querySelector('.modal-footer .workload-save-btn[type="submit"]') : null;
        const methodField = document.getElementById('workloadFormMethod');
        const modalTitle = document.getElementById('workloadAddModalLabel');
        const subjectIdField = document.getElementById('workloadSubjectId');
        const subjectPicker = document.getElementById('workloadSubjectPicker');
        const subjectTrigger = document.getElementById('workloadSubjectTrigger');
        const subjectTriggerText = document.getElementById('workloadSubjectTriggerText');
        const subjectDropdown = document.getElementById('workloadSubjectDropdown');
        const subjectSearchInput = document.getElementById('workloadSubjectSearch');
        const subjectClearBtn = document.getElementById('workloadSubjectClearBtn');
        const subjectOptionContainer = document.getElementById('workloadSubjectOptions');
        const subjectEmptyState = document.getElementById('workloadSubjectEmpty');
        const subjectOptions = subjectOptionContainer ? Array.from(subjectOptionContainer.querySelectorAll('.workload-subject-option')) : [];
        const subjectSection = document.getElementById('workloadSubjectSection');
        const subjectAlertBox = document.getElementById('workloadSubjectAlertBox');
        const evidenceContainer = document.getElementById('workload-evidence-links');
        const workloadRequireEvidenceFlag = document.getElementById('workloadRequireEvidenceFlag');
        const workloadRequireSubjectFlag = document.getElementById('workloadRequireSubjectFlag');
        const detailFieldsContainer = document.getElementById('workload-detail-fields');
        const groupLabelInput = document.getElementById('workloadGroupLabel');
        let lastDefaultFormId = '';
        let lastDefaultItemId = '';
        let lastDefaultGroupId = '';
        let lastDefaultGroupName = '';
        let lastDefaultRequiresSubject = false;
        let activeGroupId = '';
        let activeGroupName = '';
        let activeGroupRequiresSubject = false;
        let pendingEditPayload = null;

        // เก็บค่าเริ่มต้นจากปุ่มเพิ่มรายการ เพื่อใช้เติม modal ตอนเปิด
        document.addEventListener('click', function (event) {
            const btn = event.target.closest && event.target.closest('.workload-add-btn');
            if (!btn) {
                return;
            }

            lastDefaultFormId = btn.dataset.defaultFormId || '';
            lastDefaultItemId = btn.dataset.itemId || '';
            lastDefaultGroupId = btn.dataset.groupId || '';
            lastDefaultGroupName = btn.dataset.groupName || '';
            lastDefaultRequiresSubject = btn.dataset.requiresSubject === '1';
        });

        // แยก helper และ lifecycle ตามหน้าที่เพื่อให้ไฟล์หลักเป็น orchestration
        @include('evaluatee.partials.workload-script-entry-subject-helpers')

        @include('evaluatee.partials.workload-script-entry-form-helpers')

        @include('evaluatee.partials.workload-script-entry-interactions')

        @include('evaluatee.partials.workload-script-entry-modal-lifecycle')

        @include('evaluatee.partials.workload-script-entry-submit')

        document.addEventListener('workload:subject-created', function (event) {
            const subject = event.detail ? event.detail.subject : null;
            if (!subject || !subjectOptionContainer) {
                return;
            }

            const option = document.createElement('button');
            const subjectName = subject.name_th || subject.name_en || '';
            const displayName = subject.code + (subjectName ? ' - ' + subjectName : '');
            option.type = 'button';
            option.className = 'workload-subject-option';
            option.dataset.subjectId = String(subject.id);
            option.dataset.credits = String(subject.credits ?? 0);
            option.dataset.lectureCredits = String(subject.lecture_credits ?? 0);
            option.dataset.labCredits = String(subject.lab_credits ?? 0);
            option.dataset.selfStudyCredits = String(subject.self_study_credits ?? 0);
            option.dataset.lectureHours = String(subject.lecture_hours ?? 0);
            option.dataset.labHours = String(subject.lab_hours ?? 0);
            option.dataset.selfStudyHours = String(subject.self_study_hours ?? 0);
            option.dataset.search = displayName.toLowerCase();

            const name = document.createElement('span');
            name.className = 'workload-subject-option-name';
            name.textContent = displayName;
            const credit = document.createElement('span');
            credit.className = 'workload-subject-option-credit';
            credit.textContent = String(subject.credits ?? 0) + ' หน่วยกิต';
            const componentValues = subject.display_component_values || [
                subject.lecture_credits ?? 0,
                subject.lab_credits ?? 0,
                subject.self_study_credits ?? 0,
            ];
            credit.textContent = '(รวม ' + String(subject.credits ?? 0) + ' หน่วยกิต | บ ' +
                componentValues[0] + ' / ป ' + componentValues[1] + ' / ศ ' + componentValues[2] + ')';
            option.append(name, credit);
            option.addEventListener('click', function () {
                selectSubjectOption(option, false);
                updateWorkloadSubmitState();
            });
            subjectOptionContainer.appendChild(option);
            subjectOptions.push(option);
            selectSubjectOption(option, false);
            updateWorkloadSubmitState();
        });

        initWorkloadEntryInteractions();
        initWorkloadEntryModalLifecycle();
        initWorkloadEntrySubmit();
        initWorkloadEntryDefaults();
    });
</script>
