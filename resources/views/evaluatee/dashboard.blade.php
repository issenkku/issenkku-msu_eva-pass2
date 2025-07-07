@extends('layouts.dashboard')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card :user="$user"/>
    
    <!-- Evaluation Header -->
    <!-- <x-evaluation-header 
        title="รอบที่ 1 : ระหว่างวันที่ 1 เมษายน 2568 - 30 เมษายน 2568"
        period=""
        deadline="1 พฤษภาคม 2568"
    /> -->
    
    <!-- Evaluation Summary -->
    <x-evaluation-summary 
        :evaluations="$evaluations" 
        :status-counts="$statusCounts"
    />
</div>

<!-- Mobile-friendly spacing -->
<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }
    
    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}
</style>
@endsection