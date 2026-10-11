<style>
    :root {
        --client-brand-900: #123f63;
        --client-brand-700: #1f628e;
        --client-brand-100: #edf5fa;
        --client-accent: #d5a619;
        --client-page: #f3f5f7;
        --client-surface: #ffffff;
        --client-surface-soft: #f7f9fa;
        --client-line: #a4adb3;
        --client-line-strong: #7f8b93;
        --client-ink-900: #1d303c;
        --client-ink-700: #3f535f;
        --client-ink-500: #62737d;
        --client-success: #18794e;
        --client-success-bg: #eaf7f0;
        --client-warning: #946515;
        --client-warning-bg: #fff5df;
        --client-danger: #b24242;
        --client-danger-bg: #fceeee;
        --client-shadow: 0 1px 3px rgba(25, 55, 74, .055);
        --client-radius-lg: 10px;
        --client-radius-md: 7px;
        --client-radius-sm: 5px;
    }

    *, *::before, *::after { box-sizing: border-box; }

    html {
        min-width: 320px;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    body {
        font-family: "Segoe UI", system-ui, -apple-system, BlinkMacSystemFont, Arial, sans-serif !important;
        font-size: 16px;
        line-height: 1.6;
        color: var(--client-ink-900) !important;
        background: var(--client-page) !important;
        overflow-x: hidden;
    }

    ::selection { color: #fff; background: var(--client-brand-700); }

    img,
    video,
    svg { max-width: 100%; }

    .page,
    .panel,
    .section,
    .section-header,
    .header-content,
    .grid,
    .stats,
    .stats-grid,
    .form-row,
    .info-grid,
    .service-card,
    .service-card-top,
    .service-box,
    .setting-item,
    .message-item,
    .table-wrap {
        min-width: 0;
        max-width: 100%;
    }

    .grid > *,
    .stats > *,
    .stats-grid > *,
    .form-row > *,
    .info-grid > *,
    .service-card-top > *,
    .header-content > *,
    .setting-item > *,
    .message-item > * { min-width: 0; }

    h1,
    h2,
    h3,
    h4,
    p,
    small,
    label,
    td,
    th,
    .info-value,
    .message-preview,
    .setting-item-info,
    .login-value,
    .description-box { overflow-wrap: anywhere; }

    .page {
        width: min(100%, 1220px) !important;
        padding-top: 22px !important;
        padding-bottom: 44px !important;
    }

    .client-page-heading {
        margin-bottom: 16px;
        padding: 18px 20px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 18px;
        border: 1.5px solid var(--client-line-strong);
        border-left: 3px solid var(--client-brand-700);
        border-radius: var(--client-radius-lg);
        background: var(--client-surface);
        box-shadow: var(--client-shadow);
    }

    .client-page-heading .eyebrow {
        display: block;
        margin-bottom: 5px;
        color: var(--client-brand-700);
        font-size: 12px;
        font-weight: 700;
        letter-spacing: .08em;
        text-transform: uppercase;
    }

    .client-page-heading h2 {
        margin: 0 0 5px;
        color: var(--client-ink-900);
        font-size: clamp(21px, 3vw, 27px);
        line-height: 1.2;
    }

    .client-page-heading p { margin: 0; color: var(--client-ink-500); font-size: 15px; line-height: 1.55; }

    .panel,
    .message-item,
    .stat-card,
    .service-box,
    .info-item,
    .setting-item,
    .stat,
    .attachment-section,
    .services-list,
    .description-box {
        border: 1.5px solid var(--client-line-strong) !important;
        border-radius: var(--client-radius-lg) !important;
        background: var(--client-surface) !important;
        box-shadow: var(--client-shadow) !important;
    }

    .panel { padding: 20px !important; }

    .section-header {
        margin: 0 0 20px !important;
        padding: 16px 18px !important;
        border: 1.5px solid var(--client-line) !important;
        border-radius: var(--client-radius-md) !important;
        background: var(--client-surface-soft) !important;
    }

    .section-header h1,
    .section h3,
    .panel h2 {
        color: var(--client-ink-900) !important;
        letter-spacing: -.015em;
    }

    .section-header h1 {
        margin: 0 0 5px !important;
        font-size: clamp(21px, 3vw, 27px) !important;
        line-height: 1.25;
    }

    .section-header small {
        display: block;
        margin: 0 !important;
        font-size: 12px !important;
        line-height: 1.5;
    }

    .section-header .button,
    .section-header .btn {
        min-width: 132px;
        padding-left: 14px !important;
        padding-right: 14px !important;
        display: inline-flex !important;
        align-items: center;
        justify-content: center;
        text-align: center;
        line-height: 1.3;
    }

    .section-header small,
    .table-note,
    .message-preview,
    .info-label,
    .stat-label,
    .welcome,
    .stat-hint {
        color: var(--client-ink-500) !important;
    }

    .button,
    button.button,
    .btn,
    .action-link,
    .filter-btn,
    .attachment-link {
        min-height: 42px;
        border-radius: var(--client-radius-md) !important;
        transition: background .15s ease, border-color .15s ease, color .15s ease;
    }

    .button,
    button.button,
    .btn-primary,
    .attachment-link {
        color: #fff !important;
        border-color: var(--client-brand-700) !important;
        background: var(--client-brand-700) !important;
        box-shadow: none !important;
    }

    .button:hover,
    button.button:hover,
    .btn-primary:hover,
    .attachment-link:hover {
        border-color: var(--client-brand-900) !important;
        background: var(--client-brand-900) !important;
        transform: none !important;
    }

    .button.secondary,
    .btn-secondary,
    .filter-btn,
    .action-link {
        color: var(--client-brand-900) !important;
        border: 1px solid var(--client-line-strong) !important;
        background: #fff !important;
        box-shadow: none !important;
    }

    .button.secondary:hover,
    .btn-secondary:hover,
    .filter-btn:hover,
    .action-link:hover {
        color: var(--client-brand-900) !important;
        border-color: #9ebfd5 !important;
        background: var(--client-brand-100) !important;
        transform: none !important;
    }

    .button:focus-visible,
    button.button:focus-visible,
    .btn:focus-visible,
    .filter-btn:focus-visible,
    .action-link:focus-visible,
    .attachment-link:focus-visible,
    .nav-toggle:focus-visible,
    input:focus-visible,
    select:focus-visible,
    textarea:focus-visible,
    a:focus-visible {
        outline: 3px solid rgba(35, 109, 158, .28);
        outline-offset: 2px;
    }

    .stats,
    .stats-grid,
    .grid,
    .info-grid,
    .form-row { gap: 13px !important; }

    .stat,
    .stat-card {
        position: relative;
        overflow: hidden;
        padding: 16px 17px !important;
        border-left: 3px solid var(--client-brand-700) !important;
    }

    .stat strong,
    .stat-card .value,
    .stat-value {
        position: relative;
        z-index: 1;
        color: var(--client-ink-900) !important;
        font-size: 24px !important;
        letter-spacing: -.025em;
    }

    .stat > span,
    .stat-card .stat-label {
        position: relative;
        z-index: 1;
        display: block;
        margin-top: 3px;
        color: var(--client-ink-500) !important;
        font-size: 12px;
        line-height: 1.4;
    }

    .service-card {
        position: relative;
        min-height: 178px !important;
        transition: border-color .15s ease, background .15s ease;
    }

    .service-card:hover {
        border-color: #abc2d1 !important;
        background: #fcfdfe !important;
    }

    .service-card-top { display: flex; align-items: flex-start; gap: 13px; }
    .service-card-top > div { min-width: 0; }

    .service-card-icon {
        width: 42px;
        height: 42px;
        flex: 0 0 auto;
        display: grid;
        place-items: center;
        color: var(--client-brand-700);
        border-radius: var(--client-radius-md);
        background: var(--client-brand-100);
    }

    .service-card-icon svg { width: 21px; height: 21px; }
    .service-card h2 { margin: 1px 0 7px !important; font-size: 18px !important; }
    .service-card p { color: var(--client-ink-500) !important; font-size: 13px; line-height: 1.6 !important; }
    .service-card .button { margin-top: 18px; text-align: center; }

    .form-group input,
    .form-group textarea,
    .form-group select,
    .form-row > input,
    .service-box > input,
    .search-input,
    .search-box input,
    #chatInput {
        min-height: 44px;
        color: var(--client-ink-900) !important;
        border: 1px solid var(--client-line-strong) !important;
        border-radius: var(--client-radius-md) !important;
        background: #fff !important;
    }

    .form-group input:focus,
    .form-group textarea:focus,
    .form-group select:focus,
    .search-input:focus,
    .search-box input:focus,
    #chatInput:focus {
        border-color: var(--client-brand-700) !important;
        box-shadow: 0 0 0 3px rgba(35, 109, 158, .11) !important;
    }

    .form-group textarea { min-height: 115px; }

    .form-section { margin-bottom: 24px !important; }
    .form-section + .form-section {
        padding-top: 22px;
        border-top: 1px solid var(--client-line-strong);
    }

    .form-section h3 {
        margin-bottom: 14px !important;
        color: var(--client-ink-900) !important;
        font-size: 16px !important;
        line-height: 1.4;
    }

    .form-group { margin-bottom: 14px !important; }
    .form-group label { margin-bottom: 6px !important; color: #344753 !important; font-size: 15px !important; line-height: 1.4; }
    .form-group small { display: block; margin-top: 5px; color: var(--client-ink-500) !important; font-size: 14px !important; line-height: 1.5; }

    .service-box { padding: 17px !important; box-shadow: none !important; }
    .service-box h4 { margin-bottom: 12px !important; color: var(--client-brand-900) !important; font-size: 14px !important; line-height: 1.4; }

    .checkbox-item {
        border: 1px solid var(--client-line) !important;
        border-radius: var(--client-radius-md) !important;
        background: var(--client-surface-soft) !important;
        transition: border-color .15s ease, background .15s ease;
    }

    .checkbox-item:hover { border-color: #abc5d7 !important; background: var(--client-brand-100) !important; }
    .checkbox-item input { accent-color: var(--client-brand-700); }

    .checks {
        gap: 9px !important;
        padding: 13px 14px !important;
        border-color: var(--client-line) !important;
        background: var(--client-surface-soft) !important;
    }

    .checks label {
        min-width: 0;
        padding: 3px 0 !important;
        color: var(--client-ink-700) !important;
        font-size: 13px !important;
        line-height: 1.45;
    }

    .checks input[type="checkbox"] { margin-top: 1px !important; accent-color: var(--client-brand-700) !important; }

    .section { margin-bottom: 24px !important; }
    .section:last-child { margin-bottom: 0 !important; }
    .panel > .section + .section { padding-top: 22px; border-top: 1px solid var(--client-line-strong); }
    .section > h3 { margin-bottom: 14px !important; color: var(--client-ink-900) !important; font-size: 16px !important; line-height: 1.4; }

    .info-grid { gap: 11px !important; }
    .info-item { padding: 13px 14px !important; box-shadow: none !important; }
    .info-label { margin-bottom: 5px !important; font-size: 10px !important; font-weight: 700 !important; letter-spacing: .05em; line-height: 1.35; text-transform: uppercase; }
    .info-value { color: var(--client-ink-900) !important; font-size: 14px !important; line-height: 1.5; }

    .grid { gap: 13px !important; }
    .item {
        min-width: 0;
        padding: 13px 14px !important;
        color: var(--client-ink-700) !important;
        border: 1px solid var(--client-line) !important;
        border-left: 3px solid var(--client-brand-700) !important;
        border-radius: var(--client-radius-md) !important;
        background: var(--client-surface-soft) !important;
        font-size: 14px;
        line-height: 1.55;
        overflow-wrap: anywhere;
    }

    .item strong {
        margin-bottom: 5px !important;
        color: var(--client-ink-500) !important;
        font-size: 10px !important;
        font-weight: 700 !important;
        letter-spacing: .05em !important;
        line-height: 1.35;
    }

    .description-box,
    .services-list,
    .attachment-section {
        padding: 14px !important;
        box-shadow: none !important;
        font-size: 13px !important;
        line-height: 1.65 !important;
    }

    .services-list li { padding: 5px 0 !important; line-height: 1.5; }
    .action-buttons { margin-top: 22px !important; }

    .header-content { display: flex; align-items: center; justify-content: space-between; gap: 18px !important; }
    .header-content small { display: block; margin-top: 4px; line-height: 1.45; }

    .toolbar-pills {
        margin-bottom: 14px !important;
        display: flex;
        flex-wrap: wrap;
        gap: 6px !important;
    }

    .toolbar-pills .button { min-height: 36px; padding: 7px 11px !important; font-size: 12px !important; }

    .search-form,
    .search-box,
    .search-box form {
        min-width: 0;
        max-width: 100% !important;
    }

    .search-form,
    .search-box form {
        display: flex !important;
        align-items: center;
        flex-wrap: wrap;
        gap: 8px !important;
    }

    .search-form { margin-bottom: 18px !important; }
    .search-box { margin-bottom: 20px !important; padding: 0 !important; }
    .search-container { margin-bottom: 18px !important; }
    .search-form .search-input,
    .search-box input { min-width: min(100%, 220px); flex: 1 1 240px !important; }

    .message-list { display: grid; gap: 10px !important; }
    .message-item { padding: 15px 16px !important; box-shadow: none !important; }
    .message-header { align-items: flex-start !important; gap: 14px !important; }
    .message-header > div { min-width: 0; }
    .message-from { margin-bottom: 4px !important; color: var(--client-ink-900) !important; font-size: 14px !important; line-height: 1.4; }
    .message-subject,
    .message-time { color: var(--client-ink-500) !important; font-size: 14px !important; line-height: 1.5; }
    .message-preview { margin: 11px 0 !important; font-size: 13px !important; line-height: 1.6 !important; }
    .message-actions { margin-top: 10px !important; display: flex; flex-wrap: wrap; }
    .message-actions .action-link { min-height: 44px; padding: 9px 12px !important; display: inline-flex; align-items: center; font-size: 14px !important; text-decoration: none; }

    #editPanel,
    #twoFaPanel_profile { padding: 16px !important; }

    #enable2faForm_profile,
    form[action*="2fa/disable"] { align-items: center !important; flex-wrap: wrap !important; }

    #enable2faForm_profile input,
    form[action*="2fa/disable"] input { min-width: 0; flex: 1 1 190px; }

    #chatPanel { line-height: 1.45; }
    #chatTitle { font-size: 14px; }
    #chatForm { align-items: center; gap: 8px !important; }
    #chatForm button { min-height: 42px; }

    .table-wrap { border: 1.5px solid var(--client-line-strong); border-radius: var(--client-radius-md); overflow: auto; overscroll-behavior-inline: contain; }

    table { width: 100% !important; max-width: 100%; border-collapse: collapse !important; }
    table thead,
    table th { background: #f5f8fa !important; }
    table th { color: #53636e !important; font-size: 11px !important; letter-spacing: .045em; text-transform: uppercase; }
    table th,
    table td { padding: 11px 12px !important; border-color: var(--client-line) !important; }
    table td { color: var(--client-ink-700) !important; }
    table tbody tr:nth-child(even) { background: #fcfdfe; }
    table tbody tr:hover { background: #f5f9fc; }
    table td[data-label="Action"] { line-height: 2; }
    table td[data-label="Action"] a { font-size: 12px; font-weight: 700; }

    .type-badge,
    .category-badge {
        padding: 4px 8px !important;
        color: var(--client-brand-900) !important;
        border-radius: 999px !important;
        background: var(--client-brand-100) !important;
    }

    .status-badge { border-radius: 999px !important; }
    .status-badge.pending,
    .status-badge.awaiting_confirmation,
    .status-pending,
    .status-awaiting_confirmation { color: var(--client-warning) !important; border-color: transparent !important; background: var(--client-warning-bg) !important; }
    .status-badge.processing,
    .status-processing,
    .status-reopened,
    .status-closed,
    .status-open { color: var(--client-brand-900) !important; border-color: transparent !important; background: var(--client-brand-100) !important; }
    .status-badge.completed,
    .status-badge.closed,
    .status-completed,
    .status-closed { color: var(--client-success) !important; background: var(--client-success-bg) !important; }
    .status-badge.rejected,
    .status-badge.cancelled,
    .status-badge.escalated,
    .status-rejected,
    .status-cancelled,
    .status-escalated { color: var(--client-danger) !important; border-color: transparent !important; background: var(--client-danger-bg) !important; }

    .alert,
    .empty-state { border-radius: var(--client-radius-md) !important; }

    .filters { display: flex; flex-wrap: wrap; gap: 7px !important; }
    .filter-btn.active { color: #fff !important; border-color: var(--client-brand-700) !important; background: var(--client-brand-700) !important; }

    #chatPanel { border: 1px solid var(--client-line); box-shadow: 0 12px 30px rgba(25, 65, 91, .15) !important; }

    @media (prefers-reduced-motion: reduce) {
        *, *::before, *::after {
            scroll-behavior: auto !important;
            transition-duration: .01ms !important;
            animation-duration: .01ms !important;
            animation-iteration-count: 1 !important;
        }
    }

    @media (max-width: 860px) {
        .page { padding-top: 18px !important; padding-bottom: 38px !important; }
        .panel { padding: 19px !important; }
        .client-page-heading { align-items: flex-start; flex-direction: column; padding: 18px; }
        .client-page-heading .button { width: 100%; text-align: center; }
        .header-content,
        .top-bar {
            align-items: stretch !important;
            flex-direction: column !important;
            flex-wrap: wrap !important;
            gap: 12px !important;
        }
        .header-content .button,
        .top-bar .button,
        .top-bar .back-btn { width: 100%; white-space: normal !important; text-align: center; }
        .info-grid,
        .form-row.three-col,
        .form-row.four-col,
        .three-col,
        .four-col { grid-template-columns: 1fr !important; }
        .action-buttons,
        .message-actions,
        .setting-item-action { flex-wrap: wrap !important; gap: 9px !important; }
        .section,
        .form-section { margin-bottom: 21px !important; }
        .panel > .section + .section,
        .form-section + .form-section { padding-top: 19px; }
        .grid { gap: 10px !important; }
    }

    @media (max-width: 560px) {
        .page { padding: 15px 12px 30px !important; }
        .panel,
        .message-item,
        .stat-card,
        .setting-item,
        .service-box,
        .info-item,
        .description-box,
        .services-list,
        .attachment-section { border-radius: 8px !important; }
        .panel { padding: 16px !important; }
        .section-header { padding: 14px !important; margin: 0 0 17px !important; }
        .section-header .button,
        .section-header .btn { width: 100%; min-width: 0; }
        .button,
        button.button,
        .btn,
        .attachment-link { width: 100%; justify-content: center; text-align: center; }
        .service-card { min-height: 164px !important; }
        .stats-grid { grid-template-columns: 1fr !important; }
        .checkbox-grid { grid-template-columns: 1fr !important; }
        .setting-item,
        .message-item { align-items: stretch !important; flex-direction: column !important; gap: 12px !important; }
        .panel [style*="min-width"] { min-width: 0 !important; }
        [style*="white-space:nowrap"],
        [style*="white-space: nowrap"] { white-space: normal !important; }
        input,
        select,
        textarea,
        button { max-width: 100%; }
        .toolbar-pills .button { width: auto !important; flex: 1 1 calc(50% - 6px); }
        .search-form,
        .search-box form { align-items: stretch; flex-direction: column; }
        .search-form .button,
        .search-box button { width: 100% !important; }
        .message-header { flex-direction: column; gap: 6px !important; }
        .message-actions .action-link { width: 100%; justify-content: center; }
        #chatPanel { right: 10px !important; bottom: 10px !important; max-width: calc(100% - 20px) !important; height: min(560px, calc(100vh - 20px)) !important; }
        #chatForm { align-items: stretch; }
        #chatForm button { flex: 0 0 auto; }
    }

    /* Shared campus portal refinement */
    html { color-scheme: light; scroll-behavior: smooth; }
    body { background: #f3f6f8 !important; }

    .page {
        width: min(100%, 1220px) !important;
        padding: 18px 20px 42px !important;
    }

    .client-page-heading {
        margin-bottom: 14px;
        padding: 17px 18px;
        border-left-width: 3px;
        box-shadow: none;
    }

    .client-page-heading .eyebrow { color: #5c6f7b; letter-spacing: .055em; }
    .client-page-heading h2 { font-size: clamp(20px, 2.5vw, 25px); }
    .client-page-heading p { max-width: 680px; font-size: 13px; }

    .panel,
    .message-item,
    .stat-card,
    .service-box,
    .info-item,
    .setting-item,
    .stat,
    .attachment-section,
    .services-list,
    .description-box { box-shadow: none !important; }

    .stats { margin: 0 0 14px !important; grid-template-columns: repeat(5, minmax(0, 1fr)) !important; }
    .stat { padding: 13px 14px !important; border-left-width: 2px !important; }
    .stat strong,
    .stat-card .value,
    .stat-value { font-size: 21px !important; }

    .campus-tools { padding: 0 !important; overflow: hidden; }
    .campus-tools-header { padding: 16px 18px 12px; border-bottom: 1px solid var(--client-line); }
    .campus-tools-header h2 { margin: 0 0 3px !important; font-size: 16px !important; }
    .campus-tools-header p { margin: 0; color: var(--client-ink-500); font-size: 12px; }
    .campus-tool-list { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
    .campus-tool {
        min-width: 0;
        min-height: 112px;
        padding: 16px 17px;
        display: flex;
        align-items: flex-start;
        gap: 12px;
        color: var(--client-ink-900);
        border-right: 1px solid var(--client-line);
        background: #fff;
        text-decoration: none;
    }
    .campus-tool:last-child { border-right: 0; }
    .campus-tool:hover { background: #f7fafc; }
    .campus-tool-icon {
        width: 36px;
        height: 36px;
        flex: 0 0 36px;
        display: grid;
        place-items: center;
        color: var(--client-brand-700);
        border-radius: 6px;
        background: var(--client-brand-100);
    }
    .campus-tool-icon svg { width: 19px; height: 19px; }
    .campus-tool-copy { min-width: 0; }
    .campus-tool-copy strong { display: block; margin: 0 0 4px; font-size: 14px; }
    .campus-tool-copy span { display: block; color: var(--client-ink-500); font-size: 12px; line-height: 1.45; }
    .campus-tool-copy small { margin-top: 8px; display: inline-block; color: var(--client-brand-700); font-size: 11px; font-weight: 700; }

    .workflow-alert { box-shadow: none !important; }
    .workflow-item { color: inherit; text-decoration: none; }
    .workflow-item:focus-visible,
    .campus-tool:focus-visible { position: relative; z-index: 2; outline: 3px solid rgba(35, 109, 158, .3); outline-offset: -3px; }

    input,
    select,
    textarea,
    button { font: inherit; }
    input::placeholder,
    textarea::placeholder { color: #758590; opacity: 1; }
    .visually-hidden {
        position: absolute !important;
        width: 1px !important;
        height: 1px !important;
        padding: 0 !important;
        margin: -1px !important;
        overflow: hidden !important;
        clip: rect(0, 0, 0, 0) !important;
        white-space: nowrap !important;
        border: 0 !important;
    }
    label .required,
    .required { color: #a12e2e; }

    @media (max-width: 980px) {
        .stats { grid-template-columns: repeat(3, minmax(0, 1fr)) !important; }
        .form-row.three-col,
        .form-row.four-col,
        .info-grid.three-col,
        .info-grid.four-col,
        .three-col,
        .four-col { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; }
        .campus-tool-list { grid-template-columns: 1fr; }
        .campus-tool { min-height: 0; border-right: 0; border-bottom: 1px solid var(--client-line); }
        .campus-tool:last-child { border-bottom: 0; }
    }

    @media (max-width: 700px) {
        .page { padding: 14px 12px 30px !important; }
        .client-page-heading { padding: 15px; gap: 13px; }
        .client-page-heading h2 { font-size: 20px; }
        .stats { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 8px !important; }
        .stat { padding: 11px 12px !important; }
        .stat strong { font-size: 19px !important; }
        .campus-tools-header { padding: 14px; }
        .campus-tool { padding: 14px; }
        .workflow-item { align-items: flex-start !important; flex-direction: column; gap: 5px !important; }
        .form-row,
        .form-row.two-col,
        .form-row.three-col,
        .form-row.four-col,
        .info-grid,
        .info-grid.two-col,
        .info-grid.three-col,
        .info-grid.four-col,
        .two-col,
        .three-col,
        .four-col { grid-template-columns: 1fr !important; }
    }

    .client-record-note { margin: 0 0 7px; color: var(--client-ink-500); font-size: 11px; }
    .record-search-form {
        width: min(100%, 520px);
        display: grid !important;
        grid-template-columns: minmax(0, 1fr) auto;
        align-items: center;
        gap: 7px !important;
    }
    .record-search-form .search-input {
        width: 100%;
        min-width: 0 !important;
        min-height: 40px;
    }
    .record-search-form .button {
        width: auto !important;
        min-width: 76px;
        min-height: 40px;
        padding: 7px 12px !important;
    }
    .client-record-scroll {
        max-height: min(60vh, 600px);
        overflow: auto;
        overscroll-behavior: contain;
        scrollbar-gutter: stable;
        border: 1px solid var(--client-line);
        border-radius: 8px;
        background: #fff;
    }
    .client-record-scroll:focus-visible { outline: 3px solid rgba(35,109,158,.28); outline-offset: 2px; }
    .client-record-table thead { position: sticky; z-index: 2; top: 0; }
    .record-actions { display: flex; flex-wrap: wrap; gap: 5px; }
    .record-action {
        min-height: 36px;
        padding: 7px 10px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        color: var(--client-brand-900);
        border: 1px solid var(--client-line-strong);
        border-radius: 6px;
        background: #fff;
        font-size: 12px;
        font-weight: 700;
        text-decoration: none;
    }
    .record-action.primary { color: #fff; border-color: var(--client-brand-700); background: var(--client-brand-700); }
    .detail-document-actions { max-width: 100%; display: flex; align-items: center; justify-content: flex-end; flex-wrap: wrap; gap: 6px; }
    .detail-document-actions .button,
    .detail-document-actions button.button { width: auto !important; min-width: 0; min-height: 38px; padding: 7px 10px !important; font-size: 12px !important; white-space: nowrap !important; }

    @media (max-width: 1024px) {
        .client-record-scroll { max-height: min(56vh, 540px); padding: 9px; }
        .client-record-table,
        .client-record-table tbody { width: 100%; display: block !important; }
        .client-record-table thead { display: none !important; }
        .client-record-table tbody { display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 9px; }
        .client-record-table tbody tr { padding: 12px; display: grid !important; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px 12px; border: 1px solid var(--client-line); border-radius: 7px; background: #fff; }
        .client-record-table tbody td { width: auto !important; padding: 0 !important; display: block !important; border: 0 !important; overflow-wrap: anywhere; }
        .client-record-table tbody td::before { content: attr(data-label); margin-bottom: 3px; display: block !important; color: var(--client-ink-500); font-size: 9px; font-weight: 800; letter-spacing: .04em; text-transform: uppercase; }
        .client-record-table tbody td:first-child,
        .client-record-table tbody td:last-child { grid-column: 1 / -1; }
        .client-record-table .record-action { flex: 1 1 auto; }
    }

    @media (max-width: 640px) {
        .record-search-form { width: 100%; grid-template-columns: minmax(0, 1fr) auto !important; }
        .record-search-form .search-input { min-height: 40px; font-size: 13px; }
        .record-search-form .button { width: auto !important; min-height: 40px; padding: 7px 10px !important; }
        .client-record-scroll { max-height: min(52vh, 450px); padding: 7px; }
        .client-record-table tbody { grid-template-columns: 1fr; gap: 7px; }
        .client-record-table tbody tr { padding: 11px; }
        .client-record-table tbody td:nth-child(2) { display: none !important; }
        .client-record-table .record-actions { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .client-record-table .record-action { width: 100%; min-height: 40px; }
        .detail-document-actions { width: 100%; display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .detail-document-actions .button,
        .detail-document-actions button.button { width: 100% !important; min-height: 40px; white-space: normal !important; }
    }

    @media (max-width: 390px) {
        .page { padding: 10px 8px 24px !important; }
        .panel { padding: 12px !important; }
        .client-page-heading { padding: 12px; }
        .client-page-heading h2 { font-size: 18px; }
        .client-page-heading p { font-size: 12px; }
        .stats { grid-template-columns: 1fr !important; }
    }

    /* Unified institutional client interface */
    body {
        min-width: 320px;
        font-size: 16px;
        letter-spacing: 0;
        background:
            linear-gradient(180deg, rgba(31, 98, 142, .035), transparent 180px),
            var(--client-page) !important;
    }

    .page {
        width: min(100%, 1240px) !important;
        padding: 24px 24px 48px !important;
    }

    .panel {
        padding: 18px !important;
        border-color: var(--client-line-strong) !important;
        box-shadow: var(--client-shadow) !important;
    }

    .section-header,
    .client-page-heading {
        margin-bottom: 18px !important;
        padding: 0 0 15px !important;
        border: 0 !important;
        border-bottom: 2px solid var(--client-line) !important;
        border-radius: 0 !important;
        background: transparent !important;
        box-shadow: none !important;
    }

    .section-header h1,
    .client-page-heading h2 {
        margin-bottom: 4px !important;
        color: var(--client-ink-900) !important;
        font-family: "Segoe UI", system-ui, sans-serif !important;
        font-size: clamp(20px, 2.4vw, 25px) !important;
        font-weight: 720 !important;
        letter-spacing: -.025em !important;
    }

    .section-header small,
    .client-page-heading p {
        max-width: 720px;
        color: var(--client-ink-500) !important;
        font-size: 15px !important;
        line-height: 1.55 !important;
    }

    .section > h3,
    .panel h2,
    .panel h3 {
        font-family: "Segoe UI", system-ui, sans-serif !important;
        font-weight: 720 !important;
        letter-spacing: -.012em !important;
    }

    .button,
    button.button,
    .btn,
    .btn-primary,
    .btn-secondary,
    .action-link,
    .filter-btn,
    .attachment-link {
        min-height: 44px !important;
        padding: 10px 14px !important;
        border-radius: 6px !important;
        font-size: 14px !important;
        font-weight: 700 !important;
        line-height: 1.2 !important;
    }

    .stats,
    .stats-grid {
        gap: 10px !important;
    }

    .stat,
    .stat-card {
        min-height: 104px;
        padding: 14px 15px !important;
        border: 1.5px solid var(--client-line-strong) !important;
        border-top: 3px solid var(--client-brand-700) !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .stat strong,
    .stat-card .value,
    .stat-value {
        font-size: 22px !important;
        font-weight: 750 !important;
    }

    .stat > span,
    .stat-card .stat-label {
        font-size: 14px !important;
    }

    .service-card,
    .campus-tool,
    .tracking-card,
    .workflow-item {
        border-color: var(--client-line-strong) !important;
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .service-card {
        min-height: 0 !important;
        padding: 16px !important;
    }

    .service-card-icon,
    .campus-tool-icon {
        color: var(--client-brand-900) !important;
        background: var(--client-brand-100) !important;
    }

    .info-item,
    .setting-item,
    .description-box,
    .services-list,
    .attachment-section {
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .form-group input,
    .form-group textarea,
    .form-group select,
    .search-input,
    .search-box input,
    input:not([type="checkbox"]):not([type="radio"]):not([type="hidden"]),
    select,
    textarea {
        min-height: 48px;
        padding: 10px 12px;
        font-size: 16px !important;
        border-color: var(--client-line-strong) !important;
        border-radius: 6px !important;
        box-shadow: none !important;
    }

    table {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }

    th {
        color: #4c626f !important;
        background: #f3f6f8 !important;
        font-size: 13px !important;
        font-weight: 800 !important;
        letter-spacing: .045em !important;
        text-transform: uppercase;
    }

    td {
        color: var(--client-ink-700) !important;
        font-size: 14px !important;
        line-height: 1.55 !important;
    }

    tr:last-child td { border-bottom: 0 !important; }

    .status-badge,
    .type-badge {
        min-height: 28px;
        padding: 4px 8px !important;
        border-radius: 999px !important;
        box-shadow: none !important;
        font-size: 13px !important;
        font-weight: 750 !important;
    }

    .toolbar-pills {
        padding-bottom: 2px;
        gap: 5px !important;
    }

    .toolbar-pills .button {
        min-height: 44px !important;
        padding: 9px 12px !important;
    }

    .client-record-scroll {
        border-radius: 8px;
        box-shadow: none;
    }

    .record-action {
        min-height: 44px;
        border-radius: 5px;
        font-size: 14px;
    }

    .form-group label,
    .checks label { font-size: 15px !important; line-height: 1.5 !important; }
    .form-group small,
    .password-hint,
    .helper-text { font-size: 14px !important; line-height: 1.5 !important; }
    .info-label,
    .item strong { font-size: 14px !important; }
    .info-value,
    .message-from,
    .message-preview,
    .description-box { font-size: 15px !important; line-height: 1.6 !important; }
    .message-subject,
    .message-time,
    .stat-hint { font-size: 14px !important; line-height: 1.5 !important; }
    .detail-document-actions .button,
    .detail-document-actions button.button { min-height: 44px !important; font-size: 14px !important; }

    .alert,
    .workflow-alert,
    .feedback-summary {
        border-radius: 8px !important;
        box-shadow: none !important;
    }

    .record-pagination {
        margin-top: 16px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
    }
    .record-pagination a,
    .record-pagination .record-pagination-disabled {
        min-height: 44px;
        padding: 9px 14px;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border: 1px solid #c5d3dc;
        border-radius: 6px;
        color: #174f75;
        background: #fff;
        font-size: 14px;
        font-weight: 700;
        text-decoration: none;
    }
    .record-pagination a:hover { background: #eef6fb; }
    .record-pagination .record-pagination-disabled { color: #405764; background: #f3f5f6; }
    .record-pagination-current { color: #405764; font-size: 13px; font-weight: 700; text-align: center; }

    @media (max-width: 860px) {
        .page { padding: 20px 20px 40px !important; }
        .panel { padding: 18px !important; }
        .section-header,
        .client-page-heading { align-items: flex-start !important; }
    }

    @media (min-width: 701px) and (max-width: 860px) {
        .client-page-heading {
            align-items: center !important;
            flex-direction: row !important;
            justify-content: space-between !important;
        }
        .client-page-heading .button,
        .client-page-heading .btn {
            width: auto !important;
            min-width: max-content !important;
            flex: 0 0 auto;
        }
        .section-header .header-content,
        .top-bar {
            align-items: center !important;
            flex-direction: row !important;
            flex-wrap: nowrap !important;
            justify-content: space-between !important;
        }
        .section-header .header-content .button,
        .section-header .header-content .btn,
        .top-bar .button,
        .top-bar .back-btn {
            width: auto !important;
            min-width: max-content !important;
            flex: 0 0 auto;
        }
    }

    @media (max-width: 640px) {
        .record-pagination { gap: 6px; }
        .record-pagination a,
        .record-pagination .record-pagination-disabled { padding-inline: 9px; font-size: 13px; }
        .record-pagination-current { font-size: 12px; }
        body { font-size: 16px; }
        .page { padding: 16px 12px 32px !important; }
        .panel { padding: 16px !important; border-radius: 8px !important; }
        .section-header,
        .client-page-heading { gap: 11px !important; }
        .section-header .header-content,
        .client-page-heading { align-items: stretch !important; flex-direction: column !important; }
        .section-header .button,
        .client-page-heading .button { width: 100% !important; min-width: 0 !important; }
        .service-card { padding: 14px !important; }
        .stat,
        .stat-card { min-height: 96px; padding: 12px !important; }
        .stat strong,
        .stat-card .value,
        .stat-value { font-size: 19px !important; }
    }

    @media (max-width: 390px) {
        .page { padding-inline: 10px !important; }
        .panel { padding: 14px !important; }
        .stats,
        .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)) !important; gap: 7px !important; }
        .stat,
        .stat-card { min-height: 86px; padding: 10px !important; }
        .stat strong,
        .stat-card .value,
        .stat-value { font-size: 18px !important; }
        .stat > span,
        .stat-card .stat-label { font-size: 14px !important; }
    }

    @media (prefers-contrast: more) {
        :root { --client-line: #9aa8b1; --client-line-strong: #71818b; --client-ink-500: #44545e; }
        .button, button.button, .btn-primary { border: 2px solid #0d3f61 !important; }
    }

    @media (forced-colors: active) {
        .status-badge,
        .type-badge,
        .nav-count,
        .notification-count { border: 1px solid currentColor !important; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('[data-pdf-action]').forEach(function (link) {
            link.addEventListener('click', function () {
                var original = link.textContent;
                link.textContent = 'Preparing PDF...';
                link.setAttribute('aria-busy', 'true');
                window.setTimeout(function () {
                    link.textContent = original;
                    link.removeAttribute('aria-busy');
                }, 4500);
            });
        });
    });
</script>
@include('partials.submission-feedback')
