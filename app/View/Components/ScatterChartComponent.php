<?php
// File: app/View/Components/ScatterChartComponent.php

namespace App\View\Components;

use Illuminate\View\Component;
use Closure;
use Illuminate\Contracts\View\View;

class ScatterChartComponent extends Component
{
    public $chartId;
    public $title;
    public $data;
    public $height;
    public $showDownload;
    public $customOptions;
    public $scatterData;
    
    public function __construct(
        $chartId = 'scatterChart',
        $title = 'การกระจายตัวของคะแนน',
        $scatterData = [],
        $height = 'h-80',
        $showDownload = true,
        $customOptions = []
    ) {
        $this->chartId = $chartId;
        $this->title = $title;
        $this->scatterData = $scatterData;
        $this->height = $height;
        $this->showDownload = $showDownload;
        $this->customOptions = $customOptions;
    }

    public function render()
    {
        return view('components.scatter-chart-component');
    }
}

?>