<script>
    let originalData = null;
    let currentReportDataId = null;
    let isInitialDataLoaded = false;
    let isDirty = false;
    let isSubmitting = false;
    let suppressDirtyTracking = true;
    @include('criteria_config.partials.script-dirty-state', [
        'formId' => 'editForm',
        'guardExpression' => 'suppressDirtyTracking || isSubmitting || !isInitialDataLoaded',
        'trackSummernote' => true,
    ])
    @include('criteria_config.partials.script-runtime-form-a11y-helpers')
    @include('criteria_config.partials.script-edit-summernote-helpers')
    document.addEventListener('DOMContentLoaded', function () {
        observeRuntimeFormFields('editForm');
        fetchVersionDetails();
        setupEventListeners();
        setupLazySummernote();
        setupUnsavedChangesProtection();
    });

    @include('criteria_config.partials.script-edit-event-listeners')
    @include('criteria_config.partials.script-edit-clone-helpers')

    @include('criteria_config.partials.script-sequence-helpers')

    @include('criteria_config.partials.script-edit-sequence-updaters')

    @include('criteria_config.partials.script-edit-drag-handlers')
    @include('criteria_config.partials.script-edit-category-handlers')
    @include('criteria_config.partials.script-edit-evaluation-handlers')
    @include('criteria_config.partials.script-edit-criteria-type-handler')

    // Quantity criteria handlers
    @include('criteria_config.partials.script-edit-quantity-handlers')
    @include('criteria_config.partials.script-edit-quality-handlers')
    @include('criteria_config.partials.script-edit-support-handlers')
    @include('criteria_config.partials.script-edit-fetch-version')

    @include('criteria_config.partials.script-edit-populate-helpers')
    @include('criteria_config.partials.script-loading-helpers')

    @include('criteria_config.partials.script-edit-feedback-helpers')
    @include('criteria_config.partials.script-edit-data-helpers')
    @include('criteria_config.partials.script-edit-submit-handler')
    @include('criteria_config.partials.script-edit-collect-form-data')
</script>
