@extends('layouts.dashboard')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
<div class="max-w-6xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card :user="$user"/>
    
    <!-- Additional Info Card -->
    <div class="bg-white p-6 rounded-lg border">
        <h3 class="text-lg font-semibold text-gray-800 mb-4">ข้อมูลเพิ่มเติม</h3>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="text-center md:text-left">
                <div class="text-sm text-gray-600 mb-1">รอบการประเมิน</div>
                <div class="font-medium text-lg">รอบที่ 1/2568</div>
            </div>
            <div class="text-center md:text-left">
                <div class="text-sm text-gray-600 mb-1">ความคืบหน้า</div>
                <div class="font-medium text-lg">{{ $completionPercentage }}%</div>
            </div>
            <div class="text-center md:text-left">
                <div class="text-sm text-gray-600 mb-1">สถานะโดยรวม</div>
                <div class="w-full bg-gray-200 rounded-full h-3 mt-2">
                    <div class="bg-purple-500 h-3 rounded-full transition-all duration-300" 
                         style="width: {{ $completionPercentage }}%"></div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Evaluation Header -->
    <x-evaluation-header 
        title="รอบที่ 1 : ระหว่างวันที่ 1 เมษายน 2568 - 30 เมษายน 2568"
        period=""
        deadline="1 พฤษภาคม 2568"
    />
    
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