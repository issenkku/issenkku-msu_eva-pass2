<div class="workload-modal-field workload-modal-field-credit" id="workload-detail-fields">
    <label class="workload-modal-label"></label>
    @foreach(($workloadModal['forms'] ?? []) as $formView)
        <div
            class="workload-form-fields"
            data-form-id="{{ $formView['id'] }}"
            data-requires-subject="{{ !empty($formView['requires_subject']) ? 1 : 0 }}"
            style="display:none;"
        >
            <div class="workload-modal-subfields">
                @foreach(($formView['fields'] ?? []) as $fieldView)
                    @if(!empty($fieldView['is_hidden_default']))
                        <input
                            type="hidden"
                            name="field_values[{{ $fieldView['variable_name'] }}]"
                            value="{{ $fieldView['default_value'] }}"
                            data-default-value="{{ $fieldView['default_value'] }}"
                        />
                    @elseif(!empty($fieldView['is_renderable_input']))
                        <div class="workload-modal-subfield">
                            <label class="workload-modal-sub-label">{{ $fieldView['label'] }}</label>
                            <input
                                type="{{ $fieldView['input_type'] }}"
                                class="workload-modal-input"
                                name="field_values[{{ $fieldView['variable_name'] }}]"
                                value=""
                                data-default-value=""
                            />
                            @if(!empty($fieldView['note']))
                                <div class="workload-modal-sub-note">{{ $fieldView['note'] }}</div>
                            @endif
                        </div>
                    @endif
                @endforeach

                @if(empty($formView['has_renderable_fields']))
                    <div class="workload-modal-subfield">
                        <label class="workload-modal-sub-label">&#3627;&#3609;&#3656;&#3623;&#3618;&#3585;&#3636;&#3605;</label>
                        <input type="number" class="workload-modal-input" name="field_values[credits]" value="0" />
                    </div>
                @endif
            </div>
        </div>
    @endforeach
</div>
