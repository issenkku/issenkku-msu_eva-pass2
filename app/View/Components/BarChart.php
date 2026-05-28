<?php

namespace App\View\Components;

use Illuminate\View\Component;

class BarChart extends Component
{
    public $chartId;

    public $title;

    public $data;

    public $labels;

    public $colors;

    public $height;

    public $downloadable;

    public $chartOptions;

    public $chartLabels;

    public $chartData;

    public function __construct(
        $chartId,
        $title,
        $data,
        $labels = [],
        $colors = [],
        $height = '320px',
        $downloadable = true,
        $chartOptions = []
    ) {
        $this->chartId = $chartId;
        $this->title = $title;
        $this->data = $data;
        $this->labels = $labels;
        $this->colors = $colors ?: $this->getDefaultColors();
        $this->height = $height;
        $this->downloadable = $downloadable;
        $this->chartOptions = $chartOptions;
        $this->processChartData();
    }

    private function processChartData()
    {
        if (! empty($this->labels)) {
            $this->chartLabels = $this->labels;
            $this->chartData = is_array($this->data) ? $this->data : [];
        } else {
            if (is_array($this->data)) {
                $this->chartLabels = array_keys($this->data);
                $this->chartData = array_values($this->data);
            } else {
                $this->chartLabels = [];
                $this->chartData = [];
            }
        }
    }

    private function getDefaultColors()
    {
        return [
            'rgba(251, 36, 36, 0.8)',
            'rgba(59, 130, 246, 0.8)',
            'rgba(251, 191, 36, 0.8)',
            'rgba(16, 185, 129, 0.8)',
            'rgba(139, 92, 246, 0.8)',
            'rgba(236, 72, 153, 0.8)',
            'rgba(245, 101, 101, 0.8)',
            'rgba(52, 211, 153, 0.8)',
        ];
    }

    public function render()
    {
        return view('components.bar-chart');
    }
}
