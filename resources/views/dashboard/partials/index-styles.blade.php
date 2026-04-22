<style>
    /* Custom animations */
    @keyframes fadeIn {
        from {
            opacity: 0;
            transform: translateY(10px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fadeIn {
        animation: fadeIn 0.5s ease-out forwards;
    }

    .stat-card {
        transition: all 0.3s ease;
        opacity: 0;
        animation: fadeIn 0.5s ease-out forwards;
    }

    .stat-card:nth-child(1) {
        animation-delay: 0.1s;
    }

    .stat-card:nth-child(2) {
        animation-delay: 0.2s;
    }

    .stat-card:nth-child(3) {
        animation-delay: 0.3s;
    }

    .hover-scale:hover {
        transform: translateY(-5px);
    }

    .loading-skeleton {
        background: linear-gradient(90deg, #f0f0f0 25%, #f8f8f8 50%, #f0f0f0 75%);
        background-size: 200% 100%;
        animation: loading 1.5s infinite;
    }

    .overview-main-chart {
        position: relative;
        border: 1px solid #e5e7eb;
        border-radius: 1rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fafc 100%);
        padding: 1.5rem;
    }

    .overview-chart-wrap {
        position: relative;
        width: min(100%, 240px);
        height: 240px;
        margin: 0 auto;
    }

    .overview-chart-center {
        position: absolute;
        inset: 0;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
        pointer-events: none;
    }

    .overview-chart-value {
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1;
        color: #0f172a;
    }

    .overview-chart-label {
        margin-top: 0.4rem;
        max-width: 7.5rem;
        font-size: 0.68rem;
        line-height: 0.95rem;
        color: #6b7280;
    }

    .overview-chart-sub-label {
        margin-top: 0.45rem;
        max-width: 10rem;
        font-size: 0.64rem;
        line-height: 0.9rem;
        color: #94a3b8;
    }

    .overview-progress-card {
        border: 1px solid #e5e7eb;
        border-radius: 0.95rem;
        background: #ffffff;
        padding: 1rem;
    }

    .overview-panel-card {
        height: 100%;
        border: 1px solid #dbe4ee;
        border-radius: 1.25rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 1.25rem;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.05);
    }

    .overview-chart-panel {
        display: flex;
        flex-direction: column;
        gap: 1.25rem;
    }

    .overview-mini-card {
        border: 1px solid #dbe4ee;
        border-radius: 1.1rem;
        background: #ffffff;
        padding: 0.8rem 0.95rem;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.05);
    }

    .overview-status-card {
        width: 100%;
        border: 1px solid #dbe4ee;
        border-radius: 1.15rem;
        background: linear-gradient(180deg, #ffffff 0%, #f8fbff 100%);
        padding: 0.85rem 1rem;
        text-align: left;
        box-shadow: 0 10px 28px rgba(15, 23, 42, 0.05);
        transition: transform 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }

    .overview-status-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .overview-status-card.is-active {
        border-color: #93c5fd;
        box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.18), 0 14px 30px rgba(15, 23, 42, 0.08);
    }

    .overview-progress-row + .overview-progress-row {
        margin-top: 1rem;
    }

    @keyframes loading {
        0% {
            background-position: 200% 0;
        }

        100% {
            background-position: -200% 0;
        }
    }
</style>
