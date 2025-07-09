@extends('layouts.app')

@section('title', 'Evaluation - ระบบประเมิน')

@section('content')
<div class="max-w-4xl mx-auto space-y-6">
    <x-evaluate-profile-card 
        :evaluatorName="$evaluatorName"
        :startTimeFormatted="$startTimeFormatted"
        :endTimeFormatted="$endTimeFormatted"
        :reportName="$reportName"
        :report="$report"
        :user="$user"
        :assignment="$assignment"
        :assessmentType="$assessmentType"
    />

    <form id="evaluationForm" method="POST" action="{{ route('evaluation_score.store', $report->id) }}">
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

        <div class="space-y-8">
            <x-quantity-table
                :evaluationItems="$evaluationItems"
                title="ด้านปริมาณ"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
            />

            <x-quality-table
                :qualityItems="$qualityItems" 
                title="ด้านคุณภาพ"
                :readonly="$readonly"
                :evidenceMap="$evidenceMap"
            />
        </div>

        <input type="hidden" name="status" id="formStatus" value="submitted">

        @if($readonly)
            </fieldset>
        @endif

        <div class="flex justify-center gap-4 mt-8">
            <a href="/evaluatee-dashboard" class="px-6 py-2 bg-white rounded-md text-center hover:bg-gray-200 w-40">กลับ</a>

            @unless($readonly)
                <button type="submit" onclick="setFormStatus('Draft')" class="bg-pink-400 text-white px-6 py-2 rounded-md hover:bg-pink-500 w-40">
                    บันทึกร่าง
                </button>
                <button type="submit" onclick="setFormStatus('Pending')" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 w-40" id="submitBtn">
                    ส่งแบบประเมิน
                </button>
            @endunless
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
</style>

<script>
function setFormStatus(status) {
    document.getElementById('formStatus').value = status;
}
</script>
@endsection