<div class="workload-modal-section" id="workloadSubjectSection" @if(empty($workloadModal['requires_subject'])) style="display:none;" @endif>
    <label class="workload-modal-label">&#3619;&#3634;&#3618;&#3623;&#3636;&#3594;&#3634; <span class="required">*</span></label>
    <input type="hidden" name="subject_id" id="workloadSubjectId" value="">
    <div class="workload-subject-picker" id="workloadSubjectPicker">
        <button type="button" class="workload-subject-trigger" id="workloadSubjectTrigger" aria-expanded="false">
            <span class="workload-subject-trigger-text" id="workloadSubjectTriggerText">-- &#3648;&#3621;&#3639;&#3629;&#3585;&#3619;&#3634;&#3618;&#3623;&#3636;&#3594;&#3634; --</span>
            <span class="workload-subject-trigger-icon" aria-hidden="true"></span>
        </button>
        <div class="workload-subject-dropdown" id="workloadSubjectDropdown" hidden>
            <div class="workload-subject-search-row">
                <input
                    type="text"
                    class="workload-modal-input"
                    id="workloadSubjectSearch"
                    placeholder="&#3588;&#3657;&#3609;&#3627;&#3634;&#3619;&#3634;&#3618;&#3623;&#3636;&#3594;&#3634; / &#3619;&#3627;&#3633;&#3626;&#3623;&#3636;&#3594;&#3634;"
                    autocomplete="off"
                />
                <button type="button" class="workload-subject-clear-btn" id="workloadSubjectClearBtn">&#3621;&#3657;&#3634;&#3591;</button>
            </div>
            <div class="workload-subject-options" id="workloadSubjectOptions">
                @foreach(($workloadModal['subjects'] ?? []) as $subjectView)
                    <button
                        type="button"
                        class="workload-subject-option"
                        data-subject-id="{{ $subjectView['id'] }}"
                        data-credits="{{ $subjectView['credits'] }}"
                        data-lecture-credits="{{ $subjectView['lecture_credits'] }}"
                        data-lab-credits="{{ $subjectView['lab_credits'] }}"
                        data-self-study-credits="{{ $subjectView['self_study_credits'] }}"
                        data-search="{{ $subjectView['search'] }}"
                    >
                        <span class="workload-subject-option-name">
                            {{ $subjectView['code'] }} {{ $subjectView['name_th'] }}{{ !empty($subjectView['name_en']) ? ' ' . $subjectView['name_en'] : '' }}
                        </span>
                        <span class="workload-subject-option-credit">
                            (&#3619;&#3623;&#3617; {{ $subjectView['credits'] ?: '-' }} &#3627;&#3609;&#3656;&#3623;&#3618;&#3585;&#3636;&#3605; | &#3610; {{ $subjectView['lecture_credits'] }} / &#3611; {{ $subjectView['lab_credits'] }} / &#3624; {{ $subjectView['self_study_credits'] }})
                        </span>
                    </button>
                @endforeach
            </div>
            <div class="workload-subject-empty" id="workloadSubjectEmpty" hidden>&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3623;&#3636;&#3594;&#3634;&#3607;&#3637;&#3656;&#3588;&#3657;&#3609;&#3627;&#3634;</div>
        </div>
    </div>
</div>

<div class="workload-alert-box" id="workloadSubjectAlertBox" @if(empty($workloadModal['requires_subject'])) style="display:none;" @endif>
    <div class="workload-alert-icon">
        <i class="fas fa-exclamation-triangle"></i>
    </div>
    <div class="workload-alert-text">
        &#3627;&#3634;&#3585;&#3652;&#3617;&#3656;&#3614;&#3610;&#3619;&#3634;&#3618;&#3623;&#3636;&#3594;&#3634;&#3607;&#3637;&#3656;&#3605;&#3657;&#3629;&#3591;&#3585;&#3634;&#3619;&#3648;&#3614;&#3636;&#3656;&#3617;
        <a href="#" class="workload-alert-link workload-open-subject-modal">&#3588;&#3621;&#3636;&#3585;&#3607;&#3637;&#3656;&#3609;&#3637;&#3656;</a>
        &#3648;&#3614;&#3639;&#3656;&#3629;&#3648;&#3614;&#3636;&#3656;&#3617;&#3604;&#3657;&#3623;&#3618;&#3605;&#3609;&#3648;&#3629;&#3591;
    </div>
</div>
