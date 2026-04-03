@unless($readonly)
    <div id="workloadSaveState" data-current-total="{{ (float) ($workloadTotalScore ?? 0) }}" data-saved-total="{{ $savedWorkloadScoreC !== null ? (float) $savedWorkloadScoreC : '' }}"></div>
    <div class="workload-save-reminder" id="workloadSaveReminder" hidden>
        <div class="workload-save-reminder-title">&#3617;&#3637;&#3586;&#3657;&#3629;&#3617;&#3641;&#3621;&#3607;&#3637;&#3656;&#3618;&#3633;&#3591;&#3652;&#3617;&#3656;&#3652;&#3604;&#3657;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;</div>
        <div class="workload-save-reminder-text" id="workloadSaveReminderText">&#3585;&#3619;&#3640;&#3603;&#3634;&#3585;&#3604;&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;&#3604;&#3657;&#3634;&#3609;&#3621;&#3656;&#3634;&#3591;&#3648;&#3614;&#3639;&#3656;&#3629;&#3618;&#3639;&#3609;&#3618;&#3633;&#3609;&#3588;&#3632;&#3649;&#3609;&#3609;&#3616;&#3634;&#3619;&#3632;&#3591;&#3634;&#3609;&#3621;&#3656;&#3634;&#3626;&#3640;&#3604;</div>
    </div>
@endunless

<div class="workload-actions">
    <a href="{{ route('evaluation.show', ['id' => $reportId, 'readonly' => !empty($readonly) ? 1 : null]) }}" class="workload-back-btn">
        <i class="fas fa-arrow-left"></i>
        &#3618;&#3657;&#3629;&#3609;&#3585;&#3621;&#3633;&#3610;
    </a>

    @unless($readonly)
        <form method="POST" id="workloadScoreForm" action="{{ route('evaluatee.workload-score.store') }}">
            @csrf
            <input type="hidden" name="report_id" value="{{ $reportId }}">
            <input type="hidden" name="quantity_sub_criteria_id" value="{{ $quantitySubCriteriaId }}">
            <button type="submit" class="workload-save-btn">
                <i class="fas fa-save"></i>
                &#3610;&#3633;&#3609;&#3607;&#3638;&#3585;
            </button>
        </form>
    @endunless
</div>
