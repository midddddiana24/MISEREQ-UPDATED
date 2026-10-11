<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Admin Panel') - MIS Service Request</title>
    <style>
        :root {
            --brand-900: #163a5f;
            --brand-700: #2f6ea1;
            --brand-600: #1f6fa8;
            --brand-100: #eaf2f8;
            --brand-accent: #f4c430;
            --surface: #ffffff;
            --surface-soft: #f7f9fb;
            --page: #f4f6f8;
            --ink-900: #14202b;
            --ink-700: #344451;
            --ink-500: #52616d;
            --line: #cfd8e0;
            --success: #1c7c54;
            --success-bg: #e9f7f0;
            --warning: #80520c;
            --warning-bg: #fff5de;
            --danger: #bb4b4b;
            --danger-bg: #fdeeee;
            --shadow: 0 3px 12px rgba(15, 23, 42, 0.06);
            --radius-xl: 14px;
            --radius-lg: 12px;
            --radius-md: 10px;
            --radius-sm: 8px;
            --sidebar-width: 264px;
            --sidebar-collapsed-width: 88px;
            --content-width: 1320px;
        }

        * {
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
            font-size: 100%;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            font-size: 17px;
            line-height: 1.65;
            color: var(--ink-900);
            background: var(--page);
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        a:focus-visible,
        button:focus-visible,
        input:focus-visible,
        select:focus-visible,
        textarea:focus-visible {
            outline: 3px solid #17689f;
            outline-offset: 3px;
        }

        button,
        input,
        select,
        textarea {
            font: inherit;
        }

        .skip-link {
            position: fixed;
            top: 10px;
            left: 10px;
            z-index: 100;
            padding: 10px 14px;
            border-radius: 8px;
            background: #fff;
            color: var(--brand-900);
            border: 2px solid var(--brand-900);
            font-weight: 700;
            transform: translateY(-150%);
        }

        .skip-link:focus { transform: translateY(0); }

        .sr-only {
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

        .admin-pagination { display: grid; gap: 8px; min-width: 0; color: var(--ink-700); font-size: 13px; line-height: 1.3; }
        .admin-pagination-summary { color: var(--ink-500); }
        .admin-pagination-controls { display: flex; flex-wrap: wrap; align-items: center; gap: 4px; }
        .admin-pagination-controls a,
        .admin-pagination-controls .admin-pagination-disabled,
        .admin-pagination-controls .admin-pagination-page-current {
            display: inline-flex;
            min-width: 32px;
            min-height: 32px;
            padding: 5px 8px;
            align-items: center;
            justify-content: center;
            border: 1px solid var(--line);
            border-radius: 6px;
            background: #fff;
            color: var(--brand-900);
            font-size: 13px;
            font-weight: 700;
            line-height: 1;
            text-decoration: none;
        }
        .admin-pagination-controls a:hover { border-color: var(--brand-700); background: var(--brand-100); }
        .admin-pagination-controls a:focus-visible { outline: 3px solid var(--brand-600); outline-offset: 2px; }
        .admin-pagination-controls .admin-pagination-page-current { border-color: var(--brand-700); background: var(--brand-100); }
        .admin-pagination-controls .admin-pagination-disabled { color: var(--ink-500); background: var(--surface-soft); }
        .admin-pagination-ellipsis { padding: 0 3px; }
        .admin-pagination-compact .admin-pagination-controls { justify-content: space-between; gap: 6px; }
        .admin-pagination-compact .admin-pagination-current { color: var(--ink-700); font-size: 12px; font-weight: 700; white-space: nowrap; }

        .admin-shell {
            display: grid;
            grid-template-columns: var(--sidebar-width) minmax(0, 1fr);
            min-height: 100vh;
            transition: grid-template-columns 0.2s ease;
        }

        .admin-sidebar {
            position: sticky;
            top: 0;
            height: 100vh;
            padding: 22px 18px;
            background: #f8fafc;
            color: var(--ink-900);
            border-right: 1px solid var(--line);
            transition: padding 0.2s ease;
        }

        .sidebar-panel {
            display: flex;
            flex-direction: column;
            height: 100%;
            background: transparent;
            border: 0;
            border-radius: 0;
            padding: 0;
        }

        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            margin-bottom: 24px;
        }

        .sidebar-collapse-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            margin: -12px 0 14px auto;
            padding: 0;
            border: 1px solid var(--line);
            border-radius: 9px;
            color: var(--ink-700);
            background: var(--surface);
            cursor: pointer;
            font-size: 22px;
            line-height: 1;
        }

        .sidebar-collapse-btn:hover {
            color: var(--brand-900);
            background: var(--brand-100);
        }

        .sidebar-brand-mark {
            width: 42px;
            height: 42px;
            border-radius: 12px;
            background: var(--brand-900);
            color: #fff;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 16px;
            font-weight: 700;
        }

        .sidebar-brand-text small {
            display: block;
            color: var(--ink-500);
            font-size: 12px;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }

        .sidebar-brand-text strong {
            display: block;
            margin-top: 2px;
            font-size: 19px;
            font-weight: 700;
            line-height: 1.2;
        }

        .sidebar-badge {
            display: inline-flex;
            align-items: center;
            width: fit-content;
            padding: 6px 10px;
            border-radius: 999px;
            background: var(--brand-100);
            color: var(--brand-900);
            font-size: 12px;
            font-weight: 600;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 18px;
        }

        .sidebar-nav {
            display: grid;
            gap: 4px;
        }

        .sidebar-link {
            display: flex;
            align-items: center;
            gap: 10px;
            min-height: 48px;
            padding: 11px 13px;
            border-radius: 10px;
            color: var(--ink-700);
            transition: background 0.18s ease, color 0.18s ease;
            font-size: 16px;
            font-weight: 600;
        }

        .sidebar-link:hover {
            background: #e8eef3;
            color: var(--ink-900);
        }

        .sidebar-link.active {
            background: var(--brand-100);
            color: var(--brand-900);
            border: 1px solid #b9cfdf;
        }

        .sidebar-link-icon {
            position: relative;
            width: 30px;
            height: 30px;
            border-radius: 8px;
            background: transparent;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 13px;
            font-weight: 700;
            flex-shrink: 0;
            border: 1px solid var(--line);
        }

        .sidebar-icon-count {
            position: absolute;
            top: -7px;
            right: -8px;
            min-width: 18px;
            height: 18px;
            padding: 0 4px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            border: 2px solid #fff;
            border-radius: 999px;
            background: #b42318;
            font-size: 9px;
            font-weight: 800;
            line-height: 1;
        }

        .sidebar-icon-count[hidden] { display: none; }

        .sidebar-link.active .sidebar-link-icon {
            background: var(--brand-100);
            color: var(--brand-900);
            border-color: transparent;
        }

        .sidebar-meta {
            margin-top: auto;
            padding: 16px 12px 0;
            border-top: 1px solid var(--line);
            color: var(--ink-500);
        }

        .sidebar-meta strong {
            display: block;
            color: var(--ink-900);
            font-size: 16px;
            margin-bottom: 2px;
        }

        .sidebar-meta span {
            font-size: 14px;
        }

        @media (min-width: 1101px) {
            .admin-shell.sidebar-collapsed {
                grid-template-columns: var(--sidebar-collapsed-width) minmax(0, 1fr);
            }

            .sidebar-collapsed .admin-sidebar {
                padding: 22px 12px;
            }

            .sidebar-collapsed .sidebar-brand {
                justify-content: center;
                margin-bottom: 18px;
            }

            .sidebar-collapsed .sidebar-brand-text,
            .sidebar-collapsed .sidebar-badge,
            .sidebar-collapsed .sidebar-link > span:not(.sidebar-link-icon),
            .sidebar-collapsed .sidebar-meta {
                display: none;
            }

            .sidebar-collapsed .sidebar-collapse-btn {
                margin-right: auto;
            }

            .sidebar-collapsed .sidebar-link {
                justify-content: center;
                padding: 9px;
            }

            .sidebar-collapsed .sidebar-link-icon {
                width: 34px;
                height: 34px;
            }
        }

        .admin-main {
            min-width: 0;
            padding: 0 28px 40px;
        }

        .admin-header {
            position: sticky;
            top: 0;
            z-index: 20;
            margin: 0 -28px 24px;
            background: var(--brand-900);
            border-bottom: 3px solid var(--brand-accent);
            color: #fff;
            box-shadow: 0 4px 14px rgba(22, 58, 95, 0.16);
        }

        .admin-header-inner {
            max-width: var(--content-width);
            margin: 0 auto;
            padding: 14px 28px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
        }

        .admin-header-brand {
            display: flex;
            align-items: center;
            gap: 18px;
            min-width: 0;
        }

        .admin-header-brand img {
            width: 64px;
            height: 64px;
            object-fit: cover;
            flex-shrink: 0;
            background: #fff;
            border-radius: 50%;
            padding: 5px;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.14);
        }

        .admin-header-copy {
            min-width: 0;
            line-height: 1.1;
        }

        .admin-header-copy small {
            display: block;
            margin-bottom: 4px;
            font: 700 14px Georgia, "Times New Roman", serif;
            opacity: 0.95;
        }

        .admin-header-copy strong {
            display: block;
            font: 700 clamp(21px, 2.2vw, 29px) Georgia, "Times New Roman", serif;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }

        .admin-header-copy span {
            display: block;
            margin-top: 4px;
            font: 600 14px Georgia, "Times New Roman", serif;
            opacity: 0.96;
        }

        .admin-header-meta {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .header-chip {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            min-height: 44px;
            padding: 10px 15px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.12);
            border: 1px solid rgba(255, 255, 255, 0.2);
            color: #fff;
            font-size: 14px;
            font-weight: 600;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            padding: 0 0 22px;
            background: transparent;
            border: 0;
            border-bottom: 1px solid var(--line);
            border-radius: 0;
            box-shadow: none;
            margin: 0 auto 24px;
            max-width: var(--content-width);
        }

        .topbar-title h1 {
            margin: 0;
            font-size: clamp(28px, 3vw, 36px);
            font-weight: 700;
            color: var(--ink-900);
            line-height: 1.15;
            letter-spacing: -0.02em;
        }

        .topbar-title p {
            margin: 6px 0 0;
            color: var(--ink-500);
            max-width: 760px;
            line-height: 1.65;
            font-size: 16px;
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 10px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .topbar-kicker {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 0;
            border-radius: 999px;
            background: transparent;
            color: var(--brand-700);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 10px;
        }

        .btn,
        .btn-primary,
        .btn-ghost,
        .btn-danger,
        .btn-secondary {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            border: 1px solid transparent;
            min-height: 46px;
            border-radius: 9px;
            padding: 11px 18px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 700;
            transition: background 0.18s ease, border-color 0.18s ease, color 0.18s ease;
        }

        .btn:hover,
        .btn-primary:hover,
        .btn-ghost:hover,
        .btn-danger:hover,
        .btn-secondary:hover {
            transform: none;
        }

        .btn-primary {
            background: var(--brand-900);
            color: #fff;
        }

        .btn-primary:hover { background: #0f2d49; }

        .btn-ghost,
        .btn-secondary {
            background: var(--surface);
            border-color: var(--line);
            color: var(--ink-900);
        }

        .btn-ghost {
            background: #fff;
        }

        .btn-ghost:hover,
        .btn-secondary:hover {
            background: #eef3f7;
            border-color: #aebbc6;
        }

        .btn-danger {
            background: var(--surface);
            border-color: #efc3c3;
            color: var(--danger);
        }

        .btn-danger:hover { background: var(--danger-bg); border-color: #d89494; }

        .notification-shell {
            position: relative;
        }

        .notification-trigger {
            position: relative;
            min-width: 44px;
            width: 44px;
            height: 44px;
            padding: 0;
            border-radius: 999px;
            font-size: 18px;
            line-height: 1;
        }

        .notification-trigger::before {
            content: "\1F514";
            font-size: 18px;
            line-height: 1;
        }

        .notification-trigger > span:first-child {
            font-size: 0;
        }

        .notification-count {
            position: absolute;
            top: -5px;
            right: -5px;
            min-width: 20px;
            height: 20px;
            padding: 0 6px;
            border-radius: 999px;
            background: var(--danger);
            color: #fff;
            font-size: 12px;
            font-weight: 700;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border: 2px solid #fff;
        }

        .notification-count[hidden] {
            display: none;
        }

        .notification-panel {
            position: absolute;
            top: calc(100% + 10px);
            right: 0;
            width: min(380px, calc(100vw - 32px));
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 16px;
            box-shadow: 0 18px 36px rgba(15, 23, 42, 0.12);
            overflow: hidden;
            display: none;
            z-index: 30;
        }

        .notification-panel.open {
            display: block;
        }

        .notification-panel-head {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            padding: 14px 16px;
            border-bottom: 1px solid var(--line);
            background: #fbfcfd;
        }

        .notification-panel-head strong {
            font-size: 16px;
            color: var(--ink-900);
        }

        .notification-panel-head span {
            color: var(--ink-500);
            font-size: 14px;
        }

        .notification-clear {
            border: 0;
            background: transparent;
            color: var(--primary);
            font: inherit;
            font-size: 13px;
            font-weight: 700;
            cursor: pointer;
            padding: 8px;
        }

        .notification-list {
            max-height: 420px;
            overflow-y: auto;
        }

        .notification-item {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto;
            align-items: start;
            border-bottom: 1px solid #edf1f5;
            color: inherit;
            background: #fff;
        }

        .notification-item-link {
            min-width: 0;
            padding: 14px 10px 14px 16px;
            color: inherit;
            text-decoration: none;
        }

        .notification-delete {
            align-self: center;
            margin-right: 8px;
            border: 0;
            background: transparent;
            color: var(--ink-500);
            cursor: pointer;
            font: inherit;
            font-size: 12px;
            padding: 8px;
        }

        .notification-delete:hover,
        .notification-clear:hover {
            color: var(--danger);
        }

        .notification-item:hover {
            background: #f8fbfd;
        }

        .notification-item.unread {
            background: #f5faff;
        }

        .notification-item:last-child {
            border-bottom: 0;
        }

        .notification-item-title {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-bottom: 6px;
        }

        .notification-item-title strong {
            color: var(--ink-900);
            font-size: 15px;
        }

        .notification-item-time {
            color: var(--ink-500);
            font-size: 13px;
            white-space: nowrap;
        }

        .notification-item-copy {
            color: var(--ink-700);
            font-size: 15px;
            line-height: 1.45;
        }

        .notification-empty {
            padding: 22px 16px;
            color: var(--ink-500);
            font-size: 13px;
            text-align: center;
        }

        .content-grid {
            display: grid;
            grid-template-columns: minmax(0, 1fr);
            gap: 22px;
            max-width: var(--content-width);
            margin: 0 auto;
        }

        .split-layout {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 16px;
        }

        .admin-strip {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
            gap: 12px;
        }

        .admin-mini-card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            padding: 18px;
        }

        .admin-mini-card strong {
            display: block;
            color: var(--ink-500);
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 6px;
        }

        .admin-mini-card span {
            display: block;
            font-size: 30px;
            font-weight: 700;
            color: var(--ink-900);
        }

        .admin-list {
            margin: 0;
            padding-left: 18px;
            color: var(--ink-500);
            line-height: 1.6;
        }

        .toolbar-row {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            margin-bottom: 8px;
        }

        .table-note {
            color: var(--ink-500);
            font-size: 14px;
        }

        .panel,
        .card {
            background: var(--surface);
            border: 1px solid var(--line);
            border-radius: var(--radius-xl);
            box-shadow: var(--shadow);
            padding: 24px;
        }

        .admin-disclosure {
            border: 1px solid var(--line);
            border-radius: var(--radius-lg);
            background: #fbfcfd;
        }

        .admin-disclosure > summary {
            cursor: pointer;
            color: var(--ink-900);
            font-size: 15px;
            font-weight: 700;
            list-style-position: outside;
        }

        .admin-disclosure > summary:hover,
        .admin-disclosure > summary:focus-visible {
            color: var(--brand-900);
        }

        details.panel.admin-disclosure[open] > summary {
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
        }

        .section-heading {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 18px;
        }

        .section-heading h2,
        .section-heading h3 {
            margin: 0;
            color: var(--ink-900);
            font-size: 22px;
            font-weight: 700;
        }

        .section-heading p {
            margin: 4px 0 0;
            color: var(--ink-500);
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .compact-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 12px;
        }

        .stat-card {
            padding: 18px;
            border-radius: var(--radius-lg);
            background: var(--surface-soft);
            border: 1px solid var(--line);
        }

        .stat-card .label {
            display: block;
            color: var(--ink-500);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 8px;
        }

        .stat-card .value {
            color: var(--ink-900);
            font-size: 32px;
            font-weight: 700;
        }

        .stat-card .hint {
            margin-top: 6px;
            color: var(--ink-500);
            font-size: 14px;
        }

        .stat-card.admin {
            background: var(--surface);
            border: 1px solid var(--line);
        }

        .stat-card.admin .value {
            font-size: 26px;
        }

        .stat-card.alert {
            background: var(--warning-bg);
        }

        .table-wrap {
            overflow-x: auto;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 16px;
            border-bottom: 1px solid var(--line);
            text-align: left;
            vertical-align: top;
        }

        th {
            color: var(--ink-700);
            font-size: 14px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: #fafbfc;
        }

        td {
            color: var(--ink-700);
            font-size: 16px;
        }

        tbody tr:nth-child(even) { background: #fbfcfd; }
        tbody tr:hover { background: #eef5fa; }

        table.compact-table th,
        table.compact-table td {
            padding: 12px;
            font-size: 15px;
        }

        table.compact-table th {
            background: #fafbfc;
        }

        .table-cell-title {
            font-weight: 700;
            color: var(--ink-900);
        }

        .table-cell-meta {
            color: var(--ink-500);
            font-size: 14px;
            margin-top: 3px;
        }

        .badge,
        .status-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 10px;
            border-radius: 999px;
            font-size: 13px;
            font-weight: 700;
            text-transform: capitalize;
        }

        .status-badge.submitted,
        .status-badge.pending,
        .status-badge.awaiting_requester_info {
            background: var(--warning-bg);
            color: var(--warning);
        }

        .status-badge.processing,
        .status-badge.awaiting_confirmation,
        .status-badge.medium,
        .status-badge.available,
        .status-badge.active {
            background: var(--brand-100);
            color: var(--brand-900);
        }

        .status-badge.completed,
        .status-badge.closed,
        .status-badge.low {
            background: var(--success-bg);
            color: var(--success);
        }

        .status-badge.rejected,
        .status-badge.cancelled,
        .status-badge.escalated,
        .status-badge.urgent,
        .status-badge.on_leave,
        .status-badge.inactive {
            background: var(--danger-bg);
            color: var(--danger);
        }

        .status-badge.busy {
            background: #f3e8ff;
            color: #7c3aed;
        }

        .status-badge.reopened,
        .status-badge.high {
            background: #fff1e6;
            color: #a54808;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(12, minmax(0, 1fr));
            gap: 16px;
        }

        .form-col-12 { grid-column: span 12; }
        .form-col-8 { grid-column: span 8; }
        .form-col-6 { grid-column: span 6; }
        .form-col-4 { grid-column: span 4; }
        .form-col-3 { grid-column: span 3; }

        .field {
            display: grid;
            gap: 8px;
        }

        .field label {
            color: var(--ink-900);
            font-size: 15px;
            font-weight: 700;
        }

        .field input,
        .field select,
        .field textarea {
            width: 100%;
            border: 1px solid var(--line);
            min-height: 48px;
            border-radius: 9px;
            background: #fff;
            padding: 11px 13px;
            color: var(--ink-900);
            font-size: 16px;
        }

        .field textarea {
            min-height: 120px;
            resize: vertical;
        }

        .field input:focus,
        .field select:focus,
        .field textarea:focus {
            outline: none;
            border-color: var(--brand-700);
            box-shadow: 0 0 0 3px rgba(47, 110, 161, 0.16);
        }

        .field-hint {
            color: var(--ink-500);
            font-size: 14px;
            line-height: 1.45;
        }

        .error-text {
            color: var(--danger);
            font-size: 14px;
            font-weight: 700;
        }

        .form-actions {
            display: flex;
            gap: 10px;
            flex-wrap: wrap;
            margin-top: 8px;
        }

        .admin-record-actions {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 6px;
        }
        .admin-record-actions form { display: inline-flex; margin: 0; }
        .admin-record-actions .btn-primary,
        .admin-record-actions .btn-ghost,
        .admin-record-actions .btn-danger {
            min-height: 34px;
            padding: 6px 10px;
            font-size: 12px;
            white-space: nowrap;
        }

        .admin-record-filter-panel { padding: 16px; }
        .admin-record-filter-grid {
            display: grid;
            grid-template-columns: minmax(220px, 2fr) minmax(145px, 1fr) minmax(145px, 1fr) auto;
            align-items: end;
            gap: 10px;
        }
        .admin-record-filter-grid > .field { min-width: 0; }
        .admin-record-filter-grid .field input,
        .admin-record-filter-grid .field select { min-width: 0; }
        .admin-record-filter-actions { display: flex; gap: 7px; }
        .admin-record-filter-actions .btn-primary,
        .admin-record-filter-actions .btn-ghost { min-height: 42px; padding: 8px 12px; font-size: 14px; }
        .admin-filter-advanced { margin-top: 12px; padding: 0; overflow: hidden; }
        .admin-filter-advanced > summary { padding: 11px 13px; }
        .admin-filter-advanced[open] > summary { border-bottom: 1px solid var(--line); }
        .admin-filter-advanced-fields { padding: 13px; }
        @media (max-width: 1050px) {
            .admin-record-filter-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
        @media (max-width: 650px) {
            .admin-record-filter-grid { grid-template-columns: minmax(0, 1fr); }
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
            gap: 12px;
        }

        .info-item {
            padding: 16px;
            border-radius: var(--radius-lg);
            background: var(--surface-soft);
            border: 1px solid var(--line);
        }

        .info-item .label {
            display: block;
            color: var(--ink-500);
            font-size: 13px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 6px;
        }

        .info-item .value {
            color: var(--ink-900);
            font-size: 16px;
            line-height: 1.5;
            word-break: break-word;
        }

        .checkbox-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 14px;
        }

        .checkbox-card {
            display: flex;
            align-items: flex-start;
            gap: 13px;
            min-height: 54px;
            padding: 15px 17px;
            border-radius: var(--radius-lg);
            background: var(--surface-soft);
            border: 1px solid var(--line);
            transition: border-color 0.18s ease, box-shadow 0.18s ease, background 0.18s ease;
            cursor: pointer;
        }

        .checkbox-card:hover {
            border-color: #c8d3df;
            background: #fbfcfd;
        }

        .checkbox-card:focus-within {
            border-color: var(--brand-700);
            box-shadow: 0 0 0 3px rgba(47, 110, 161, 0.12);
            background: #fff;
        }

        .checkbox-card input[type="checkbox"] {
            width: 20px;
            height: 20px;
            margin: 2px 0 0;
            accent-color: var(--brand-700);
            flex-shrink: 0;
        }

        .checkbox-card-copy {
            display: grid;
            gap: 2px;
        }

        .checkbox-card-title {
            color: var(--ink-900);
            font-size: 16px;
            font-weight: 650;
            line-height: 1.45;
        }

        .checkbox-stack {
            display: grid;
            gap: 12px;
        }

        .case-layout {
            display: grid;
            grid-template-columns: minmax(0, 1.65fr) minmax(320px, 0.95fr);
            gap: 16px;
        }

        .case-stack {
            display: grid;
            gap: 16px;
        }

        .case-banner {
            display: flex;
            align-items: flex-start;
            justify-content: space-between;
            gap: 14px;
            flex-wrap: wrap;
            padding-bottom: 14px;
            border-bottom: 1px solid var(--line);
            margin-bottom: 16px;
        }

        .case-id {
            display: inline-block;
            padding: 5px 8px;
            border: 1px solid var(--line);
            background: #fafbfc;
            font-size: 13px;
            font-weight: 600;
            color: var(--ink-500);
            border-radius: 999px;
        }

        .case-actions {
            display: grid;
            gap: 12px;
        }

        .case-actions .panel {
            padding: 16px;
        }

        .admin-panel-title {
            margin: 0 0 10px 0;
            color: var(--ink-900);
            font-size: 19px;
        }

        .timeline-list {
            display: grid;
            gap: 10px;
        }

        .timeline-entry {
            border: 1px solid var(--line);
            background: var(--surface-soft);
            padding: 14px;
            border-radius: 10px;
        }

        .timeline-entry-head {
            display: flex;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
            margin-bottom: 6px;
        }

        .alert {
            padding: 15px 17px;
            border-radius: 12px;
            margin: 0 auto 16px;
            border: 1px solid transparent;
            font-weight: 600;
            font-size: 16px;
            max-width: var(--content-width);
        }

        .alert-success {
            background: var(--success-bg);
            color: var(--success);
            border-color: #bfe8d1;
        }

        .alert-error {
            background: var(--danger-bg);
            color: var(--danger);
            border-color: #f5c7c7;
        }

        .alert-warning {
            background: var(--warning-bg);
            color: var(--warning);
            border-color: #f2d7a2;
        }

        .empty-state {
            padding: 34px 20px;
            text-align: center;
            color: var(--ink-500);
            font-size: 16px;
        }

        .mobile-menu-btn {
            display: none;
            width: 46px;
            height: 46px;
            border: 1px solid var(--line);
            border-radius: 10px;
            background: #fff;
            color: var(--ink-900);
            font-size: 22px;
            line-height: 1;
            cursor: pointer;
            margin-bottom: 12px;
        }

        .sidebar-backdrop {
            position: fixed;
            inset: 0;
            z-index: 19;
            display: none;
            border: 0;
            background: rgba(15, 23, 42, 0.48);
        }

        .sidebar-backdrop.open { display: block; }

        @media (prefers-reduced-motion: reduce) {
            html { scroll-behavior: auto; }
            *, *::before, *::after { transition: none !important; }
        }

        @media (max-width: 1100px) {
            .sidebar-collapse-btn {
                display: none;
            }

            .admin-shell {
                grid-template-columns: 1fr;
            }

            .admin-sidebar {
                position: fixed;
                left: 0;
                top: 0;
                bottom: 0;
                width: min(280px, calc(100vw - 24px));
                transform: translateX(-110%);
                z-index: 21;
                transition: transform 0.25s ease;
                box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
            }

            .admin-sidebar.open {
                transform: translateX(0);
            }

            .admin-main {
                padding: 0 18px 32px;
            }

            .admin-header {
                margin-left: -18px;
                margin-right: -18px;
            }

            .admin-header-inner {
                padding: 16px 18px;
            }

            .mobile-menu-btn {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .topbar {
                gap: 16px;
            }
        }

        @media (max-width: 860px) {
            .split-layout {
                grid-template-columns: 1fr;
            }

            .case-layout {
                grid-template-columns: 1fr;
            }

            .form-col-8,
            .form-col-6,
            .form-col-4,
            .form-col-3 {
                grid-column: span 12;
            }

            .checkbox-grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 760px) {
            .admin-header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .admin-header-brand {
                gap: 14px;
            }

            .admin-header-brand img {
                width: 62px;
                height: 62px;
            }

            .admin-header-meta {
                width: 100%;
                justify-content: flex-start;
            }

            .topbar,
            .panel,
            .card {
                border-radius: 16px;
            }

            .topbar-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .topbar-actions .btn,
            .topbar-actions .btn-primary,
            .topbar-actions .btn-ghost,
            .topbar-actions .btn-danger,
            .topbar-actions .btn-secondary,
            .topbar-actions form {
                min-width: 0;
            }

            th, td { padding: 14px; }
        }

        /* Minimal shared admin system */
        :root {
            --page: #f6f7f9;
            --surface-soft: #f8fafb;
            --line: #dce2e8;
            --shadow: 0 1px 3px rgba(15, 23, 42, .04);
            --radius-xl: 10px;
            --radius-lg: 9px;
            --radius-md: 8px;
            --sidebar-width: 230px;
            --sidebar-collapsed-width: 72px;
            --content-width: 1380px;
        }

        body { font-size: 15px; line-height: 1.5; }
        .admin-sidebar { padding: 16px 12px; background: #fff; }
        .sidebar-panel { min-height: 0; }
        .sidebar-brand-row { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; }
        .sidebar-brand { min-width: 0; gap: 10px; margin: 0; }
        .sidebar-brand-logo {
            width: 38px;
            height: 38px;
            flex: none;
            object-fit: contain;
            padding: 2px;
            border: 1px solid var(--line);
            border-radius: 50%;
            background: #fff;
        }
        .sidebar-brand-text { min-width: 0; }
        .sidebar-brand-text strong { font-size: 14px; line-height: 1.25; }
        .sidebar-brand-text small { margin-bottom: 2px; font-size: 9px; line-height: 1.3; }
        .sidebar-collapse-btn { width: 28px; height: 28px; flex: none; margin: 4px 0 0; border-radius: 7px; font-size: 17px; }
        .sidebar-nav { flex: 1 1 auto; gap: 3px; min-height: 0; margin-top: 22px; overflow-y: auto; scrollbar-width: thin; }
        .sidebar-link {
            position: relative;
            min-height: 42px;
            padding: 7px 9px;
            border: 1px solid transparent;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 600;
        }
        .sidebar-link:hover { background: #f3f6f8; }
        .sidebar-link > span:last-child { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-link.active {
            border-color: #d4e1eb;
            background: #edf4f9;
            box-shadow: inset 3px 0 0 var(--brand-700);
        }
        .sidebar-link-icon {
            width: 27px;
            height: 27px;
            border: 0;
            border-radius: 7px;
            background: #f1f4f6;
            color: var(--ink-500);
            font-size: 9px;
            letter-spacing: -.02em;
        }
        .sidebar-link.active .sidebar-link-icon { background: var(--brand-900); color: #fff; }
        .sidebar-footer { flex: none; margin-top: auto; padding: 13px 4px 0; border-top: 1px solid var(--line); }
        .sidebar-user { display: flex; align-items: center; gap: 9px; min-width: 0; margin-bottom: 9px; }
        .sidebar-user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 32px;
            height: 32px;
            flex: none;
            border-radius: 50%;
            background: var(--brand-100);
            color: var(--brand-900);
            font-size: 12px;
            font-weight: 800;
        }
        .sidebar-user-copy { min-width: 0; }
        .sidebar-user-copy strong, .sidebar-user-copy span { display: block; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
        .sidebar-user-copy strong { color: var(--ink-900); font-size: 12px; }
        .sidebar-user-copy span { margin-top: 1px; color: var(--ink-500); font-size: 10px; }
        .sidebar-footer form { margin: 0; }
        .sidebar-logout {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 9px;
            width: 100%;
            min-height: 38px;
            padding: 7px 10px;
            border: 1px solid transparent;
            border-radius: 8px;
            background: transparent;
            color: #8f2f2f;
            font: inherit;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
        }
        .sidebar-logout:hover { border-color: #efd1d1; background: #fff5f5; color: #a52f2f; }
        .sidebar-logout-icon { display: inline-flex; align-items: center; justify-content: center; width: 22px; font-size: 16px; }

        .admin-main { padding: 0 20px 28px; }
        .admin-header { margin: 0 -20px 16px; border-bottom-width: 2px; box-shadow: 0 2px 8px rgba(22, 58, 95, .12); }
        .admin-header-inner { padding: 9px 20px; gap: 14px; }
        .admin-header-brand { gap: 11px; }
        .admin-header-brand img { width: 44px; height: 44px; padding: 3px; box-shadow: none; }
        .admin-header-copy small { margin-bottom: 2px; font-size: 10px; }
        .admin-header-copy strong { font-size: clamp(16px, 1.7vw, 20px); }
        .admin-header-copy span { margin-top: 2px; font-size: 11px; }
        .header-chip { min-height: 34px; padding: 7px 11px; font-size: 12px; }

        .topbar { gap: 14px; padding-bottom: 13px; margin-bottom: 15px; }
        .topbar-kicker { margin-bottom: 5px; font-size: 10px; }
        .topbar-title h1 { font-size: clamp(23px, 2.4vw, 29px); }
        .topbar-title p { margin-top: 4px; max-width: 820px; font-size: 13px; line-height: 1.45; }
        .topbar-actions { gap: 7px; }

        .btn, .btn-primary, .btn-ghost, .btn-danger, .btn-secondary {
            min-height: 38px;
            padding: 8px 13px;
            border-radius: 8px;
            gap: 6px;
            font-size: 13px;
        }
        .notification-trigger { width: 38px; min-width: 38px; height: 38px; }
        .notification-panel { width: min(360px, calc(100vw - 24px)); border-radius: 10px; }
        .notification-panel-head { padding: 11px 13px; }
        .notification-panel-head strong { font-size: 14px; }
        .notification-panel-head span { font-size: 12px; }
        .notification-list { max-height: 360px; }
        .notification-item-link { padding: 11px 8px 11px 13px; }
        .notification-item-title strong, .notification-item-copy { font-size: 13px; }
        .notification-item-time { font-size: 11px; }

        .content-grid { gap: 14px; }
        .split-layout, .case-layout, .case-stack { gap: 12px; }
        .panel, .card { padding: 16px; border-radius: 10px; box-shadow: var(--shadow); }
        .section-heading { margin-bottom: 12px; }
        .section-heading h2, .section-heading h3 { font-size: 18px; }
        .section-heading p { margin-top: 3px; font-size: 13px; line-height: 1.45; }
        .toolbar-row { gap: 10px; }
        .admin-strip, .stats-grid, .compact-grid { gap: 9px; }
        .admin-mini-card, .stat-card { padding: 13px; border-radius: 9px; }
        .admin-mini-card strong, .stat-card .label { margin-bottom: 4px; font-size: 11px; }
        .admin-mini-card span, .stat-card .value { font-size: 25px; }
        .stat-card.admin .value { font-size: 23px; }
        .stat-card .hint { margin-top: 3px; font-size: 12px; }

        .table-wrap { border: 1px solid var(--line); border-radius: 9px; }
        th, td { padding: 10px 11px; }
        th { font-size: 11px; letter-spacing: .045em; }
        td { font-size: 13px; }
        table.compact-table th, table.compact-table td { padding: 9px 10px; font-size: 13px; }
        table.compact-table th { font-size: 11px; }
        tbody tr:nth-child(even) { background: #fbfcfd; }
        tbody tr:hover { background: #f2f6f9; }
        .table-cell-meta, .table-note { font-size: 12px; }
        .badge, .status-badge { padding: 4px 8px; font-size: 11px; }

        .form-grid { gap: 12px; }
        .field { gap: 5px; }
        .field label { font-size: 13px; }
        .field input, .field select, .field textarea {
            min-height: 40px;
            padding: 8px 10px;
            border-radius: 8px;
            font-size: 14px;
        }
        .field textarea { min-height: 88px; }
        .field-hint, .error-text { font-size: 12px; }
        .form-actions { gap: 7px; margin-top: 5px; }
        .info-grid { grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 9px; }
        .info-item { padding: 11px 12px; border-radius: 8px; }
        .info-item .label { margin-bottom: 4px; font-size: 10px; }
        .info-item .value { font-size: 13px; line-height: 1.4; }
        .checkbox-grid { grid-template-columns: repeat(auto-fit, minmax(210px, 1fr)); gap: 9px; }
        .checkbox-card { min-height: 44px; padding: 10px 12px; gap: 9px; border-radius: 8px; }
        .checkbox-card input[type="checkbox"] { width: 17px; height: 17px; }
        .checkbox-card-title { font-size: 13px; }
        .timeline-list { gap: 7px; }
        .timeline-entry { padding: 10px 11px; border-radius: 8px; }
        .case-banner { gap: 10px; padding-bottom: 10px; margin-bottom: 12px; }
        .case-id { padding: 4px 7px; font-size: 11px; }
        .admin-panel-title { margin-bottom: 7px; font-size: 16px; }
        .alert { padding: 10px 13px; margin-bottom: 12px; border-radius: 8px; font-size: 13px; }
        .empty-state { padding: 24px 16px; font-size: 13px; }
        .mobile-menu-btn { width: 38px; height: 38px; margin-bottom: 7px; border-radius: 8px; font-size: 18px; }

        @media (min-width: 1101px) {
            .sidebar-collapsed .admin-sidebar { padding: 14px 9px; }
            .sidebar-collapsed .sidebar-brand-row { display: grid; justify-content: center; gap: 8px; }
            .sidebar-collapsed .sidebar-brand { justify-content: center; margin: 0; }
            .sidebar-collapsed .sidebar-brand-logo { width: 34px; height: 34px; }
            .sidebar-collapsed .sidebar-brand-text,
            .sidebar-collapsed .sidebar-user-copy,
            .sidebar-collapsed .sidebar-logout-label { display: none; }
            .sidebar-collapsed .sidebar-collapse-btn { margin: 0 auto; }
            .sidebar-collapsed .sidebar-nav { margin-top: 16px; }
            .sidebar-collapsed .sidebar-link { padding: 6px; }
            .sidebar-collapsed .sidebar-link-icon { width: 30px; height: 30px; }
            .sidebar-collapsed .sidebar-footer { padding: 11px 0 0; }
            .sidebar-collapsed .sidebar-user,
            .sidebar-collapsed .sidebar-logout { justify-content: center; }
            .sidebar-collapsed .sidebar-logout { padding: 7px; }
        }

        @media (max-width: 1100px) {
            .admin-main { padding: 0 14px 24px; }
            .admin-header { margin-left: -14px; margin-right: -14px; }
            .admin-header-inner { padding: 9px 14px; }
        }

        @media (max-width: 760px) {
            .admin-header-inner { flex-direction: row; align-items: center; }
            .admin-header-meta { display: none; }
            .admin-header-brand img { width: 38px; height: 38px; }
            .admin-header-copy small { display: none; }
            .admin-header-copy strong { font-size: 14px; white-space: normal; }
            .admin-header-copy span { font-size: 10px; }
            .topbar { display: block; border-radius: 0; }
            .topbar-title h1 { font-size: 22px; }
            .topbar-actions { margin-top: 10px; }
            .panel, .card { padding: 13px; border-radius: 9px; }
            th, td { padding: 9px; }
            .stats-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        }
    </style>
    @yield('head')
</head>

<body class="@yield('body-class')">
    <a href="#main-content" class="skip-link">Skip to main content</a>
    @php($admin = auth()->user())
    @php($wvsuLogo = asset('images/wvsu-logo.png'))
    <div class="admin-shell" id="adminShell">
        <aside class="admin-sidebar" id="adminSidebar" aria-label="Admin sidebar">
            <div class="sidebar-panel">
                <div class="sidebar-brand-row">
                    <div class="sidebar-brand">
                        <img class="sidebar-brand-logo" src="{{ $wvsuLogo }}" alt="West Visayas State University logo">
                        <div class="sidebar-brand-text">
                            <small>West Visayas State University</small>
                            <strong>MIS Administration</strong>
                        </div>
                    </div>
                    <button type="button" class="sidebar-collapse-btn" id="sidebarCollapseBtn" aria-controls="adminSidebar" aria-expanded="true" aria-label="Minimize sidebar" title="Minimize sidebar">
                        <span aria-hidden="true">&lsaquo;</span>
                    </button>
                </div>

                <nav class="sidebar-nav" aria-label="Admin navigation">
                    <a href="{{ route('admin.dashboard') }}" title="Dashboard" class="sidebar-link {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.dashboard') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">D</span>
                        <span>Dashboard</span>
                    </a>
                    <a href="{{ route('admin.requests.index') }}" title="Service Requests" class="sidebar-link {{ request()->routeIs('admin.requests.*') || request()->routeIs('admin.service-requests.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.requests.*') || request()->routeIs('admin.service-requests.*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">SR</span>
                        <span>Service Requests</span>
                    </a>
                    <a href="{{ route('admin.incident-reports.index') }}" title="Incident Reports" class="sidebar-link {{ request()->routeIs('admin.incident-reports.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.incident-reports.*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">IR</span>
                        <span>Incident Reports</span>
                    </a>
                    <a href="{{ route('admin.pm-checklists.index') }}" title="Preventive Maintenance" class="sidebar-link {{ request()->routeIs('admin.pm-checklists.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.pm-checklists.*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">PM</span>
                        <span>Preventive Maintenance</span>
                    </a>
                    <a href="{{ route('admin.messenger') }}" title="Messenger" class="sidebar-link {{ request()->routeIs('admin.messenger') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.messenger') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">M<span class="sidebar-icon-count" id="adminMessageCount" hidden>0</span></span>
                        <span>Messenger</span>
                    </a>
                    <a href="{{ route('admin.technicians.index') }}" title="Technicians" class="sidebar-link {{ request()->routeIs('admin.technicians.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.technicians.*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">T</span>
                        <span>Technicians</span>
                    </a>
                    <a href="{{ route('admin.users.index') }}" title="Users" class="sidebar-link {{ request()->routeIs('admin.users.*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.users.*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">U</span>
                        <span>Users</span>
                    </a>
                    <a href="{{ route('admin.settings') }}" title="Settings" class="sidebar-link {{ request()->routeIs('admin.settings*') ? 'active' : '' }}" aria-current="{{ request()->routeIs('admin.settings*') ? 'page' : 'false' }}">
                        <span class="sidebar-link-icon">S</span>
                        <span>Settings</span>
                    </a>
                </nav>

                <div class="sidebar-footer">
                    <div class="sidebar-user">
                        <span class="sidebar-user-avatar" aria-hidden="true">{{ mb_strtoupper(mb_substr($admin?->first_name ?? 'A', 0, 1)) }}</span>
                        <div class="sidebar-user-copy">
                            <strong>{{ $admin?->full_name ?? $admin?->first_name ?? 'Administrator' }}</strong>
                            <span>{{ ucfirst($admin?->role ?? 'admin') }} account</span>
                        </div>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="sidebar-logout" title="Log out">
                            <span class="sidebar-logout-icon" aria-hidden="true">&#8594;</span>
                            <span class="sidebar-logout-label">Log out</span>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        <button type="button" class="sidebar-backdrop" id="sidebarBackdrop" aria-label="Close admin navigation" tabindex="-1"></button>

        <main class="admin-main" id="main-content" tabindex="-1">
            <header class="admin-header">
                <div class="admin-header-inner">
                    <div class="admin-header-brand">
                        <img src="{{ $wvsuLogo }}" alt="West Visayas State University logo">
                        <div class="admin-header-copy">
                            <small>Republic of the Philippines</small>
                            <strong>West Visayas State University</strong>
                            <span>MIS Service Request System</span>
                        </div>
                    </div>
                    <div class="admin-header-meta">
                        <span class="header-chip">Admin Workspace</span>
                        <span class="header-chip">{{ now()->format('M d, Y') }}</span>
                    </div>
                </div>
            </header>
            <header class="topbar">
                <div class="topbar-title">
                    <button type="button" class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Open admin navigation" aria-controls="adminSidebar" aria-expanded="false">&#9776;</button>
                    <div class="topbar-kicker">Administration Workspace</div>
                    <h1>@yield('page-title', 'Admin Panel')</h1>
                    <p>@yield('page-subtitle', 'Manage requests, technicians, communications, and system settings from one place.')</p>
                </div>

                <div class="topbar-actions">
                    <div class="notification-shell">
                        <button type="button" class="btn-ghost notification-trigger" id="adminNotificationBtn" aria-label="Admin notifications" aria-controls="adminNotificationPanel" aria-expanded="false">
                            <span>🔔</span>
                            <span class="notification-count" id="adminNotificationCount" aria-live="polite" hidden>0</span>
                        </button>
                        <div class="notification-panel" id="adminNotificationPanel" aria-label="Admin notifications">
                            <div class="notification-panel-head">
                                <div>
                                    <strong>Notifications</strong>
                                    <span>Incoming request and module activity</span>
                                </div>
                                <button type="button" class="notification-clear" id="adminNotificationClear" hidden>Clear alerts</button>
                            </div>
                            <div class="notification-list" id="adminNotificationList" aria-live="polite">
                                <div class="notification-empty">No notifications right now.</div>
                            </div>
                            <a href="{{ route('admin.notifications.index') }}" class="notification-clear" style="display:block;padding:10px 14px;text-align:center;">View all notifications</a>
                        </div>
                    </div>
                    @yield('header-actions')
                </div>
            </header>

            @if(session('success'))
                <div class="alert alert-success" role="status">{{ session('success') }}</div>
            @endif

            @if(session('error'))
                <div class="alert alert-error" role="alert">{{ session('error') }}</div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning" role="alert">{{ session('warning') }}</div>
            @endif

            @if($errors->any())
                <div class="alert alert-error" role="alert">
                    Please review the highlighted fields and try again.
                </div>
            @endif

            <div class="content-grid">
                @yield('content')
            </div>
        </main>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const adminShell = document.getElementById('adminShell');
            const sidebar = document.getElementById('adminSidebar');
            const menuBtn = document.getElementById('mobileMenuBtn');
            const collapseBtn = document.getElementById('sidebarCollapseBtn');
            const sidebarBackdrop = document.getElementById('sidebarBackdrop');
            const notificationBtn = document.getElementById('adminNotificationBtn');
            const notificationPanel = document.getElementById('adminNotificationPanel');
            const notificationList = document.getElementById('adminNotificationList');
            const notificationCount = document.getElementById('adminNotificationCount');
            const notificationClear = document.getElementById('adminNotificationClear');
            let latestNotificationFeedRequest = 0;
            const adminMessageCount = document.getElementById('adminMessageCount');

            function setDesktopSidebarCollapsed(collapsed, remember = true) {
                if (!adminShell || !collapseBtn) return;

                adminShell.classList.toggle('sidebar-collapsed', collapsed);
                collapseBtn.setAttribute('aria-expanded', collapsed ? 'false' : 'true');
                collapseBtn.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
                collapseBtn.setAttribute('title', collapsed ? 'Expand sidebar' : 'Minimize sidebar');
                const icon = collapseBtn.querySelector('[aria-hidden="true"]');
                if (icon) icon.textContent = collapsed ? '›' : '‹';

                if (remember) {
                    try {
                        localStorage.setItem('mis-admin-sidebar-collapsed', collapsed ? '1' : '0');
                    } catch (error) {
                        // Storage may be unavailable in private browsing; the control still works.
                    }
                }
            }

            if (adminShell && collapseBtn) {
                let savedCollapsed = true;
                try {
                    const savedSidebarState = localStorage.getItem('mis-admin-sidebar-collapsed');
                    savedCollapsed = savedSidebarState === null ? true : savedSidebarState === '1';
                } catch (error) {
                    savedCollapsed = true;
                }
                setDesktopSidebarCollapsed(savedCollapsed, false);

                collapseBtn.addEventListener('click', function() {
                    setDesktopSidebarCollapsed(!adminShell.classList.contains('sidebar-collapsed'));
                });
            }

            function setSidebarState(open) {
                if (!sidebar || !menuBtn) return;

                sidebar.classList.toggle('open', open);
                sidebarBackdrop?.classList.toggle('open', open);
                menuBtn.setAttribute('aria-expanded', open ? 'true' : 'false');
                menuBtn.setAttribute('aria-label', open ? 'Close admin navigation' : 'Open admin navigation');
            }

            if (sidebar && menuBtn) {
                menuBtn.addEventListener('click', function() {
                    setSidebarState(!sidebar.classList.contains('open'));
                });

                sidebarBackdrop?.addEventListener('click', function() {
                    setSidebarState(false);
                    menuBtn.focus();
                });

                document.addEventListener('click', function(event) {
                    if (window.innerWidth > 1100) {
                        return;
                    }

                    if (!sidebar.contains(event.target) && !menuBtn.contains(event.target)) {
                        setSidebarState(false);
                    }
                });

                sidebar.querySelectorAll('a').forEach(function(link) {
                    link.addEventListener('click', function() {
                        if (window.innerWidth <= 1100) setSidebarState(false);
                    });
                });
            }

            function renderAdminNotifications(payload) {
                if (!notificationList || !notificationCount) {
                    return;
                }

                const notifications = payload.notifications || [];
                const unreadTotal = payload.total_unread || 0;
                const hasPersistentAlerts = notifications.some(function(item) { return Boolean(item.delete_url); });
                if (notificationClear) notificationClear.hidden = !hasPersistentAlerts;

                if (unreadTotal > 0) {
                    notificationCount.hidden = false;
                    notificationCount.textContent = unreadTotal > 9 ? '9+' : String(unreadTotal);
                } else {
                    notificationCount.hidden = true;
                    notificationCount.textContent = '0';
                }

                if (!notifications.length) {
                    notificationList.innerHTML = '<div class="notification-empty">No notifications right now.</div>';
                    return;
                }

                const escapeHtml = function(value) {
                    return String(value ?? '').replace(/[&<>'"]/g, function(character) {
                        return {'&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'}[character];
                    });
                };

                notificationList.innerHTML = notifications.map(function(item) {
                    const unreadClass = item.read ? '' : ' unread';
                    return `
                        <article class="notification-item${unreadClass}">
                            <a href="${escapeHtml(item.url || '#')}" data-read-url="${escapeHtml(item.read_url || '')}" class="notification-item-link">
                                <div class="notification-item-title">
                                    <strong>${escapeHtml(item.title || 'Notification')}</strong>
                                    <span class="notification-item-time">${escapeHtml(item.time || '')}</span>
                                </div>
                                <div class="notification-item-copy">${escapeHtml(item.message || '')}</div>
                            </a>
                            ${item.delete_url ? `<button type="button" class="notification-delete" data-delete-url="${escapeHtml(item.delete_url)}" aria-label="Delete ${escapeHtml(item.title || 'notification')}">Delete</button>` : ''}
                        </article>
                    `;
                }).join('');

                notificationList.querySelectorAll('.notification-item-link[data-read-url]').forEach(function(link) {
                    if (!link.dataset.readUrl) return;
                    link.addEventListener('click', function(event) {
                        if (!link.closest('.notification-item')?.classList.contains('unread')) return;
                        event.preventDefault();
                        fetch(link.dataset.readUrl, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                'Accept': 'application/json'
                            }
                        }).finally(function() { window.location.href = link.href; });
                    });
                });

                notificationList.querySelectorAll('[data-delete-url]').forEach(function(button) {
                    button.addEventListener('click', async function() {
                        button.disabled = true;
                        try {
                            const response = await fetch(button.dataset.deleteUrl, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                                    'Accept': 'application/json'
                                }
                            });
                            if (!response.ok) throw new Error('Unable to delete notification.');
                            loadAdminNotifications();
                        } catch (error) {
                            button.disabled = false;
                            window.alert('Unable to delete the notification. Please try again.');
                        }
                    });
                });
            }

            async function loadAdminNotifications() {
                if (!notificationBtn) {
                    return;
                }

                const requestNumber = ++latestNotificationFeedRequest;
                try {
                    const response = await fetch('{{ route('admin.notifications') }}', {
                        cache: 'no-store',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) {
                        return;
                    }

                    const payload = await response.json();
                    if (requestNumber === latestNotificationFeedRequest) renderAdminNotifications(payload);
                } catch (error) {
                    // Keep the topbar functional even if notifications fail to load.
                }
            }

            async function loadAdminMessageSummary() {
                if (!adminMessageCount || document.hidden) return;

                try {
                    const response = await fetch('{{ route('admin.messenger.unread-summary') }}', {
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });
                    if (!response.ok) return;

                    const payload = await response.json();
                    const unread = Number(payload.total_unread || 0);
                    adminMessageCount.hidden = unread < 1;
                    adminMessageCount.textContent = unread > 99 ? '99+' : String(unread);
                    adminMessageCount.closest('.sidebar-link')?.setAttribute(
                        'aria-label',
                        unread ? `Messenger, ${unread} unread message${unread === 1 ? '' : 's'}` : 'Messenger'
                    );
                    window.dispatchEvent(new CustomEvent('admin-messenger-summary', { detail: payload }));
                } catch (error) {
                    // The rest of the admin shell remains available if message status cannot load.
                }
            }

            if (notificationBtn && notificationPanel) {
                notificationBtn.addEventListener('click', function(event) {
                    event.stopPropagation();
                    notificationPanel.classList.toggle('open');
                    notificationBtn.setAttribute('aria-expanded', notificationPanel.classList.contains('open') ? 'true' : 'false');
                    if (notificationPanel.classList.contains('open')) loadAdminNotifications();
                });

                document.addEventListener('click', function(event) {
                    if (!notificationPanel.contains(event.target) && !notificationBtn.contains(event.target)) {
                        notificationPanel.classList.remove('open');
                        notificationBtn.setAttribute('aria-expanded', 'false');
                    }
                });

                loadAdminNotifications();
                window.setInterval(loadAdminNotifications, 20000);
            }

            loadAdminMessageSummary();
            window.setInterval(loadAdminMessageSummary, 6000);
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    loadAdminMessageSummary();
                    loadAdminNotifications();
                }
            });
            window.addEventListener('pageshow', function(event) {
                if (event.persisted) loadAdminNotifications();
            });

            notificationClear?.addEventListener('click', async function() {
                notificationClear.disabled = true;
                try {
                    const response = await fetch('{{ route('admin.notifications.clear') }}', {
                        method: 'DELETE',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                            'Accept': 'application/json'
                        }
                    });
                    if (!response.ok) throw new Error('Unable to clear notifications.');
                    loadAdminNotifications();
                } catch (error) {
                    window.alert('Unable to clear the notifications. Please try again.');
                } finally {
                    notificationClear.disabled = false;
                }
            });

            document.addEventListener('keydown', function(event) {
                if (event.key !== 'Escape') return;

                if (sidebar?.classList.contains('open')) {
                    setSidebarState(false);
                    menuBtn?.focus();
                }

                if (notificationPanel?.classList.contains('open')) {
                    notificationPanel.classList.remove('open');
                    notificationBtn?.setAttribute('aria-expanded', 'false');
                    notificationBtn?.focus();
                }
            });
        });
    </script>
    @include('partials.submission-feedback')
    @yield('scripts')
</body>

</html>
