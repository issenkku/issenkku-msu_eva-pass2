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

        initWorkloadEntryInteractions();
        initWorkloadEntryModalLifecycle();
        initWorkloadEntrySubmit();
        initWorkloadEntryDefaults();
    });
</script>
