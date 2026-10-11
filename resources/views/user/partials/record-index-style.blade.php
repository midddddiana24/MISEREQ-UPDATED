<style>
    .record-index .panel {
        padding: 22px;
        border: 1px solid var(--client-line, #d7dce2);
        border-radius: 6px;
        background: #fff;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .1);
    }

    .record-index .section-header {
        margin: -22px -22px 22px;
        padding: 16px 22px;
        border-radius: 6px 6px 0 0;
        background: #f3f4f6;
    }

    .record-index .header-content {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        flex-wrap: wrap;
    }

    .record-index .header-content h1 { margin: 0; font-size: 22px; }
    .record-index .header-content small { display: block; margin-top: 4px; color: #6b7280; font-size: 13px; }

    .record-index .stats-grid {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 28px;
    }

    .record-index .stat-card {
        min-width: 0;
        padding: 16px;
        border: 1px solid var(--client-line, #d7dce2);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 8px 24px rgba(15, 23, 42, .06);
    }

    .record-index .stat-label {
        display: block;
        margin-bottom: 8px;
        color: #6b7280;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .06em;
        text-transform: uppercase;
    }

    .record-index .stat-value { display: block; color: #111827; font-size: 30px; font-weight: 700; }
    .record-index .stat-hint { margin-top: 6px; color: #6b7280; font-size: 12px; line-height: 1.45; }

    .record-index .filters {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-bottom: 14px;
    }

    .record-index .filter-btn {
        min-height: 40px;
        padding: 8px 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid var(--client-line, #d7dce2);
        border-radius: 6px;
        background: #fff;
        color: #2d123d;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }

    .record-index .filter-btn:hover { background: #eaf2f7; text-decoration: none; }
    .record-index .filter-btn.active { border-color: #2f7fbd; background: #2f7fbd; color: #fff; }

    .record-index .search-container {
        display: flex;
        align-items: stretch;
        gap: 8px;
        margin-bottom: 20px;
    }

    .record-index .search-input {
        width: min(100%, 420px);
        min-width: 0;
        min-height: 44px;
        padding: 9px 12px;
        border: 1px solid var(--client-line, #d7dce2);
        border-radius: 6px;
        font-size: 16px;
    }

    .record-index .search-input:focus {
        outline: 0;
        border-color: #1f6fa8;
        box-shadow: 0 0 0 3px rgba(31, 111, 168, .12);
    }

    .record-index .search-container .button { min-height: 44px; border-radius: 6px; }
    .record-index .record-list-note { margin: 0 0 7px; color: #586d78; font-size: 11px; }

    .record-index .client-record-scroll {
        max-height: min(62vh, 620px);
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        border: 1px solid var(--client-line, #d7dce2);
        border-radius: 8px;
        background: #fff;
    }

    .record-index .client-record-scroll:focus-visible { outline: 3px solid rgba(35, 109, 158, .28); outline-offset: 2px; }
    .record-index .client-record-table { margin: 0; }
    .record-index .client-record-table thead { position: sticky; z-index: 2; top: 0; }
    .record-index .client-record-table td:first-child { font-weight: 700; color: #1f2937; }

    .record-index .status-badge {
        display: inline-flex;
        padding: 6px 10px;
        border-radius: 5px;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        text-transform: uppercase;
    }

    .record-index .status-open, .record-index .status-pending { background: #fef3c7; color: #92400e; }
    .record-index .status-resolved, .record-index .status-completed { background: #d1fae5; color: #065f46; }
    .record-index .status-closed { background: #dbeafe; color: #0c4a6e; }
    .record-index .status-needs_action { background: #fee2e2; color: #991b1b; }

    .record-index .record-actions { min-width: 180px; display: flex; flex-wrap: wrap; gap: 6px; }
    .record-index .record-action {
        min-height: 36px;
        padding: 7px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #c5d3dc;
        border-radius: 6px;
        background: #fff;
        color: #174f75;
        font-size: 12px;
        font-weight: 700;
        line-height: 1.2;
        text-decoration: none;
    }

    .record-index .record-action:hover { border-color: #8fb4ca; background: #eef6fb; text-decoration: none; }
    .record-index .record-action.primary { border-color: #236d9e; background: #236d9e; color: #fff; }
    .record-index .record-action.primary:hover { background: #174f75; }

    @media (max-width: 1024px) {
        .record-index .client-record-scroll { max-height: min(58vh, 560px); padding: 9px; }
        .record-index .client-record-table, .record-index .client-record-table tbody { display: block; width: 100%; }
        .record-index .client-record-table thead { display: none; }
        .record-index .client-record-table tbody { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 10px; }
        .record-index .client-record-table tbody tr {
            min-width: 0;
            padding: 13px;
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            align-content: start;
            gap: 8px 12px;
            border: 1px solid var(--client-line, #d7dce2);
            border-radius: 8px;
            background: #fff;
        }
        .record-index .client-record-table tbody td { min-width: 0; width: auto; padding: 0; display: block; border: 0; overflow-wrap: anywhere; }
        .record-index .client-record-table tbody td::before {
            content: attr(data-label);
            display: block;
            margin-bottom: 3px;
            color: #6b7280;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .04em;
            text-transform: uppercase;
        }
        .record-index .client-record-table tbody td:first-child,
        .record-index .client-record-table tbody td:last-child { grid-column: 1 / -1; }
        .record-index .record-actions { min-width: 0; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .record-index .record-action { width: 100%; min-height: 42px; }
    }

    @media (max-width: 860px) {
        .record-index .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }

    @media (max-width: 640px) {
        .record-index .panel { padding: 14px; }
        .record-index .section-header { margin: -14px -14px 14px; padding: 14px; }
        .record-index .header-content { align-items: stretch; flex-direction: column; }
        .record-index .header-content .button { width: 100%; min-height: 44px; display: inline-flex; align-items: center; justify-content: center; }
        .record-index .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 8px; margin-bottom: 16px; }
        .record-index .stat-card { min-width: 0; min-height: 88px; padding: 10px 12px; display: flex; flex-direction: column; justify-content: space-between; border-radius: 10px; }
        .record-index .stat-value { font-size: 22px; line-height: 1; }
        .record-index .stat-label { min-height: 0; margin-bottom: 8px; line-height: 1.2; overflow-wrap: anywhere; }
        .record-index .stat-hint { display: none; }
        .record-index .filters { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .record-index .filter-btn { width: 100%; padding-inline: 8px; text-align: center; white-space: normal; }
        .record-index .search-container { flex-direction: row; }
        .record-index .search-input { min-width: 0; flex: 1 1 auto; width: auto; max-width: none; }
        .record-index .search-container .button { flex: 0 0 auto; width: auto; padding-inline: 12px; }
        .record-index .client-record-scroll { max-height: none; overflow: visible; padding: 0; border: 0; background: transparent; }
        .record-index .client-record-table tbody { grid-template-columns: 1fr; }
        .record-index .client-record-table tbody tr { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 6px 10px; padding: 11px; }
        .record-index .client-record-table tbody td::before { font-size: 11px; }
        .record-index .incident-record-table tbody td:nth-child(3),
        .record-index .incident-record-table tbody td:nth-child(4),
        .record-index .maintenance-record-table tbody td:nth-child(2),
        .record-index .maintenance-record-table tbody td:nth-child(4) { display: none; }
    }

</style>
