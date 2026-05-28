<script>
document.addEventListener('DOMContentLoaded', function() {
    const {{ $chartId }}_labels = @json($chartLabels);
    const {{ $chartId }}_data = @json($chartData);
    const {{ $chartId }}_colors = @json($colors);

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
                borderColor: {{ $chartId }}_colors.slice(0, {{ $chartId }}_data.length)
                    .map(color => color.replace('0.8', '1')),
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
