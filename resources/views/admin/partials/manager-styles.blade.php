<style>
    .shift-card {
        transition: box-shadow 0.2s, transform 0.2s;
    }
    .shift-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 20px rgba(0,0,0,0.12);
    }

    /* Status pulse rings */
    @keyframes ring-pulse {
        0%, 100% { opacity: 1; }
        50%       { opacity: 0.35; }
    }
    .ring-pulse { animation: ring-pulse 2s ease-in-out infinite; }

    /* Coverage bar */
    .cov-bar { height: 6px; border-radius: 9999px; background: #e5e7eb; overflow: hidden; }
    .dark .cov-bar { background: #374151; }
    .cov-fill { height: 100%; border-radius: 9999px; transition: width 0.5s; }

    /* Volunteer avatar pill */
    .vol-pill {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 2px 8px;
        border-radius: 9999px;
        font-size: 0.72rem;
        font-weight: 600;
        white-space: nowrap;
    }

    /* Column header badges */
    .section-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 4px 12px;
        border-radius: 9999px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .auto-refresh-bar {
        height: 3px;
        background: rgba(255,255,255,0.15);
        border-radius: 9999px;
        overflow: hidden;
    }
    .auto-refresh-fill {
        height: 100%;
        background: rgba(255,255,255,0.6);
        border-radius: 9999px;
        width: 100%;
        transition: none;
    }
    .auto-refresh-fill.animating {
        width: 0%;
        transition: width 60s linear;
    }
</style>
