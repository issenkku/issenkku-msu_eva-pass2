<?php

use Symfony\Component\Process\Process;

test('criteria config index renders data-hook based copy and delete controls', function () {
    $html = view('criteria_config.index')->render();

    expect($html)
        ->toContain('data-copy-criteria-version')
        ->toContain('data-delete-criteria-version')
        ->toContain('window.__criteriaConfigActionHooksBound')
        ->not->toContain('onclick="copyCriteriaVersion(')
        ->not->toContain('onclick="showDeleteModal(');
});

test('criteria version copy payload preserves quantity activation for every evaluation list', function () {
    $script = file_get_contents(
        resource_path('views/criteria_config/partials/index-script.blade.php')
    );
    $functionStart = strpos($script, 'function buildCopyPayload');
    $functionEnd = strpos($script, 'function copyCriteriaVersion', $functionStart);
    $buildCopyPayload = substr($script, $functionStart, $functionEnd - $functionStart);

    $sourceData = [
        'report_datas' => [],
        'categories' => [[
            'main_categories' => 'Category',
            'sub_categories' => 'Subcategory',
            'sequence' => 1,
            'evaluation_lists' => [
                [
                    'name' => 'Enabled quantity',
                    'sum_score' => 50,
                    'sequence' => 1,
                    'quantity_enabled' => true,
                ],
                [
                    'name' => 'Disabled quantity',
                    'sum_score' => 50,
                    'sequence' => 2,
                    'quantity_enabled' => false,
                ],
            ],
        ]],
    ];

    $javascript = <<<'JS'
const authUserId = 99;
%s
const payload = buildCopyPayload(%s, 123);
process.stdout.write(JSON.stringify(
    payload.categories[0].evaluation_lists.map(item => item.quantity_enabled)
));
JS;

    $process = new Process([
        'node',
        '-e',
        sprintf($javascript, $buildCopyPayload, json_encode($sourceData)),
    ]);
    $process->mustRun();

    expect(json_decode($process->getOutput(), true))->toBe([true, false]);
});
