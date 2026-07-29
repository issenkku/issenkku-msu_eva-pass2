<?php

namespace App\Support;

use App\Rules\HasRichText;

final class SupportScoreRules
{
    /**
     * @return array<string, array<int, mixed>>
     */
    public static function validation(): array
    {
        return [
            'support_list' => ['nullable', 'array'],
            'support_list.*.support_criteria_id' => ['required', 'integer', 'exists:support_criterias,id'],
            'support_list.*.achieved_score' => ['nullable', 'integer', 'between:1,5'],
            'support_list.*.modification_reason' => ['nullable', 'string', 'max:2000'],
            'support_list.*.evidence_links' => ['nullable', 'array'],
            'support_list.*.evidence_links.*' => ['nullable', 'url:http,https'],
            'support_list.*.activity_entries' => ['nullable', 'array'],
            'support_list.*.activity_entries.*.id' => ['nullable', 'integer'],
            'support_list.*.activity_entries.*.support_indicator_item_id' => ['nullable', 'integer'],
            'support_list.*.activity_entries.*.content' => ['required', 'string', new HasRichText],
            'support_list.*.activity_entries.*.indicator' => ['nullable', 'string'],
            'support_list.*.activity_entries.*.weight' => ['nullable', 'numeric', 'decimal:0,2'],
            'support_list.*.activity_entries.*.achieved_score' => ['nullable', 'numeric', 'decimal:0,2'],
            'support_list.*.activity_entries.*.modification_reason' => ['nullable', 'string', 'max:2000'],
            'support_list.*.activity_entries.*.evidence_links' => ['nullable', 'array'],
            'support_list.*.activity_entries.*.evidence_links.*' => ['nullable', 'url:http,https'],
        ];
    }
}
