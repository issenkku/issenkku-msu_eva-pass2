@extends('layouts.app')
@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
    @include('partials.evaluation-form-styles')

    {{-- หน้าอ่านผลการประเมินสำหรับผู้ดูแลระบบ --}}
    <div class="max-w-4xl mx-auto space-y-6">
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

        <form id="evaluationForm" method="POST" action="{{ route('director_score.store', $report->id) }}">
            @csrf
            @include('partials.evaluation-form-flash')

            @if($readonly)
                <fieldset disabled>
            @endif

            <x-unified-director
                :categoryItems="$categoryItems"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
                :qualityEvidenceMap="$qualityEvidenceMap"
                :workloadMap="$workloadMap"
            />

            @include('partials.admin-comment-section', [
                'readonly' => $readonly,
                'assignment' => $assignment,
                'report' => $report,
            ])

            <input type="hidden" name="status" id="formStatus" value="submitted">

            @if($readonly)
                </fieldset>
            @endif

            @include('partials.evaluation-form-action-buttons', [
                'readonly' => true,
                'backHref' => '/dashboard',
            ])
        </form>
    </div>
@endsection
