<?php

namespace App\Support;

final class SupportScoreRules
{
    /**
     * @return array<string, array<int, string>>
     */
    public static function validation(): array
    {
        return [
            'support_list' => ['nullable', 'array'],
            'support_list.*.support_criteria_id' => ['required', 'integer', 'exists:support_criterias,id'],
            'support_list.*.achieved_score' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'support_list.*.modification_reason' => ['nullable', 'string', 'max:2000'],
            'support_list.*.evidence_links' => ['nullable', 'array'],
            'support_list.*.evidence_links.*' => ['nullable', 'url:http,https'],
        ];
    }
}
