{{-- File: resources/views/components/scatter-chart-component.blade.php --}}

<div class="bg-white rounded-xl shadow-md p-6 animate-fadeIn">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        @if($showDownload)
        <div class="flex space-x-2">
            <button 
                id="download{{ $chartId }}Btn" 
                class="text-gray-400 hover:text-gray-600 transition-colors" 
                title="ดาวน์โหลดกราฟ"
                onclick="window.scatterCharts?.{{ $chartId }}?.downloadChart('{{ $chartId }}.png')"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" 
                          d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4">
                    </path>
                </svg>
            </button>
        </div>
        @endif
    </div>
    <div class="{{ $height }}">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    console.log('Loading scatter chart for ID: {{ $chartId }}');
    
    // Initialize global charts object if it doesn't exist
    if (!window.scatterCharts) {
        window.scatterCharts = {};
    }
    
    // Chart data from component props
    const scatterData = @json($scatterData ?? []);
    console.log("Raw Scatter Data for {{ $chartId }}:", scatterData);
    console.log("Data length:", scatterData.length);
    
    // Validate data
    if (!Array.isArray(scatterData) || scatterData.length === 0) {
        console.error('No valid scatter data found for chart {{ $chartId }}');
        document.getElementById('{{ $chartId }}').parentNode.innerHTML = 
            '<div class="flex items-center justify-center h-full text-gray-500">No data available</div>';
        return;
    }
    
    // Validate data structure
    const firstItem = scatterData[0];
    if (!firstItem || typeof firstItem.x === 'undefined' || typeof firstItem.y === 'undefined') {
        console.error('Invalid data structure for chart {{ $chartId }}. Expected: {x, y, quantity?, quality?}');
        console.log('First item:', firstItem);
        return;
    }
    
    // Custom options from component props
    const customOptions = @json($customOptions);
    
    // Default options
    const defaultOptions = {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: {
                display: true,
                position: 'top'
            },
            tooltip: {
                callbacks: {
                    label: function(context) {
                        const datasetLabel = context.dataset.label || '';
                        return `${datasetLabel} - Report ${context.parsed.x}: ${context.parsed.y.toFixed(2)}`;
                    }
                }
            }
        },
        scales: {
            x: {
                type: 'linear',
                title: {
                    display: true,
                    text: 'ชุดรายงานการประเมิน'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                ticks: {
                    stepSize: 1,
                    callback: function(value) {
                        return Number.isInteger(value) ? value : '';
                    }
                }
            },
            y: {
                title: {
                    display: true,
                    text: 'คะแนน'
                },
                grid: {
                    color: 'rgba(0, 0, 0, 0.05)'
                },
                beginAtZero: true
            }
        },
        animation: {
            duration: 1500,
            easing: 'easeOutQuart'
        }
    };
    
    // Merge options
    const finalOptions = { ...defaultOptions, ...customOptions };
    
    // Create datasets
    const datasets = [];
    
    // Main score dataset
    datasets.push({
        label: 'คะแนนรวม',
        data: scatterData.map(d => ({ x: d.x, y: d.y })),
        backgroundColor: 'rgba(79, 70, 229, 0.7)',
        borderColor: 'rgba(79, 70, 229, 1)',
        pointRadius: 6,
        pointHoverRadius: 8,
        pointBackgroundColor: function(context) {
            const value = context.dataset.data[context.dataIndex].y;
            return value >= 60 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(239, 68, 68, 0.8)';
        }
    });
    
    // Quantity dataset (if available)
    const hasQuantity = scatterData.some(d => typeof d.quantity !== 'undefined');
    if (hasQuantity) {
        datasets.push({
            label: 'คะแนนปริมาณ',
            data: scatterData.map(d => ({ x: d.x, y: d.quantity || 0 })),
            backgroundColor: 'rgba(255, 99, 132, 0.6)',
            borderColor: 'rgba(255, 99, 132, 1)',
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: function(context) {
                const value = context.dataset.data[context.dataIndex].y;
                return value >= 60 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(255, 99, 132, 0.6)';
            }
        });
    }
    
    // Quality dataset (if available)
    const hasQuality = scatterData.some(d => typeof d.quality !== 'undefined');
    if (hasQuality) {
        datasets.push({
            label: 'คะแนนคุณภาพ',
            data: scatterData.map(d => ({ x: d.x, y: d.quality || 0 })),
            backgroundColor: 'rgba(75, 192, 192, 0.6)',
            borderColor: 'rgba(75, 192, 192, 1)',
            pointRadius: 6,
            pointHoverRadius: 8,
            pointBackgroundColor: function(context) {
                const value = context.dataset.data[context.dataIndex].y;
                return value >= 60 ? 'rgba(16, 185, 129, 0.8)' : 'rgba(75, 192, 192, 0.6)';
            }
        });
    }
    
    console.log('Datasets for {{ $chartId }}:', datasets);
    
    // Create chart
    const ctx = document.getElementById('{{ $chartId }}').getContext('2d');
    if (!ctx) {
        console.error('Cannot get canvas context for {{ $chartId }}');
        return;
    }
    
    try {
        const chart = new Chart(ctx, {
            type: 'scatter',
            data: { datasets },
            options: finalOptions
        });
        
        // Store chart reference globally for download functionality
        window.scatterCharts['{{ $chartId }}'] = chart;
        
        // Add download method to chart object
        chart.downloadChart = function(filename = 'chart.png') {
            const url = this.toBase64Image();
            const link = document.createElement('a');
            link.download = filename;
            link.href = url;
            link.click();
        };
        
        console.log('Chart {{ $chartId }} created successfully');
        
    } catch (error) {
        console.error('Error creating chart {{ $chartId }}:', error);
    }
});
</script>
@endpush