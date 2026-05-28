@extends('layouts.app')

@section('title', 'ภาระงานด้านงานสอน')

@section('content')
<div class="workload-background-page">
<div class="max-w-6xl mx-auto space-y-6">
    @if(!empty($readonly))
        <div class="workload-readonly-banner">
            หน้านี้เป็นโหมดอ่านอย่างเดียว จึงไม่สามารถเพิ่ม แก้ไข หรือลบข้อมูลด้านปริมาณได้
        </div>
    @endif

    @include('evaluatee.partials.workload-page-header', [
        'title' => $quantitySubCriteria->name ?? '-',
    ])

    @include('evaluatee.partials.workload-flash-messages')

    @if(isset($quantitySubCriteria) && $quantitySubCriteria)
        @if(!empty($quantitySubCriteria->require_evidence))
            <div class="rounded-xl border border-amber-200 bg-amber-50 px-4 py-3 text-amber-800">
                เกณฑ์นี้กำหนดให้แนบหลักฐานก่อนบันทึกข้อมูลภาระงาน
            </div>
        @endif

        @include('evaluatee.partials.workload-group-panels')
    @endif

    @include('evaluatee.partials.workload-summary-panel', [
        'totalDisplay' => $workloadView['total_display'] ?? '-',
    ])
    @include('evaluatee.partials.workload-actions', [
        'readonly' => $readonly,
        'reportId' => $reportId,
        'quantitySubCriteriaId' => $quantitySubCriteriaId,
        'workloadTotalScore' => $workloadTotalScore ?? 0,
        'savedWorkloadScoreC' => $savedWorkloadScoreC,
    ])
</div>
</div>

@include('evaluatee.partials.workload-entry-modal')
<x-subject-modal />
@include('evaluatee.partials.workload-styles')
@include('evaluatee.partials.workload-scripts')
@endsection
