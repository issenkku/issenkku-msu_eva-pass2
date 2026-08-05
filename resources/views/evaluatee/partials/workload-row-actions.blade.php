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
            data-requires-subject="{{ !empty($rowView['requires_subject']) ? 1 : 0 }}"
            data-field-values='@json($rowView['field_values'] ?? [])'
            data-evidence-links='@json(collect($rowView['evidence_links'] ?? [])->values())'
        >
            <i class="fas fa-edit"></i>
            <span>&#3649;&#3585;&#3657;&#3652;&#3586;</span>
        </button>
        <button
            type="button"
            class="workload-mini-btn workload-delete-btn"
            data-delete-trigger
            data-delete-id="{{ $rowView['id'] }}"
            data-workload-item-id="{{ $itemView['id'] ?? '' }}"
        >
            <i class="fas fa-trash-alt"></i>
            <span>&#3621;&#3610;</span>
        </button>
    @endunless
</div>
