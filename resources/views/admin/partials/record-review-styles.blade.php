<style>
    .service-review-page .content-grid,
    .record-review-page .content-grid { width: 100%; max-width: 1540px; }
    .review-workspace { display: grid; grid-template-columns: minmax(0, 1fr) minmax(285px, 330px); align-items: start; gap: 14px; }
    .review-main, .review-sidebar { min-width: 0; display: grid; align-content: start; gap: 12px; }
    .review-sidebar { position: sticky; top: 82px; }
    .review-card { min-width: 0; padding: 17px 19px; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
    .review-hero { padding: 20px 22px; background: linear-gradient(110deg, #fff 55%, #f2f7fc); }
    .review-hero-top { display: flex; justify-content: space-between; align-items: flex-start; gap: 14px; }
    .review-kicker { display: block; color: var(--ink-500); font-size: 11px; font-weight: 750; letter-spacing: .055em; text-transform: uppercase; overflow-wrap: anywhere; }
    .review-hero h2 { margin: 6px 0 3px; color: var(--ink-900); font-size: clamp(19px, 2vw, 25px); line-height: 1.25; overflow-wrap: anywhere; }
    .review-hero p { margin: 0; color: var(--ink-600); font-size: 13px; line-height: 1.5; }
    .review-status { flex: none; display: grid; justify-items: end; gap: 5px; }
    .review-status > small { color: var(--ink-500); font-size: 10px; font-weight: 750; letter-spacing: .05em; text-transform: uppercase; }
    .review-status .status-badge { font-size: 12px; padding: 6px 10px; }
    .review-status p { color: var(--ink-500); font-size: 11px; text-align: right; }
    .review-card-head { display: flex; justify-content: space-between; align-items: flex-start; gap: 12px; margin: -17px -19px 15px; padding: 12px 19px; border-bottom: 1px solid var(--line); border-radius: 10px 10px 0 0; background: #f5f7f9; }
    .review-card-head h3, .review-card-head h2 { margin: 0; color: var(--ink-900); font-size: 14px; line-height: 1.4; }
    .review-card-head p { margin: 2px 0 0; color: var(--ink-500); font-size: 11px; line-height: 1.4; }
    .review-facts { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 13px 18px; margin: 0; }
    .review-facts > div { min-width: 0; padding-bottom: 10px; border-bottom: 1px solid #e9eef2; }
    .review-facts dt { margin-bottom: 3px; color: var(--ink-500); font-size: 11px; font-weight: 700; }
    .review-facts dd { min-width: 0; margin: 0; color: var(--ink-900); font-size: 13px; font-weight: 650; line-height: 1.45; overflow-wrap: anywhere; }
    .review-facts a { color: var(--brand-800); }
    .review-copy { margin: 0; color: var(--ink-800); font-size: 13px; line-height: 1.6; white-space: pre-wrap; overflow-wrap: anywhere; }
    .review-copy + .review-copy { margin-top: 12px; }
    .review-empty { margin: 0; color: var(--ink-500); font-size: 12px; line-height: 1.5; }
    .review-tags { display: flex; flex-wrap: wrap; gap: 7px; }
    .review-tag { padding: 6px 9px; border: 1px solid #d8e5f3; border-radius: 7px; color: var(--brand-900); background: #f3f8fe; font-size: 12px; font-weight: 650; line-height: 1.35; }
    .review-links { display: flex; flex-wrap: wrap; align-items: center; gap: 9px; }
    .review-links .btn-primary, .review-links .btn-ghost { min-height: 38px; }
    .review-sidebar .review-card { padding: 14px; }
    .review-sidebar .review-card-head { margin: -14px -14px 12px; padding: 11px 14px; }
    .review-sidebar .review-facts { grid-template-columns: 1fr; gap: 8px; }
    .review-sidebar .review-facts > div { display: flex; justify-content: space-between; gap: 8px; padding-bottom: 7px; }
    .review-sidebar .review-facts dt, .review-sidebar .review-facts dd { font-size: 12px; }
    .review-sidebar .review-facts dd { text-align: right; }
    .review-sidebar .btn-primary, .review-sidebar .btn-danger { width: 100%; }
    .review-sidebar-actions { display: grid; gap: 8px; }
    .review-sidebar-actions form { margin: 0; }
    .review-sidebar .field { gap: 4px; }
    .review-sidebar .field label { font-size: 12px; }
    .review-sidebar .field input, .review-sidebar .field select, .review-sidebar .field textarea { width: 100%; min-height: 40px; padding: 8px 9px; font-size: 13px; }
    .review-sidebar .field textarea { min-height: 84px; }
    .review-sidebar form.pdf-control-form { display: grid; gap: 10px; }
    .review-sidebar .form-actions { margin: 0; }
    .review-sidebar .form-actions .btn-primary { width: 100%; }
    .pdf-status-note { margin: 9px 0 0; color: var(--ink-500); font-size: 12px; line-height: 1.5; }
    .pdf-workflow-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 7px; }
    .pdf-workflow-actions a, .pdf-related-link { display: grid; align-items: center; min-height: 38px; padding: 8px; border: 1px solid var(--line); border-radius: 7px; color: var(--ink-700); background: #f8fafc; font-size: 12px; font-weight: 700; text-align: center; text-decoration: none; }
    .pdf-related-list { display: grid; gap: 7px; }
    .pdf-related-link { grid-template-columns: minmax(0, 1fr) auto; gap: 8px; text-align: left; }
    .pdf-related-link span { overflow-wrap: anywhere; }
    .pdf-related-link span:last-child { color: var(--ink-500); font-size: 11px; }
    .pdf-workflow-actions a:hover, .pdf-related-link:hover { border-color: var(--brand-700); color: var(--brand-900); }
    @media (max-width: 1000px) { .review-workspace { grid-template-columns: 1fr; } .review-sidebar { position: static; grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 680px) { .review-sidebar, .review-facts { grid-template-columns: 1fr; } .review-hero-top { flex-direction: column; } .review-status { justify-items: start; } .review-card { padding: 15px; } .review-card-head { margin: -15px -15px 13px; padding: 11px 15px; } }
</style>
