@extends('layouts.app')
@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
    @include('partials.evaluation-form-styles')

    {{-- ฟอร์มอนุมัติผลการประเมินของผู้บริหาร --}}
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
        'formAction' => route('manager_score.store', $report->id),
        'currentRole' => 'manager',
        'backHref' => auth()->user()->hasRole('admin') ? '/dashboard' : '/manager-dashboard',
        'draftStatus' => 'Manager_draft',
        'submitText' => 'รับรองการประเมิน',
    ])

    @include('partials.evaluation-form-loading-overlay')
    @include('partials.evaluation-form-confirmation-modal')
    @include('partials.evaluation-form-script', ['confirmStatus' => 'Completed'])
@endsection
