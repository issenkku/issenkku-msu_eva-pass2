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

@media (max-width: 1439px) {
    .max-w-8xl {
        max-width: 100%;
    }

    .lg\:mx-10 {
        margin-left: 1rem !important;
        margin-right: 1rem !important;
    }

    .lg\:px-13 {
        padding-left: 1rem !important;
        padding-right: 1rem !important;
    }
}

@media (max-width: 1199px) {
    .grid.xl\:grid-cols-3 {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .dashboard-overview-card {
        grid-column: auto !important;
    }

    .dashboard-overview-chart-grid {
        grid-template-columns: minmax(0, 1fr) !important;
    }

    .overview-chart-wrap {
        width: min(100%, 240px);
    }

    .overview-chart-value {
        font-size: 1.8rem;
    }

    .dashboard-filter-card {
        margin-bottom: 1.25rem;
    }

    .dashboard-filter-card > button {
        padding: 1rem !important;
    }

    .dashboard-filter-card > button > .flex:first-child {
        min-width: 0;
    }

    .dashboard-filter-card h2 {
        font-size: 1rem;
    }

    .dashboard-filter-grid {
        grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
        gap: 0.875rem !important;
    }

    .dashboard-filter-grid > .md\:col-span-2 {
        grid-column: span 2 / span 2 !important;
    }

    .dashboard-filter-grid input,
    .dashboard-filter-grid select {
        min-height: 42px;
    }

    .dashboard-filter-grid label {
        display: flex !important;
        min-height: 2.45rem;
        align-items: flex-end;
        line-height: 1.25;
    }

    .dashboard-filter-actions {
        display: grid !important;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 0.75rem !important;
        justify-content: stretch !important;
    }

    .dashboard-filter-actions button {
        width: 100%;
        min-height: 42px;
    }
}

@media (max-width: 768px) {
    .space-y-6 > * + * {
        margin-top: 1rem;
    }

    .max-w-8xl {
        max-width: 100%;
        padding-left: 0;
        padding-right: 0;
    }

    .lg\:mx-10 {
        margin-left: 0 !important;
        margin-right: 0 !important;
    }

    .lg\:px-13 {
        padding-left: 0 !important;
        padding-right: 0 !important;
    }

    .overview-chart-wrap {
        width: min(100%, 220px);
    }

    .overview-chart-center {
        padding: 0 1.25rem;
    }

    .overview-chart-value {
        font-size: 1.55rem;
    }

    .overview-chart-label {
        font-size: 0.82rem;
    }

    .overview-chart-meta {
        font-size: 0.7rem;
    }

    .dashboard-filter-card {
        border-radius: 0.875rem;
    }

    .dashboard-filter-card > button {
        align-items: flex-start;
        padding: 0.875rem !important;
    }

    .dashboard-filter-card > button > .flex:first-child {
        align-items: flex-start;
    }

    .dashboard-filter-card > button p {
        line-height: 1.35;
    }

    .dashboard-filter-card #managerFilterPanel,
    .dashboard-filter-card #directorFilterPanel,
    .dashboard-filter-card #evaluatorFilterPanel {
        padding-left: 0.875rem !important;
        padding-right: 0.875rem !important;
    }

    .dashboard-filter-grid,
    .dashboard-filter-grid > .md\:col-span-2 {
        grid-template-columns: minmax(0, 1fr) !important;
        grid-column: auto !important;
    }

    .dashboard-filter-grid label {
        min-height: auto;
    }

    .dashboard-filter-actions {
        grid-template-columns: minmax(0, 1fr);
    }
}
</style>
