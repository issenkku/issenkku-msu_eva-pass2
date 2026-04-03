<div class="workload-row-actions">
    @unless($readonly)
        <button
            type="button"
            class="workload-mini-btn workload-edit-btn"
            data-bs-toggle="modal"
            data-bs-target="#workloadAddModal"
            data-entry-id="{{ $rowView['id'] }}"
            data-form-id="{{ $rowView['form_id'] ?? '' }}"
            data-item-id="{{ $rowView['selected_item_id'] ?? '' }}"
            data-subject-id="{{ $rowView['subject_id'] ?? '' }}"
            data-group-id="{{ $rowView['group_id'] ?? '' }}"
            data-group-name="{{ $rowView['group_name'] ?? '' }}"
            data-field-values='@json($rowView['field_values'] ?? [])'
            data-evidence-links='@json(collect($rowView['evidence_links'] ?? [])->values())'
        >
            &#3649;&#3585;&#3657;&#3652;&#3586;
        </button>
        <x-button
            type="danger"
            text="&#3621;&#3610;"
            class="workload-mini-btn workload-delete-btn"
            icon="fas fa-trash-alt"
            buttonType="button"
            onclick="confirmDelete({{ $rowView['id'] }})"
        />
    @endunless
</div>
