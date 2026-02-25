@extends('layouts.app')
{{-- ไฟล์มุมมอง: resources/views\evaluatee\dashboard.blade.php --}}

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
{{-- บล็อกเนื้อหา --}}
<div class="max-w-8xl mx-auto space-y-6">
    <!-- Profile Card at Top -->
    <x-profile-card 
        :user="$user"
        title="ข้อมูลผู้รับการประเมิน"/>
    
    <!-- Evaluation Header -->
    {{-- บล็อกเนื้อหา --}}
    <div class="mx-5 px-10 pb-6 pt-6 rounded-2xl shadow-md border"
        style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
        
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center justify-between mb-6">
            <h3 class="text-2xl font-extrabold text-purple-700 tracking-wide flex items-center gap-2">
                <i class="fas fa-bell text-fuchsia-500"></i>
                การประเมินที่ยังไม่เสร็จ
            </h3>
        </div>

        {{--  --}}
        <div class="grid grid-cols-1 gap-6">
            @forelse($unfinishedAssignments as $assignment)
                <x-evaluation-header
                    :title="$assignment['title']"
                    :period="$assignment['period']"
                    :deadline="$assignment['deadline']"
                    :daysLeft="$assignment['daysLeft']"
                    :evaluationId="$assignment['id']"
                />
            @empty
                <div class="col-span-full text-center py-10 bg-white rounded-xl shadow-inner border border-gray-100">
                    <p class="text-gray-500 text-lg">
                        🎉 ไม่มีการประเมินที่ค้างอยู่
                    </p>
                </div>
            @endforelse
        </div>
    </div>
    
    {{--  --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pr-10 pl-10">
        {{--  --}}
        <div class="grid grid-cols-1 md:grid-rows-2 gap-2">
            <x-summary-score
                title="คะแนนสูงสุด"
                :value="$highestScore"
                subtitle="คะแนนสูงสุดทุกปีการประเมิน"
                color="blue"
                icon="fas fa-trophy"
                iconSize="text-3xl"
            />

            <x-summary-score
                title="คะแนนเฉลี่ย"
                :value="$averageScore"
                subtitle="คะแนนเฉลี่ยทุกปีการประเมิน"
                color="purple"
            />
        </div>

        <x-scatter-chart-component 
            :scatter-data="$scatterData"
            chart-id="myChart"
            title="กราฟการกระจายตัวของคะแนน"
        />

    </div>

    <!-- Evaluation Summary -->
    <x-evaluation-summary 
        :evaluations="$evaluations" 
        :status-counts="$statusCounts"
        :years="$years"
    />
</div>

@if(session('success'))
    {{--  --}}
    <div id="successMessage" class="fixed top-4 right-4 bg-green-500 text-white px-6 py-4 rounded-lg shadow-lg z-[10000] transform transition-transform duration-300">
        {{-- บล็อกเนื้อหา --}}
        <div class="flex items-center space-x-3">
            <svg class="h-6 w-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
            </svg>
            <span>{{ session('success') }}</span>
            <button onclick="this.parentElement.parentElement.remove()" class="ml-2 text-white hover:text-gray-200">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                </svg>
            </button>
        </div>
    </div>
@endif

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

<script>
    document.addEventListener('DOMContentLoaded', function() {
            const messages = document.querySelectorAll('#successMessage, #warningMessage, #errorMessage');
            messages.forEach(function(message) {
                setTimeout(function() {
                    if (message.parentElement) {
                        message.style.transform = 'translateX(100%)';
                        setTimeout(function() {
                            if (message.parentElement) {
                                message.remove();
                            }
                        }, 300);
                    }
                }, 5000);
            });
        });
</script>
@endsection
