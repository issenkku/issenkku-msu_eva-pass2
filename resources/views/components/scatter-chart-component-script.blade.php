{{-- ไฟล์มุมมอง: resources/views/components/scatter-chart-component-script.blade.php --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    if (!window.scatterCharts) {
        window.scatterCharts = {};
    }

    const scatterData = @json($scatterData ?? []);

    if (!Array.isArray(scatterData) || scatterData.length === 0) {
        console.error('No valid scatter data found for chart {{ $chartId }}');
        document.getElementById('{{ $chartId }}').parentNode.innerHTML =
            '<div class="flex h-full items-center justify-center text-gray-500">ไม่มีข้อมูลสำหรับแสดงผล</div>';
        return;
    }

    const firstItem = scatterData[0];
    if (!firstItem || typeof firstItem.x === 'undefined' || typeof firstItem.y === 'undefined') {
        console.error('Invalid data structure for chart {{ $chartId }}. Expected: {x, y, quantity?, quality?}');
        return;
    }

    const customOptions = @json($customOptions);
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
                        return `${datasetLabel} - ชุดรายงาน ${context.parsed.x}: ${context.parsed.y.toFixed(2)}`;
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

    const finalOptions = { ...defaultOptions, ...customOptions };
    const datasets = [{
        label: 'คะแนนรวม',
        data: scatterData.map(d => ({ x: d.x, y: d.y })),
        backgroundColor: 'rgba(229, 70, 70, 0.7)',
        borderColor: 'rgba(229, 70, 70, 1)',
        pointRadius: 6,
        pointHoverRadius: 8,
        pointBackgroundColor: function(context) {
            const value = context.dataset.data[context.dataIndex].y;
            return value >= 60 ? 'rgba(239, 68, 68, 0.8)' : 'rgba(239, 68, 68, 0.8)';
        }
    }];

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
                return value >= 60 ? 'rgba(255, 99, 132, 0.6)' : 'rgba(255, 99, 132, 0.6)';
            }
        });
    }

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

    const canvas = document.getElementById('{{ $chartId }}');
    const ctx = canvas ? canvas.getContext('2d') : null;
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

        window.scatterCharts['{{ $chartId }}'] = chart;
        chart.downloadChart = function(filename = 'chart.png') {
            const link = document.createElement('a');
            link.download = filename;
            link.href = this.toBase64Image();
            link.click();
        };
    } catch (error) {
        console.error('Error creating chart {{ $chartId }}:', error);
    }
});
</script>
