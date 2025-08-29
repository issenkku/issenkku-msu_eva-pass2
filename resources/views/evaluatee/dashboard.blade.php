@extends('layouts.app')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลผู้รับการประเมิน"/>
    
    <!-- Evaluation Header -->
    <!-- <x-evaluation-header 
        title="รอบที่ 1 : ระหว่างวันที่ 1 เมษายน 2568 - 30 เมษายน 2568"
        period=""
        deadline="1 พฤษภาคม 2568"
    /> -->
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pr-10 pl-10">
        <div class="grid grid-cols-1 md:grid-rows-2 gap-2">
            <x-summary-score
                title="คะแนนเฉลี่ย"
                :value="$averageScore"
                subtitle="คะแนนเฉลี่ยทุกปีการประเมิน"
                color="purple"
            />

            <x-summary-score
                title="คะแนนสูงสุด"
                :value="$highestScore"
                subtitle="คะแนนสูงสุดทุกปีการประเมิน"
                color="blue"
                icon="fas fa-trophy"
                iconSize="text-3xl"
            />
        </div>
    </div>

    
    
    <!-- Evaluation Summary -->
    <x-evaluation-summary 
        :evaluations="$evaluations" 
        :status-counts="$statusCounts"
        :years="$years"
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