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
    public $chartLabels; // Add this
    public $chartData;   // Add this

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
        
        // Process the data and labels
        $this->processChartData();
    }

    private function processChartData()
    {
        // If labels are provided, use them with data array
        if (!empty($this->labels)) {
            $this->chartLabels = $this->labels;
            $this->chartData = is_array($this->data) ? $this->data : [];
        } else {
            // If no labels, assume data is associative array
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
            'rgba(251, 36, 36, 0.8)',   // Red
            'rgba(59, 130, 246, 0.8)',  // Blue
            'rgba(251, 191, 36, 0.8)',  // Yellow
            'rgba(16, 185, 129, 0.8)',  // Green
            'rgba(139, 92, 246, 0.8)',  // Purple
            'rgba(236, 72, 153, 0.8)',  // Pink
            'rgba(245, 101, 101, 0.8)', // Light Red
            'rgba(52, 211, 153, 0.8)',  // Teal
        ];
    }

    public function render()
    {
        return view('components.bar-chart');
    }
}