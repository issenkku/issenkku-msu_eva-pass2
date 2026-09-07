<?php

use Illuminate\Support\Facades\Blade;

function academicQualityCategoryItemsWithZeroScore(): array
{
    return [[
        'main_categories' => 'เกณฑ์สายวิชาการ',
        'sub_categories' => '',
        'evaluation_lists' => [[
            'id' => 10,
            'name' => 'คุณภาพ',
            'annotation' => null,
            'sum_score' => 5,
            'quantity_items' => [],
            'quality_items' => [[
                'id' => 11,
                'name' => 'ผลงาน',
                'tooltips' => null,
                'ratio' => 1,
                'main_calculated_score' => 0,
                'sub_criterias' => [[
                    'id' => 17,
                    'name' => 'ยังไม่ได้คะแนน',
                    'description' => null,
                    'sequence' => 1,
                    'num_score' => 5,
                    'user_selected' => true,
                    'score' => 0,
                    'score_histories' => [],
                    'evidence' => [],
                ]],
            ]],
            'support_items' => [],
        ]],
    ]];
}

test('academic reviewer forms keep a persisted zero as the unchanged quality score', function (string $component) {
    $html = Blade::render(
        '<x-dynamic-component :component="$component" :category-items="$categoryItems" :readonly="false" />',
        [
            'component' => $component,
            'categoryItems' => academicQualityCategoryItemsWithZeroScore(),
        ]
    );

    expect($html)
        ->toMatch('/<input[^>]*\sname="quality_list\[17\]\[score\]"[^>]*\svalue="0"/s')
        ->toMatch('/data-score-input-name="quality_list\[17\]\[score\]"[^>]*data-original-value="0"/s');
})->with(['unified-evaluator', 'unified-director']);
