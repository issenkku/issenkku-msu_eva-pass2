{{-- สไตล์กลางของหน้า dashboard overview และกราฟสรุป --}}
<style>
@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }

    .max-w-4xl {
        max-width: 100%;
        padding: 0 1rem;
    }
}

.overview-chart-wrap {
    position: relative;
    width: min(100%, 280px);
    aspect-ratio: 1 / 1;
}

.overview-chart-wrap canvas {
    width: 100% !important;
    height: 100% !important;
}

.overview-chart-center {
    position: absolute;
    inset: 0;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    pointer-events: none;
    text-align: center;
    padding: 0 2rem;
}

.overview-chart-value {
    color: #0f172a;
    font-size: 2.1rem;
    font-weight: 800;
    line-height: 1;
}

.overview-chart-label {
    margin-top: 0.5rem;
    color: #64748b;
    font-size: 0.95rem;
    font-weight: 700;
}

.overview-chart-meta {
    margin-top: 0.35rem;
    color: #94a3b8;
    font-size: 0.75rem;
    line-height: 1.4;
}
</style>
