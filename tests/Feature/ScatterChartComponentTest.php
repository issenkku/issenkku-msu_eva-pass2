<?php

test('scatter chart component renders a data-hook download trigger', function () {
    $html = view('components.scatter-chart-component', [
        'chartId' => 'reportScatter',
        'title' => 'Scatter',
        'height' => 'h-64',
        'showDownload' => true,
        'scatterData' => [
            ['x' => 1, 'y' => 2],
        ],
        'customOptions' => [],
    ])->render();

    expect($html)
        ->toContain('data-scatter-download="reportScatter"')
        ->not->toContain('onclick="window.scatterCharts?.reportScatter?.downloadChart(\'reportScatter.png\')"');
});

test('scatter chart script listens for the data-hook download trigger', function () {
    $html = view('components.scatter-chart-component-script', [
        'chartId' => 'reportScatter',
        'scatterData' => [
            ['x' => 1, 'y' => 2],
        ],
        'customOptions' => [],
    ])->render();

    expect($html)
        ->toContain("closest('[data-scatter-download]')")
        ->toContain('chart.downloadChart(`${chartId}.png`)')
        ->not->toContain('onclick="window.scatterCharts?.reportScatter?.downloadChart(\'reportScatter.png\')"');
});
