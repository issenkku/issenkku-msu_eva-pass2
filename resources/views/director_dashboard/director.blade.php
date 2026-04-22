@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views/director_dashboard/director.blade.php --}}

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
    @include('partials.evaluation-form-styles')

    {{-- ฟอร์มอนุมัติผลการประเมินของกรรมการ --}}
    @include('partials.evaluation-approval-form', [
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
        'formAction' => route('director_score.store', $report->id),
        'currentRole' => 'director',
        'backHref' => '/director-dashboard',
        'draftStatus' => 'Director_draft',
        'submitText' => 'รับรองการประเมิน',
    ])

    @include('partials.evaluation-form-loading-overlay')
    @include('partials.evaluation-form-confirmation-modal')
    @include('partials.evaluation-form-script', ['confirmStatus' => 'Manager_assign'])
@endsection
