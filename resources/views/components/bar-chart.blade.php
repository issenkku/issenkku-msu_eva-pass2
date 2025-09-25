<div class="bg-white rounded-xl shadow-md p-6 animate-fadeIn" style="animation-delay: 0.4s;">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-lg font-semibold text-gray-900">{{ $title }}</h3>
        @if($downloadable)
        <div class="flex space-x-2">
            <button id="{{ $chartId }}_downloadBtn" class="text-gray-400 hover:text-gray-600 transition-colors" title="ดาวน์โหลดกราฟ">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                </svg>
            </button>
        </div>
        @endif
    </div>
    <div class="h-80" style="height: {{ $height }}">
        <canvas id="{{ $chartId }}"></canvas>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Now we use the processed data from the component
    const {{ $chartId }}_labels = @json($chartLabels);
    const {{ $chartId }}_data = @json($chartData);
    const {{ $chartId }}_colors = @json($colors);
    
    // Debugging - you can remove these later
    console.log('Chart ID: {{ $chartId }}');
    console.log('Labels:', {{ $chartId }}_labels);
    console.log('Data:', {{ $chartId }}_data);
    
    const {{ $chartId }}_ctx = document.getElementById('{{ $chartId }}');
    
    if (!{{ $chartId }}_ctx) {
        console.error('Canvas element not found: {{ $chartId }}');
        return;
    }

    if (!{{ $chartId }}_data || {{ $chartId }}_data.length === 0) {
        console.error('No data provided for chart: {{ $chartId }}');
        return;
    }

    const {{ $chartId }}_chart = new Chart({{ $chartId }}_ctx, {
        type: 'bar',
        data: {
            labels: {{ $chartId }}_labels,
            datasets: [{
                label: '{{ $title }}',
                data: {{ $chartId }}_data,
                backgroundColor: {{ $chartId }}_colors.slice(0, {{ $chartId }}_data.length),
                borderColor: {{ $chartId }}_colors.slice(0, {{ $chartId }}_data.length).map(color => color.replace('0.8', '1')),
                borderWidth: 1,
                borderRadius: 8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `${context.label}: ${context.raw} รายงาน`;
                        }
                    }
                },
                ...@json($chartOptions['plugins'] ?? [])
            },
            scales: {
                y: {
                    beginAtZero: true,
                    title: {
                        display: true,
                        text: 'จำนวน'
                    },
                    ticks: {
                        precision: 0
                    },
                    grid: {
                        color: 'rgba(0, 0, 0, 0.05)'
                    }
                },
                x: {
                    grid: {
                        display: false
                    }
                },
                ...@json($chartOptions['scales'] ?? [])
            },
            animation: {
                duration: 2000,
                easing: 'easeOutQuart'
            },
            ...@json($chartOptions['other'] ?? [])
        }
    });

    @if($downloadable)
    document.getElementById('{{ $chartId }}_downloadBtn').addEventListener('click', function() {
        const link = document.createElement('a');
        link.download = '{{ $title }}_chart.png';
        link.href = {{ $chartId }}_chart.toBase64Image();
        link.click();
    });
    @endif
});
</script>
@endpush