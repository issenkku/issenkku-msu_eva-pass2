@extends('layouts.app')

@section('title', 'Evaluation')

@section('content')
@include('partials.evaluation-form-styles')

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
    'submitText' => '&#3626;&#3656;&#3591;&#3649;&#3610;&#3610;&#3611;&#3619;&#3632;&#3648;&#3617;&#3636;&#3609;',
])

@include('partials.evaluation-form-loading-overlay')
@include('partials.evaluation-form-confirmation-modal')
@include('partials.evaluation-form-script', ['confirmStatus' => 'Pending'])
@endsection
