@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/evaluator_dashboard/evaluator.blade.php --}}

@section('title', 'Evaluation')

@section('content')
    @include('partials.evaluation-form-styles')

    {{-- ฟอร์มประเมินของผู้ประเมิน --}}
    @include('partials.evaluation-evaluator-form', [
        'versionName' => $versionName,
        'reportName' => $reportName,
        'reportDescription' => $reportDescription,
        'assessmentType' => $assessmentType,
        'reportComment' => $reportComment,
        'startTimeFormatted' => $startTimeFormatted,
        'endTimeFormatted' => $endTimeFormatted,
        'report' => $report,
        'user' => $user,
        'assignment' => $assignment,
        'categoryItems' => $categoryItems,
        'readonly' => $readonly,
        'evidenceMap' => $evidenceMap,
        'qualityEvidenceMap' => $qualityEvidenceMap,
        'workloadMap' => $workloadMap,
        'scoreSummary' => $scoreSummary,
        'formAction' => route('evaluator.evaluator_score.store', $report->id),
        'backHref' => '/evaluator-dashboard',
        'draftStatus' => 'Evaluator_draft',
        'submitText' => 'ส่งแบบประเมิน',
    ])

    @include('partials.evaluation-form-loading-overlay')
    @include('partials.evaluation-form-confirmation-modal')
    @include('partials.evaluation-form-script', ['confirmStatus' => 'Pending'])
@endsection
