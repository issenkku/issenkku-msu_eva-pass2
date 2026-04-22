{{-- โครงฟอร์มรับรองผล ใช้ร่วมกันระหว่างกรรมการและผู้บริหาร --}}
<div class="mx-auto max-w-4xl space-y-6">
    <x-evaluation-page-header :version-name="$versionName" />

    <x-evaluate-report-card
        :reportName="$reportName"
        :reportDescription="$reportDescription"
        :assessmentType="$assessmentType"
        :reportComment="$reportComment"
    />

    <x-evaluator-profile-card
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <x-director-profile-card
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <form id="evaluationForm" method="POST" action="{{ $formAction }}">
        @csrf
        @include('partials.evaluation-form-flash')

        @if ($readonly)
            <fieldset disabled>
        @endif

        <x-unified-director
            :categoryItems="$categoryItems"
            :readonly="$readonly"
            :evidenceMap="$evidenceMap"
            :qualityEvidenceMap="$qualityEvidenceMap"
            :workloadMap="$workloadMap"
        />

        <x-role-comment-section
            :report="$report"
            :current-role="$currentRole"
            :readonly="$readonly"
        />

        <input type="hidden" name="status" id="formStatus" value="submitted">

        @if ($readonly)
            </fieldset>
        @endif

        @include('partials.evaluation-form-action-buttons', [
            'readonly' => $readonly,
            'backHref' => $backHref,
            'draftStatus' => $draftStatus,
            'submitText' => $submitText,
        ])
    </form>
</div>
