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
        <form
            method="POST"
            action="{{ route('evaluatee.import-previous-workload', $reportId) }}"
            data-import-previous-workload-form
        >
            @csrf
            <input type="hidden" name="source_report_id" value="" data-import-previous-workload-source-input>
            <input type="hidden" name="quantity_sub_criteria_id" value="{{ $quantitySubCriteriaId }}">
            <button type="submit" class="workload-import-btn">
                <i class="fas fa-file-import"></i>
                นำเข้าจากรอบก่อนหน้า
            </button>
        </form>

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

@unless($readonly)
    <div class="workload-unsaved-confirm" data-workload-unsaved-modal hidden>
        <div class="workload-unsaved-confirm-backdrop" data-workload-unsaved-cancel></div>
        <div
            class="workload-unsaved-confirm-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="workloadUnsavedConfirmTitle"
        >
            <div class="workload-unsaved-confirm-icon">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div class="workload-unsaved-confirm-content">
                <h2 id="workloadUnsavedConfirmTitle">ยังไม่ได้บันทึกภาระงาน</h2>
                <p>
                    มีข้อมูลภาระงานที่ยังไม่ได้บันทึก หากย้อนกลับตอนนี้ข้อมูลล่าสุดอาจไม่ถูกบันทึกไว้
                </p>
            </div>
            <div class="workload-unsaved-confirm-actions">
                <button type="button" class="workload-unsaved-confirm-cancel" data-workload-unsaved-cancel>
                    อยู่หน้านี้ต่อ
                </button>
                <button type="button" class="workload-unsaved-confirm-submit" data-workload-unsaved-confirm>
                    <i class="fas fa-arrow-left"></i>
                    ย้อนกลับ
                </button>
            </div>
        </div>
    </div>

    <div class="workload-import-confirm" data-import-previous-workload-modal hidden>
        <div class="workload-import-confirm-backdrop" data-import-previous-workload-cancel></div>
        <div
            class="workload-import-confirm-dialog"
            role="dialog"
            aria-modal="true"
            aria-labelledby="workloadImportConfirmTitle"
        >
            <div class="workload-import-confirm-icon">
                <i class="fas fa-file-import"></i>
            </div>
            <div class="workload-import-confirm-content">
                <h2 id="workloadImportConfirmTitle">นำเข้าข้อมูลจากรอบก่อนหน้า</h2>
                <p>
                    ระบบจะเติมเฉพาะรายการที่ยังไม่มีในรอบนี้ และจะไม่ทับข้อมูลที่คุณกรอกไว้แล้ว
                </p>
                @if(($importablePreviousReports ?? collect())->isNotEmpty())
                    <label class="workload-import-confirm-label" for="workloadImportSourceReport">
                        เลือกรอบที่ต้องการนำเข้า
                    </label>
                    <select
                        id="workloadImportSourceReport"
                        class="workload-import-confirm-select"
                        data-import-previous-workload-source-select
                    >
                        @foreach($importablePreviousReports as $sourceReport)
                            @php
                                $sourceAssignmentData = $sourceReport->assignments?->assignmentData;
                                $sourceStart = $sourceAssignmentData?->start_time;
                                $sourceEnd = $sourceAssignmentData?->end_time;
                                $sourceStartLabel = $sourceStart ? $sourceStart->format('d/m/') . ($sourceStart->year + 543) : '-';
                                $sourceEndLabel = $sourceEnd ? $sourceEnd->format('d/m/') . ($sourceEnd->year + 543) : '-';
                            @endphp
                            <option value="{{ $sourceReport->id }}">
                                {{ $sourceStartLabel }} - {{ $sourceEndLabel }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <div class="workload-import-confirm-empty">
                        ยังไม่พบรอบก่อนหน้าที่มีเกณฑ์เดียวกัน
                    </div>
                @endif
            </div>
            <div class="workload-import-confirm-actions">
                <button type="button" class="workload-import-confirm-cancel" data-import-previous-workload-cancel>
                    ยกเลิก
                </button>
                <button type="button" class="workload-import-confirm-submit" data-import-previous-workload-confirm>
                    <i class="fas fa-check"></i>
                    นำเข้า
                </button>
            </div>
        </div>
    </div>
@endunless
