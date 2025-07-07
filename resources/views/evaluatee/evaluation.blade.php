@extends('layouts.user-evaluation')

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

    <form id="evaluationForm" method="POST" action="{{ route('reports.quantity-scores.store', $report->id) }}">
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

        <x-quantity-table
            :evaluationItems="$evaluationItems"
            title="ด้านปริมาณ"
            :readonly="false"
        />
        
        <x-quality-table
            :evaluationItems="$evaluationItems"
            title="ด้านคุณภาพ"
            :readonly="false"
        />

        <div class="flex justify-center gap-4 mt-8">
            <a href="{{ route('dashboard') }}" class="px-6 py-2 bg-white rounded-md text-center hover:bg-gray-200 w-40">กลับ</a>
            <button type="button" onclick="saveDraft()" class="bg-pink-400 text-white px-6 py-2 rounded-md hover:bg-pink-500 w-40">
                บันทึกร่าง
            </button>
            <button type="submit" class="bg-purple-600 text-white px-6 py-2 rounded-md hover:bg-purple-700 w-40" id="submitBtn">
                ส่งแบบประเมิน
            </button>
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
document.getElementById('quantityScoreForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    submitBtn.disabled = true;
    submitBtn.textContent = 'กำลังส่ง...';
    
    const inputs = this.querySelectorAll('input[name*="score_C"]');
    let hasValue = false;
    
    inputs.forEach(input => {
        if (input.value.trim() !== '') {
            hasValue = true;
        }
    });
    
    if (!hasValue) {
        e.preventDefault();
        alert('กรุณากรอกข้อมูลอย่างน้อย 1 รายการ');
        submitBtn.disabled = false;
        submitBtn.textContent = 'ส่งแบบประเมิน';
        return;
    }
});

function saveDraft() {
    const draftData = [];

    document.querySelectorAll('input[name*="[quantity_sub_criteria_id]"]').forEach(hiddenInput => {
        const name = hiddenInput.name; // e.g. quantity_list[0][quantity_sub_criteria_id]
        const indexMatch = name.match(/quantity_list\[(\d+)]/);
        if (!indexMatch) return;

        const index = indexMatch[1];
        const subCriteriaId = hiddenInput.value;

        const scoreInput = document.querySelector(`input[name="quantity_list[${index}][score_C]"]`);
        if (scoreInput) {
            draftData.push({
                quantity_sub_criteria_id: parseInt(subCriteriaId),
                score_C: parseFloat(scoreInput.value || 0)
            });
        }
    });

    localStorage.setItem('quantityScoreDraft', JSON.stringify(draftData));
    alert('บันทึกร่างสำเร็จแล้ว');
}

document.addEventListener('DOMContentLoaded', () => {
    const saved = localStorage.getItem('quantityScoreDraft');
    if (!saved) return;

    const draftData = JSON.parse(saved);

    draftData.forEach(item => {
        // Loop through all hidden inputs to find matching sub_criteria_id
        document.querySelectorAll('input[name*="[quantity_sub_criteria_id]"]').forEach(hiddenInput => {
            if (parseInt(hiddenInput.value) === item.quantity_sub_criteria_id) {
                const name = hiddenInput.name; // e.g. quantity_list[3][quantity_sub_criteria_id]
                const indexMatch = name.match(/quantity_list\[(\d+)]/);
                if (!indexMatch) return;

                const index = indexMatch[1];
                const scoreInput = document.querySelector(`input[name="quantity_list[${index}][score_C]"]`);
                if (scoreInput) {
                    scoreInput.value = item.score_C;
                }
            }
        });
    });
});
</script>
@endsection