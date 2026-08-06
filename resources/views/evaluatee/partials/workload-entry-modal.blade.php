@unless($readonly)
<div class="modal fade" id="workloadAddModal" tabindex="-1" aria-labelledby="workloadAddModalLabel" aria-hidden="true" data-bs-focus="false">
    <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable modal-fullscreen-sm-down modal-lg workload-modal-dialog">
        <div class="modal-content workload-modal-content">
            <form class="workload-modal-form" method="POST" id="workloadEntryForm" action="{{ route('evaluatee.workload-entries.store') }}" data-store-url="{{ route('evaluatee.workload-entries.store') }}" data-update-url="{{ route('evaluatee.workload-entries.update', '__id__') }}">
                @csrf
                <input type="hidden" id="workloadFormMethod" name="_method" value="">
                <input type="hidden" id="workloadRequireEvidenceFlag" value="{{ !empty($workloadModal['requires_evidence']) ? 1 : 0 }}">
                <input type="hidden" id="workloadRequireSubjectFlag" value="{{ !empty($workloadModal['requires_subject']) ? 1 : 0 }}">

                @include('evaluatee.partials.workload-entry-modal-header')

                <div class="modal-body workload-modal-body">
                    <input type="hidden" name="report_id" value="{{ $reportId }}">

                    @include('evaluatee.partials.workload-entry-modal-subject-section')

                    <div class="workload-modal-grid">
                        <div class="workload-modal-field workload-modal-field-workload">
                            <label for="workloadFormItemSelect" class="workload-modal-label">&#3616;&#3634;&#3619;&#3632;&#3591;&#3634;&#3609; <span class="required">*</span></label>
                            <select class="workload-modal-select" name="workload_form_item_id" id="workloadFormItemSelect" required>
                                <option value="">-- &#3648;&#3621;&#3639;&#3629;&#3585;&#3616;&#3634;&#3619;&#3632;&#3591;&#3634;&#3609; --</option>
                                @foreach(($workloadModal['workload_item_options'] ?? []) as $itemOption)
                                    <option
                                        value="{{ $itemOption['id'] }}"
                                        data-form-id="{{ $itemOption['form_id'] }}"
                                        data-group-id="{{ $itemOption['group_id'] }}"
                                        data-requires-subject="{{ !empty($itemOption['requires_subject']) ? 1 : 0 }}"
                                        data-sequence="{{ $itemOption['sequence'] }}"
                                        data-variable-name="{{ $itemOption['variable_name'] }}"
                                        data-score="{{ $itemOption['score'] }}"
                                    >
                                        {{ $itemOption['label'] }} ({{ $itemOption['score'] }})
                                    </option>
                                @endforeach
                            </select>
                            <input type="hidden" name="workload_form_id" id="workloadFormIdField" value="">
                            <input type="hidden" id="selectedItemField" value="">
                        </div>

                        @include('evaluatee.partials.workload-entry-modal-evidence-section')

                        @include('evaluatee.partials.workload-entry-modal-detail-fields')
                    </div>
                </div>

                <div class="modal-footer workload-modal-footer">
                    <button type="button" class="workload-cancel-btn" data-bs-dismiss="modal">&#3618;&#3585;&#3648;&#3621;&#3636;&#3585;</button>
                    <button type="submit" class="workload-save-btn">&#3610;&#3633;&#3609;&#3607;&#3638;&#3585;</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endunless
