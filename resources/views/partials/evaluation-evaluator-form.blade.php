<div class="max-w-4xl mx-auto space-y-6">
    <x-evaluation-page-header :version-name="$versionName" :show-version="false" />

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

    <form id="evaluationForm" method="POST" action="{{ $formAction }}">
        @csrf
        @include('partials.evaluation-form-flash')

        @if ($readonly)
            <fieldset disabled>
        @endif

        <x-unified-evaluator
            :categoryItems="$categoryItems"
            :readonly="$readonly"
            :evidenceMap="$evidenceMap"
            :qualityEvidenceMap="$qualityEvidenceMap"
            :workloadMap="$workloadMap"
        />

        @include('partials.evaluator-score-summary', ['scoreSummary' => $scoreSummary])

        <x-role-comment-section
            :report="$report"
            current-role="evaluator"
            :readonly="$readonly"
        />

        <input type="hidden" name="status" id="formStatus" value="{{ $draftStatus }}">
        <input type="hidden" id="is-readonly" value="{{ $readonly ? 1 : 0 }}">

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
