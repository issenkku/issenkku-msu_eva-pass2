@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <!-- Header -->
    <div class="page-header">
        <h1>แบบประเมินผลงาน</h1>
        <p class="version">เวอร์ชัน: {{ $versionName }}</p>
    </div>

    <x-evaluate-report-card 
        :reportName="$reportName"
        :reportDescription="$reportDescription"
        :assessmentType="$assessmentType"
        :reportComment="$reportComment"
    />

    <x-evaluator-profile-card 
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <x-director-profile-card
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <form id="evaluationForm" method="POST" action="{{ route('director_score.store', $report->id) }}">
        @csrf

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4">
                <ul>
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Wrap all inputs in fieldset --}}
        @if($readonly)
            <fieldset disabled>
        @endif

        <x-unified-director
            :categoryItems="$categoryItems"
            :readonly="$readonly"
            :evidenceMap="$evidenceMap"
        />
        

        <div class="bg-purple-50 border border-blue-200 rounded-lg p-6 mt-8">
            <h3 class="text-lg font-semibold text-purple-900 mb-4 flex items-center">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                            d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z">
                    </path>
                </svg>
                ความคิดเห็นจากผู้ประเมิน
            </h3>
            @if(!$readonly)
                <textarea
                    name="comment"
                    class="bg-white form-input text-base w-full mt-2 px-3 rounded-lg p-4 border border-gray-400 focus:ring-green-500 focus:border-green-500"
                    placeholder="ระบุความความเห็นเพิ่มเติม">{{ old('comment', $assignment->report->comment ?? '') }}</textarea>
            @else
                @if(isset($report->comment) && !empty($report->comment))
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <div class="prose max-w-none text-gray-700">
                            {!! nl2br(e($report->comment)) !!}
                        </div>
                    </div>
                @else
                    <div class="bg-white rounded-lg p-4 border border-blue-100 shadow-sm">
                        <p class="text-gray-500 italic">ไม่มีความคิดเห็นเพิ่มเติม</p>
                    </div>
                @endif
            @endif
        </div> 

        <input type="hidden" name="status" id="formStatus" value="submitted">

        @if($readonly)
            </fieldset>
        @endif

        <div class="flex justify-center gap-4 mt-8">
            <x-button 
                type= defualt 
                text="ย้อนกลับ" 
                icon="fas fa-arrow-left"
                href="/director-dashboard" />
        </div>
    </form>
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
/* Header Styles */
.page-header {
    text-align: center;
    margin-bottom: 40px;
    padding-bottom: 24px;
    border-bottom: 3px solid #f3f4f6;
}

.page-header h1 {
    font-size: 28px;
    font-weight: 700;
    color: #1f2937;
    margin-bottom: 8px;
}
</style>
@endsection