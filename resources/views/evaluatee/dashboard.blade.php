@extends('layouts.app')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
    @include('evaluatee.partials.overview-styles')

    <div class="max-w-8xl mx-auto space-y-6">
        <x-profile-card :user="$user" title="ข้อมูลผู้รับการประเมิน" />

        @include('evaluatee.partials.unfinished-assignments', ['unfinishedAssignments' => $unfinishedAssignments])
        @include('evaluatee.partials.overview-panel', ['evaluateeOverview' => $evaluateeOverview])

        <x-evaluation-summary :summary="$evaluationSummary" :evaluations="$evaluations" :years="$years" />
    </div>

    @include('partials.dashboard-flash-message')
    @include('evaluatee.partials.overview-script', ['evaluateeOverview' => $evaluateeOverview])
@endsection
