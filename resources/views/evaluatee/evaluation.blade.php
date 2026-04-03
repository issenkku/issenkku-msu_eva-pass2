@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
@include('partials.evaluation-form-styles')
{{-- หน้าแบบประเมินของผู้ถูกประเมิน มี modal ตรวจสอบก่อนส่งเฉพาะหน้า แต่ใช้ partial กลางร่วมกับหน้าอื่น --}}
<div class="max-w-4xl mx-auto space-y-6">
    <x-evaluation-page-header :version-name="$versionName" :show-version="false" />

    <x-evaluate-report-card
        :reportName="$reportName"
        :reportDescription="$reportDescription"
        :assessmentType="$assessmentType"
        :reportComment="$reportComment"
    />

    <x-evaluate-profile-card
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <form id="evaluationForm" method="POST" action="{{ route('evaluation_score.store', $report->id) }}">
        @csrf
        @include('partials.evaluation-form-flash')

        @if ($readonly)
            <fieldset disabled>
        @endif

        @if ($readonly)
            <x-unified-evaluator
                :categoryItems="$categoryItems"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
                :qualityEvidenceMap="$qualityEvidenceMap"
                :workloadMap="$workloadMap"
            />
        @else
            <x-unified-evaluation
                :categoryItems="$categoryItems"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
                :qualityEvidenceMap="$qualityEvidenceMap"
                :report="$report"
                :workloadMap="$workloadMap"
            />
        @endif

        @include('partials.evaluator-score-summary', ['scoreSummary' => $scoreSummary])
        @include('partials.evaluatee-evaluator-comment', ['report' => $report])

        <input type="hidden" name="status" id="formStatus" value="Draft">

        @if ($readonly)
            </fieldset>
        @endif

        @include('partials.evaluation-form-action-buttons', [
            'readonly' => $readonly,
            'backHref' => '/evaluatee-dashboard',
            'draftStatus' => 'Draft',
            'submitText' => 'ส่งแบบประเมิน',
        ])
    </form>
</div>

@include('partials.evaluation-form-loading-overlay')
@include('partials.evaluatee-confirmation-modal')
@include('partials.evaluatee-evaluation-script')
@endsection
