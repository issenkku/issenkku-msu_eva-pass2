{{-- ไฟล์มุมมอง: resources/views/evaluatee/partials/workload-scripts.blade.php --}}
<x-delete-warning-modal
    text="รายการภาระงาน"
    formAction="{{ route('evaluatee.workload-entries.destroy', ':id') }}"
/>

@include('evaluatee.partials.workload-script-entry-modal')
@include('evaluatee.partials.workload-script-subject-modal')
@include('evaluatee.partials.workload-script-subject-form')
@include('evaluatee.partials.workload-script-evidence-links')
@include('evaluatee.partials.workload-script-flash-autohide')
@include('evaluatee.partials.workload-script-save-reminder')
