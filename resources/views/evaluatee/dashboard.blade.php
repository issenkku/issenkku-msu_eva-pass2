@extends('layouts.app')

@section('title', 'Dashboard - ระบบประเมิน')

@section('content')
<div class="max-w-8xl mx-auto space-y-6">
    <x-profile-card
        :user="$user"
        title="ข้อมูลผู้รับการประเมิน" />

    <div class="mx-5 rounded-2xl border px-10 pb-6 pt-6 shadow-md"
        style="background: linear-gradient(135deg, #f5f3ff 0%, #fff 50%, #fdf2f8 100%); border-color: #ede9fe;">
        <div class="mb-6 flex items-center justify-between">
            <h3 class="flex items-center gap-2 text-2xl font-extrabold tracking-wide text-purple-700">
                <i class="fas fa-bell text-fuchsia-500"></i>
                การประเมินที่ยังไม่เสร็จ
            </h3>
        </div>

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
                <div class="col-span-full rounded-xl border border-gray-100 bg-white py-10 text-center shadow-inner">
                    <p class="text-lg text-gray-500">ไม่มีการประเมินที่ค้างอยู่</p>
                </div>
            @endforelse
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6 px-10 md:grid-cols-2 xl:grid-cols-4">
        <x-summary-score
            title="งานประเมินทั้งหมด"
            :value="$totalAssignments"
            subtitle="จำนวนรายการประเมินทั้งหมดของคุณ"
            color="blue"
            icon="fas fa-clipboard-list"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="งานที่ต้องทำตอนนี้"
            :value="$actionRequiredAssignments"
            subtitle="รายการที่ยังไม่เริ่มหรือกำลังกรอกอยู่"
            color="red"
            icon="fas fa-bolt"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="ใกล้ครบกำหนด"
            :value="$dueSoonAssignments"
            subtitle="รายการที่ครบกำหนดภายใน 3 วัน"
            color="yellow"
            icon="fas fa-hourglass-half"
            iconSize="text-3xl"
        />

        <x-summary-score
            title="ประเมินเสร็จแล้ว"
            :value="$completedAssignments"
            subtitle="รายการที่ดำเนินการเสร็จสมบูรณ์แล้ว"
            color="green"
            icon="fas fa-check-circle"
            iconSize="text-3xl"
        />
    </div>

    <div class="px-10">
        <div class="rounded-2xl border bg-white p-6 shadow-md">
            <h3 class="text-xl font-bold text-gray-900">สถานะของฉัน</h3>
            <div class="mt-4 space-y-3 text-sm">
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                    <span class="text-gray-600">ยังไม่เริ่ม / กำลังกรอก</span>
                    <span class="font-semibold text-red-600">{{ $actionRequiredAssignments }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                    <span class="text-gray-600">รอการพิจารณา</span>
                    <span class="font-semibold text-yellow-600">{{ $inReviewAssignments }}</span>
                </div>
                <div class="flex items-center justify-between rounded-xl bg-gray-50 px-4 py-3">
                    <span class="text-gray-600">เสร็จสิ้นแล้ว</span>
                    <span class="font-semibold text-green-600">{{ $completedAssignments }}</span>
                </div>
            </div>
        </div>
    </div>

    <x-evaluation-summary
        :evaluations="$evaluations"
        :status-counts="$statusCounts"
        :years="$years"
    />
</div>

@if(session('success'))
    <div id="successMessage" class="fixed right-4 top-4 z-[10000] transform rounded-lg bg-green-500 px-6 py-4 text-white shadow-lg transition-transform duration-300">
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
