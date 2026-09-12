<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Service Request Details - MIS Service Request System</title>
    @php
    $user = auth()->user();
    $canEdit = $request->canBeEditedByUser();
    $canCancel = $request->canBeCancelledByUser();
    $statusGuidance = [
    'submitted' => 'Your request was received and is waiting for MIS review.',
    'pending' => 'MIS is reviewing the request and arranging the appropriate support.',
    'processing' => 'The assigned technician is currently working on this request.',
    'awaiting_requester_info' => 'MIS needs additional information before work can continue. Please answer the question below.',
    'awaiting_confirmation' => 'MIS marked the work complete. Please review the result and confirm whether the concern was resolved.',
    'completed' => 'The technical work is complete. You may submit service feedback below.',
    'closed' => 'This request is closed. You may reopen it if the same concern returns.',
    'reopened' => 'This request was reopened and returned to the active MIS queue.',
    'escalated' => 'This request needs additional MIS review.',
    'cancelled' => 'This request was cancelled and is no longer active.',
    'rejected' => 'MIS could not proceed with this request. Review the timeline for the reason.',
    ][$request->status] ?? 'The request status was updated.';
    $wvsuLogo = file_exists(public_path('images/wvsu-janiuay-logo.png'))
    ? asset('images/wvsu-janiuay-logo.png')
    : asset('images/wvsu-logo.png');
    @endphp
    <style>
        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            font-family: Arial, Helvetica, sans-serif;
            color: #111827;
            background: #f4f8fc;
            overflow-x: hidden;
        }

        .site-header {
            background: #1f6fa8;
            border-bottom: 4px solid #f4c430;
            color: #fff;
        }

        .header-inner {
            max-width: 1180px;
            margin: 0 auto;
            padding: 16px clamp(14px, 4vw, 20px);
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 18px;
            flex-wrap: wrap;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .brand img {
            width: 64px;
            height: 64px;
            object-fit: contain;
            background: #fff;
            border-radius: 50%;
            padding: 4px;
        }

        .brand small {
            display: block;
            font: 700 15px Georgia, "Times New Roman", serif;
        }

        .brand h1 {
            font: 700 clamp(20px, 3vw, 32px) Georgia, "Times New Roman", serif;
            text-transform: uppercase;
            line-height: 1.05;
        }

        .brand p {
            font: 700 16px Georgia, "Times New Roman", serif;
        }

        .top-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            justify-content: flex-end;
        }

        .button,
        button.button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 10px 14px;
            border: 1px solid #1f6fa8;
            border-radius: 3px;
            background: #1f6fa8;
            color: #fff;
            font: 700 14px Arial, sans-serif;
            text-decoration: none;
            cursor: pointer;
        }

        .button.secondary {
            background: #fff;
            color: #1f6fa8;
        }

        .button:hover {
            background: #164875;
        }

        .button.secondary:hover {
            background: #f0f0f0;
        }

        .page {
            max-width: 1180px;
            margin: 0 auto;
            padding: 22px clamp(14px, 4vw, 20px) 48px;
        }

        .nav-tabs {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
            margin-bottom: 18px;
        }

        .nav-shell {
            margin-bottom: 18px;
        }

        .nav-toggle {
            display: none;
            align-items: center;
            gap: 10px;
            min-height: 44px;
            padding: 10px 14px;
            border: 1px solid #cfd8e3;
            border-radius: 10px;
            background: #fff;
            color: #1f3b57;
            font-weight: 700;
            cursor: pointer;
            box-shadow: 0 2px 8px rgba(15, 23, 42, 0.06);
            margin-bottom: 12px;
        }

        .nav-toggle-icon {
            display: inline-grid;
            gap: 4px;
        }

        .nav-toggle-icon span {
            display: block;
            width: 18px;
            height: 2px;
            border-radius: 999px;
            background: #1f6fa8;
        }

        .nav-tabs a {
            padding: 8px 14px;
            border-radius: 3px;
            color: #2d123d;
            font-weight: 700;
            text-decoration: none;
            white-space: nowrap;
        }

        .nav-tabs a.active {
            background: #2f7fbd;
            color: #fff;
        }

        .nav-tabs a:hover {
            background: #e5e7eb;
        }

        .panel {
            padding: 22px;
            background: #fff;
            border: 1px solid #d7dce2;
            border-radius: 6px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, .12);
        }

        .section {
            margin-bottom: 28px;
        }

        .section h3 {
            margin-bottom: 16px;
            font-size: 16px;
            font-weight: 700;
            color: #111827;
            padding-bottom: 8px;
            border-bottom: 2px solid #e5e7eb;
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 16px;
        }

        .info-item {
            background: #f9fafb;
            padding: 14px;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
        }

        .info-label {
            font-size: 12px;
            font-weight: 600;
            color: #6b7280;
            text-transform: uppercase;
            margin-bottom: 6px;
            letter-spacing: 0.5px;
        }

        .info-value {
            font-size: 15px;
            font-weight: 500;
            color: #111827;
            word-break: break-word;
        }

        .status-badge {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 3px;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-submitted {
            background: #f1f5f9;
            color: #475569;
        }

        .status-processing {
            background: #dbeafe;
            color: #1d4ed8;
        }

        .status-awaiting_requester_info {
            background: #fef3c7;
            color: #92400e;
        }

        .status-awaiting_confirmation {
            background: #ede9fe;
            color: #6d28d9;
        }

        .status-closed {
            background: #dcfce7;
            color: #166534;
        }

        .status-reopened {
            background: #ffedd5;
            color: #c2410c;
        }

        .status-escalated {
            background: #fee2e2;
            color: #b91c1c;
        }

        .status-completed {
            background: #d1fae5;
            color: #065f46;
        }

        .status-rejected {
            background: #fee2e2;
            color: #7f1d1d;
        }

        .status-cancelled {
            background: #f3f4f6;
            color: #374151;
        }

        .section-header {
            background: #f3f4f6;
            padding: 16px 22px;
            margin: -22px -22px 16px -22px;
            border-radius: 6px 6px 0 0;
        }

        .header-content {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            flex-wrap: wrap;
        }

        .header-content h1 {
            font-size: 22px;
            font-weight: 700;
            margin: 0;
        }

        .header-content small {
            display: block;
            color: #6b7280;
            font-size: 13px;
            margin: 4px 0 0;
        }

        .description-box {
            background: #f9fafb;
            padding: 14px;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
            white-space: pre-wrap;
            word-wrap: break-word;
            line-height: 1.6;
        }

        .services-list {
            background: #f9fafb;
            padding: 14px;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
        }

        .services-list ul {
            list-style: none;
            padding: 0;
        }

        .services-list li {
            padding: 6px 0;
            color: #374151;
        }

        .services-list li:before {
            content: "✓ ";
            color: #1f6fa8;
            font-weight: bold;
            margin-right: 8px;
        }

        .attachment-section {
            background: #f9fafb;
            padding: 14px;
            border-radius: 4px;
            border-left: 3px solid #1f6fa8;
        }

        .attachment-link {
            display: inline-block;
            margin-top: 8px;
            padding: 8px 12px;
            background: #1f6fa8;
            color: #fff;
            text-decoration: none;
            border-radius: 3px;
            font-size: 14px;
            font-weight: 600;
        }

        .attachment-link:hover {
            background: #164875;
        }

        .action-buttons {
            display: flex;
            gap: 12px;
            margin-top: 28px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn {
            padding: 10px 24px;
            border: none;
            border-radius: 4px;
            font-weight: 700;
            font-size: 14px;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .btn-primary {
            background: #1f6fa8;
            color: #fff;
        }

        .btn-primary:hover {
            background: #164875;
        }

        .btn-secondary {
            background: #fff;
            color: #1f6fa8;
            border: 1px solid #1f6fa8;
        }

        .btn-secondary:hover {
            background: #f0f0f0;
        }

        .two-col {
            grid-template-columns: repeat(2, 1fr);
        }

        .three-col {
            grid-template-columns: repeat(3, 1fr);
        }

        .four-col {
            grid-template-columns: repeat(4, 1fr);
        }

        small {
            display: block;
            color: #6b7280;
            margin-top: 4px;
            font-size: 12px;
        }

        .control-number {
            font-family: 'Courier New', monospace;
            font-size: 16px;
            font-weight: 700;
            color: #1f6fa8;
            background: #f3f4f6;
            padding: 8px 12px;
            border-radius: 3px;
            display: inline-block;
        }

        @media (max-width: 1024px) {
            .panel {
                padding: 20px;
            }
        }

        @media (max-width: 860px) {
            .header-inner {
                flex-direction: column;
                align-items: flex-start;
            }

            .top-actions {
                width: 100%;
                justify-content: flex-start;
            }

            .nav-toggle {
                display: inline-flex;
            }

            .nav-tabs {
                display: none;
                flex-direction: column;
                gap: 6px;
                padding: 12px;
                border: 1px solid #d7dce2;
                border-radius: 12px;
                background: #fff;
                box-shadow: 0 10px 24px rgba(15, 23, 42, 0.08);
                margin-bottom: 0;
            }

            .nav-tabs.open {
                display: flex;
            }

            .nav-tabs a {
                width: 100%;
            }

            .header-content {
                flex-direction: column;
                align-items: flex-start;
            }

            .info-grid,
            .two-col,
            .three-col,
            .four-col {
                grid-template-columns: 1fr;
            }

            .action-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }

        @media (max-width: 560px) {
            .brand {
                align-items: flex-start;
                width: 100%;
            }

            .brand img {
                width: 56px;
                height: 56px;
            }

            .top-actions form,
            .top-actions .button,
            .top-actions button.button {
                width: 100%;
            }

            .panel {
                padding: 18px;
            }

            .section-header {
                padding: 14px 18px;
                margin: -18px -18px 16px -18px;
            }

            .control-number {
                width: 100%;
                text-align: center;
            }
        }

        .tracking-grid {
            display: grid;
            grid-template-columns: minmax(0, .9fr) minmax(0, 1.1fr);
            gap: 16px;
        }

        .tracking-card {
            padding: 16px;
            border: 1px solid #dce5ea;
            border-radius: 8px;
            background: #fbfdfe;
        }

        .tracking-card h4 {
            margin: 0 0 12px;
            color: #294152;
            font-size: 14px;
        }

        .tracking-detail {
            margin-top: 9px;
        }

        .tracking-detail span {
            display: block;
            color: #71808b;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: .05em;
            text-transform: uppercase;
        }

        .tracking-detail strong {
            margin-top: 3px;
            display: block;
            color: #263f4f;
            font-size: 13px;
            overflow-wrap: anywhere;
        }

        .request-timeline {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .request-timeline li {
            position: relative;
            padding: 0 0 16px 25px;
            color: #526570;
            font-size: 12px;
            line-height: 1.45;
        }

        .request-timeline li:last-child {
            padding-bottom: 0;
        }

        .request-timeline li::before {
            content: '';
            position: absolute;
            top: 3px;
            left: 2px;
            width: 10px;
            height: 10px;
            border: 3px solid #dceef8;
            border-radius: 50%;
            background: #236d9e;
        }

        .request-timeline li::after {
            content: '';
            position: absolute;
            top: 16px;
            bottom: 0;
            left: 8px;
            width: 1px;
            background: #cddce5;
        }

        .request-timeline li:last-child::after {
            display: none;
        }

        .request-timeline strong {
            display: block;
            color: #294152;
            font-size: 13px;
        }

        .request-timeline time {
            display: block;
            margin-top: 2px;
            color: #71808b;
            font-size: 10px;
        }

        .feedback-form {
            max-width: 720px;
        }

        .feedback-row {
            margin-top: 13px;
        }

        .feedback-row>label {
            margin-bottom: 6px;
            display: block;
            color: #344d5c;
            font-size: 12px;
            font-weight: 700;
        }

        .rating-options,
        .resolved-options {
            display: flex;
            flex-wrap: wrap;
            gap: 7px;
        }

        .rating-options label,
        .resolved-options label {
            padding: 8px 11px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            border: 1px solid #d8e2e8;
            border-radius: 6px;
            background: #fff;
            color: #405763;
            font-size: 12px;
            cursor: pointer;
        }

        .feedback-form textarea {
            width: 100%;
            min-height: 100px;
            padding: 10px 11px;
            resize: vertical;
            border: 1px solid #ccd9e1;
            border-radius: 6px;
            font: inherit;
        }

        .feedback-summary {
            padding: 13px;
            border-left: 3px solid #236d9e;
            background: #f2f8fb;
            color: #405763;
            font-size: 13px;
            line-height: 1.5;
        }

        .workflow-notice {
            margin-bottom: 16px;
            padding: 14px 16px;
            border: 1px solid #d7e3ea;
            border-left: 4px solid #236d9e;
            border-radius: 7px;
            background: #f8fbfd;
        }

        .workflow-notice strong {
            display: block;
            color: #294152;
            font-size: 14px;
        }

        .workflow-notice p {
            margin: 4px 0 0;
            color: #526570;
            font-size: 13px;
        }

        .workflow-meta {
            margin-top: 8px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .workflow-meta span {
            padding: 4px 8px;
            border-radius: 999px;
            background: #eaf2f7;
            color: #405763;
            font-size: 11px;
            font-weight: 700;
        }

        .client-update-box {
            margin-top: 10px;
            padding: 11px 12px;
            border-radius: 6px;
            background: #fff;
            color: #405763;
            font-size: 13px;
        }

        .clarification-list {
            display: grid;
            gap: 10px;
        }

        .clarification-card {
            padding: 13px;
            border: 1px solid #dce5ea;
            border-radius: 7px;
            background: #fbfdfe;
        }

        .clarification-card strong {
            display: block;
            color: #294152;
            font-size: 13px;
        }

        .clarification-card p {
            margin: 6px 0 0;
            color: #526570;
            font-size: 13px;
        }

        .clarification-card small {
            margin-top: 6px;
            display: block;
            color: #71808b;
            font-size: 10px;
        }

        .inline-response textarea {
            width: 100%;
            min-height: 86px;
            margin-top: 10px;
            padding: 10px;
            border: 1px solid #ccd9e1;
            border-radius: 6px;
            resize: vertical;
            font: inherit;
        }

        .schedule-card {
            padding: 14px;
            border: 1px solid #cfe0ea;
            border-radius: 7px;
            background: #f8fbfd;
        }

        .schedule-card textarea {
            width: 100%;
            min-height: 82px;
            padding: 10px;
            border: 1px solid #ccd9e1;
            border-radius: 6px;
            resize: vertical;
            font: inherit;
        }

        @media (max-width: 760px) {
            .tracking-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @include('user.partials.theme')
    <style>
        .request-header-actions {
            max-width: 100%;
            display: flex;
            align-items: center;
            justify-content: flex-end;
            flex-wrap: wrap;
            gap: 6px;
        }

        .request-header-actions .button,
        .request-header-actions button.button {
            width: auto !important;
            min-width: 0;
            min-height: 38px;
            padding: 7px 10px !important;
            display: inline-flex !important;
            align-items: center;
            justify-content: center;
            border-radius: 6px !important;
            font-size: 12px !important;
            line-height: 1.2;
            white-space: nowrap !important;
        }

        @media (max-width: 700px) {
            .section-header .header-content {
                width: 100%;
                gap: 11px !important;
            }

            .section-header .header-content>div:first-child {
                width: 100%;
            }

            .section-header .header-content h1 {
                font-size: 19px !important;
            }

            .section-header .header-content small {
                font-size: 11px !important;
            }

            .request-header-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                gap: 6px;
            }

            .request-header-actions .button,
            .request-header-actions button.button {
                width: 100% !important;
                min-height: 40px;
                padding: 7px 8px !important;
                white-space: normal !important;
                text-align: center;
            }
        }

        @media (max-width: 360px) {

            .request-header-actions .button,
            .request-header-actions button.button {
                font-size: 11px !important;
            }
        }

        .request-details-scroll-hint {
            display: none;
        }

        @media (max-width: 1024px) {
            .request-details-scroll-hint {
                margin: 0 0 7px;
                display: block;
                color: var(--client-ink-500);
                font-size: 10px;
            }

            .request-details-scroll {
                max-height: min(64vh, 690px);
                padding: 2px 7px 2px 2px;
                overflow-y: auto;
                overscroll-behavior: contain;
                scrollbar-gutter: stable;
                scroll-behavior: smooth;
            }

            .request-details-scroll:focus-visible {
                outline: 3px solid rgba(35, 109, 158, .25);
                outline-offset: 2px;
            }

            .request-details-panel .section {
                margin-bottom: 15px !important;
            }

            .request-details-panel .section>h3 {
                margin-bottom: 9px !important;
                padding-bottom: 6px !important;
                font-size: 14px !important;
            }

            .request-details-panel .info-grid,
            .request-details-panel .info-grid.two-col,
            .request-details-panel .info-grid.three-col,
            .request-details-panel .info-grid.four-col {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 8px !important;
            }

            .request-details-panel .info-item {
                padding: 10px 11px !important;
            }

            .request-details-panel .tracking-grid {
                grid-template-columns: repeat(2, minmax(0, 1fr)) !important;
                gap: 9px;
            }

            .request-details-panel .tracking-card {
                padding: 11px;
            }

            .request-details-panel .request-timeline,
            .request-details-panel .clarification-list,
            .request-details-panel .services-list,
            .request-details-panel .description-box {
                max-height: 210px;
                overflow-y: auto;
                overscroll-behavior: contain;
            }

            .request-details-panel .request-timeline {
                padding-right: 4px;
            }

            .request-details-panel .services-list,
            .request-details-panel .description-box {
                max-height: 150px;
            }
        }

        @media (max-width: 700px) {
            .request-details-scroll {
                max-height: min(58vh, 520px);
                padding-right: 5px;
            }

            .request-details-panel .section {
                margin-bottom: 12px !important;
            }

            .request-details-panel .tracking-grid {
                grid-template-columns: 1fr !important;
            }

            .request-details-panel .tracking-card {
                padding: 10px;
            }

            .request-details-panel .tracking-card:last-child {
                max-height: 230px;
                overflow-y: auto;
            }

            .request-details-panel .info-label {
                font-size: 9px !important;
            }

            .request-details-panel .info-value {
                font-size: 12px !important;
            }

            .request-details-panel .control-number {
                width: auto !important;
                padding: 5px 7px;
                font-size: 12px;
                text-align: left;
            }
        }

        @media (max-width: 380px) {

            .request-details-panel .info-grid,
            .request-details-panel .info-grid.two-col,
            .request-details-panel .info-grid.three-col,
            .request-details-panel .info-grid.four-col {
                grid-template-columns: 1fr !important;
            }

            .request-details-panel {
                padding: 11px !important;
            }

            .request-details-panel .section-header {
                padding: 11px !important;
            }

            .request-details-panel .section-header h1 {
                font-size: 17px !important;
            }

            .request-details-panel .section-header small {
                font-size: 10px !important;
            }

            .request-header-actions {
                gap: 5px;
            }

            .request-header-actions .button,
            .request-header-actions button.button {
                min-height: 38px;
                padding: 6px !important;
                font-size: 10.5px !important;
            }

            .request-details-scroll {
                max-height: 54vh;
                padding-right: 3px;
            }

            .request-details-panel .workflow-notice {
                padding: 11px;
            }

            .request-details-panel .workflow-meta {
                gap: 5px;
            }

            .request-details-panel .workflow-meta span {
                padding: 4px 7px;
                font-size: 9px;
            }

            .request-navigation-actions {
                width: 100%;
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
            }

            .request-footer-actions .request-navigation-actions .btn {
                width: 100% !important;
                min-height: 38px;
                padding: 7px 8px;
                font-size: 11px;
            }

            .request-footer-actions .request-navigation-actions .home-link:last-child {
                grid-column: span 1;
            }
        }

        .request-footer-actions {
            margin-top: 24px;
            padding-top: 18px;
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            flex-wrap: wrap;
            gap: 10px 16px;
            border-top: 1px solid var(--client-line);
        }

        .request-navigation-actions {
            display: flex;
            align-items: center;
            flex-wrap: wrap;
            gap: 7px;
            flex: 0 1 auto;
        }

        .request-footer-actions .btn {
            width: auto !important;
            min-width: 0;
            min-height: 40px;
            padding: 8px 13px;
            border-radius: 6px;
            font-size: 13px;
        }

        .request-footer-actions .home-link {
            color: var(--client-ink-700) !important;
            border-color: var(--client-line-strong) !important;
        }

        .cancel-request-card {
            width: fit-content;
            max-width: 100%;
            padding: 11px 12px;
            display: grid;
            grid-template-columns: minmax(220px, 320px) auto;
            align-items: end;
            gap: 7px 9px;
            border: 1px solid #ead4d4;
            border-radius: 7px;
            background: #fffafa;
            flex: 0 1 auto;
        }

        .cancel-request-card label {
            grid-column: 1 / -1;
            margin: 0 !important;
            color: #754646 !important;
            font-size: 11px !important;
        }

        .cancel-request-card textarea {
            width: 100%;
            height: 42px;
            min-height: 42px !important;
            max-height: 90px;
            margin: 0 !important;
            padding: 9px 10px !important;
            resize: vertical;
            border: 1px solid #d7c2c2 !important;
            border-radius: 6px !important;
            background: #fff !important;
            font: inherit;
            font-size: 13px;
        }

        .cancel-request-card .cancel-button {
            color: #9f3030 !important;
            border-color: #d6a6a6 !important;
            background: #fff !important;
            white-space: nowrap;
        }

        .cancel-request-card .cancel-button:hover {
            border-color: #b95c5c !important;
            background: #fff1f1 !important;
        }

        @media (max-width: 560px) {
            .request-footer-actions {
                margin-top: 20px;
                padding-top: 15px;
                align-items: stretch;
            }

            .request-navigation-actions {
                gap: 6px;
            }

            .request-footer-actions .btn {
                flex: 0 1 auto;
                min-height: 40px;
                padding: 8px 11px;
            }

            .cancel-request-card {
                width: 100%;
                grid-template-columns: minmax(0, 1fr) auto;
                padding: 10px;
            }

            .cancel-request-card textarea {
                height: 40px;
                min-height: 40px !important;
            }
        }

        @media (max-width: 390px) {
            .cancel-request-card {
                grid-template-columns: 1fr;
            }

            .cancel-request-card .cancel-button {
                width: auto !important;
                justify-self: start;
            }
        }

        @media (max-width: 330px) {
            .cancel-request-card textarea {
                font-size: 12px;
            }
        }
    </style>
</head>

<body>
    @include('partials.header')

    <main class="page" id="mainContent" tabindex="-1">
        @include('user.partials.navigation')

        @if(session('success'))
        <div class="alert alert-success" style="margin-bottom:14px;">{{ session('success') }}</div>
        @endif
        @if($errors->any())
        <div class="alert alert-error" style="margin-bottom:14px;">
            <strong>Please review the feedback form.</strong>
            <ul style="margin:6px 0 0;padding-left:18px;">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
        @endif

        <div class="panel request-details-panel">
            <div class="section-header">
                <div class="header-content">
                    <div>
                        <h1>Service Request Details</h1>
                        <small>Request Information · West Visayas State University</small>
                    </div>
                    <div class="request-header-actions" aria-label="Request document actions">
                        <a href="{{ route('user.messenger', ['request' => $request->id]) }}" class="button secondary">Open Messages</a>
                        <a href="{{ route('request.pdf', $request->id) }}" target="_blank" rel="noopener" class="button" data-pdf-action>Print PDF</a>
                        <a href="{{ route('requests.index') }}" class="button secondary">&larr; Back to Requests</a>
                    </div>
                </div>
            </div>

            <p class="request-details-scroll-hint" id="requestDetailsScrollHint">Scroll inside the request information area to review more details.</p>
            <div class="request-details-scroll" tabindex="0" role="region" aria-label="Service request information" aria-describedby="requestDetailsScrollHint">
                <div class="workflow-notice" role="status">
                    <strong>{{ $request->status_label }}</strong>
                    <p>{{ $statusGuidance }}</p>
                    <div class="workflow-meta">
                        <span>{{ $request->responsibility_label }}</span>
                        @if($request->assignedTechnician)
                        <span>Assigned to {{ $request->assignedTechnician->full_name }}</span>
                        @endif
                        @if($request->status === 'awaiting_confirmation')
                        <span>Your confirmation is required</span>
                        @endif
                    </div>
                    @if($request->client_update)
                    <div class="client-update-box">
                        <strong>Latest MIS update</strong>
                        <div style="margin-top:4px;">{{ $request->client_update }}</div>
                        @if($request->client_update_at)<small>Published {{ $request->client_update_at->format('M d, Y g:i A') }}</small>@endif
                    </div>
                    @endif
                </div>

                <!-- Control Number & Status -->
                <div class="section">
                    <div class="info-grid four-col">
                        <div class="info-item">
                            <div class="info-label">Control Number</div>
                            <div class="control-number">{{ $request->control_number }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Status</div>
                            <div>
                                <span class="status-badge status-{{ strtolower($request->status ?? 'pending') }}">
                                    {{ $request->status_label }}
                                </span>
                            </div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Date Submitted</div>
                            <div class="info-value">{{ $request->date_of_request ? date('M d, Y', strtotime($request->date_of_request)) : 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <div class="section">
                    <h3>Request Tracking</h3>
                    <div class="tracking-grid">
                        <div class="tracking-card">
                            <h4>Assigned Support</h4>
                            @if($request->assignedTechnician)
                            <div class="tracking-detail"><span>Technician</span><strong>{{ $request->assignedTechnician->full_name }}</strong></div>
                            <div class="tracking-detail"><span>Role / Specialization</span><strong>{{ $request->assignedTechnician->job_assignment ?? $request->assignedTechnician->specialization ?? 'MIS Technical Support' }}</strong></div>
                            <div class="tracking-detail"><span>Assigned</span><strong>{{ $request->assigned_date?->format('M d, Y g:i A') ?? 'Recently assigned' }}</strong></div>
                            @else
                            <div class="feedback-summary">Your request is waiting for technician assignment. Assigned MIS staff will appear here.</div>
                            @endif
                        </div>
                        <div class="tracking-card">
                            <h4>Status Timeline</h4>
                            <ol class="request-timeline">
                                <li>
                                    <strong>Request submitted</strong>
                                    <span>Your service request was received by MIS.</span>
                                    <time>{{ $request->created_at?->format('M d, Y g:i A') }}</time>
                                </li>
                                @php
                                $linkedTimeline = $request->statusLogs->map(fn ($statusLog) => [
                                'title' => ucfirst(str_replace('_', ' ', $statusLog->status)),
                                'description' => $statusLog->remarks ?: 'The request status was updated.',
                                'created_at' => $statusLog->created_at,
                                'actor' => $statusLog->updatedBy?->full_name,
                                ])->merge($request->activityLogs->map(fn ($activity) => [
                                'title' => str_starts_with($activity->action, 'incident_') ? 'Incident report update' : 'Maintenance update',
                                'description' => str($activity->description)->replace('_', ' ')->ucfirst(),
                                'created_at' => $activity->created_at,
                                'actor' => $activity->actor?->full_name,
                                ]))->sortBy('created_at');
                                @endphp
                                @foreach($linkedTimeline as $timelineItem)
                                <li>
                                    <strong>{{ $timelineItem['title'] }}</strong>
                                    <span>{{ $timelineItem['description'] }}</span>
                                    <time>{{ $timelineItem['created_at']?->format('M d, Y g:i A') }}@if($timelineItem['actor']) &middot; {{ $timelineItem['actor'] }}@endif</time>
                                </li>
                                @endforeach
                            </ol>
                        </div>
                    </div>
                </div>

                @if($request->clarifications->isNotEmpty())
                <div class="section">
                    <h3>Information Requests</h3>
                    <div class="clarification-list">
                        @foreach($request->clarifications as $clarification)
                        <div class="clarification-card">
                            <strong>{{ $clarification->question }}</strong>
                            <small>Asked by MIS on {{ $clarification->created_at->format('M d, Y g:i A') }}</small>
                            @if($clarification->answered_at)
                            <p>{{ $clarification->answer }}</p>
                            <small>Your response was sent {{ $clarification->answered_at->format('M d, Y g:i A') }}</small>
                            @elseif($request->status === 'awaiting_requester_info')
                            <form class="inline-response" method="POST" action="{{ route('requests.clarifications.answer', [$request, $clarification]) }}" enctype="multipart/form-data">
                                @csrf
                                <label for="clarification_answer_{{ $clarification->id }}" class="info-label" style="display:block;margin-top:10px;">Your response</label>
                                <textarea id="clarification_answer_{{ $clarification->id }}" name="answer" maxlength="3000" required>{{ old('answer') }}</textarea>
                                <label for="clarification_files_{{ $clarification->id }}" class="info-label" style="display:block;margin-top:10px;">Supporting files (optional)</label>
                                <input id="clarification_files_{{ $clarification->id }}" type="file" name="attachments[]" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" multiple>
                                <small>Up to 3 files, maximum 10MB each.</small>
                                <button type="submit" class="button" style="margin-top:8px;">Send Response</button>
                            </form>
                            @endif
                            @foreach($clarification->attachments as $attachment)
                            <div style="margin-top:7px;"><a href="{{ route('requests.clarification-attachments.download', [$request, $attachment]) }}" class="attachment-link">Download {{ $attachment->original_name }}</a></div>
                            @endforeach
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @if($request->scheduled_visit_at)
                <div class="section">
                    <h3>Scheduled MIS Visit</h3>
                    <div class="schedule-card">
                        <strong>{{ $request->scheduled_visit_at->format('M d, Y g:i A') }}</strong>
                        <p style="margin:4px 0 0;color:#526570;font-size:13px;">{{ $request->scheduled_location }}</p>
                        @if(in_array($request->schedule_response, [null, 'pending'], true))
                        <form method="POST" action="{{ route('requests.schedule-response', $request) }}" style="margin-top:12px;">
                            @csrf
                            <div class="resolved-options">
                                <label><input type="radio" name="schedule_response" value="accepted" required> Accept schedule</label>
                                <label><input type="radio" name="schedule_response" value="reschedule_requested" required> Request another time</label>
                            </div>
                            <div class="feedback-row">
                                <label for="schedule_response_note">Note for MIS (required when requesting another time)</label>
                                <textarea id="schedule_response_note" name="schedule_response_note" maxlength="2000"></textarea>
                            </div>
                            <button type="submit" class="button">Send Schedule Response</button>
                        </form>
                        @else
                        <p style="margin:10px 0 0;color:#405763;font-size:13px;">Your response: <strong>{{ str($request->schedule_response)->replace('_', ' ')->title() }}</strong></p>
                        @if($request->schedule_response_note)<p style="margin:4px 0 0;color:#526570;font-size:13px;">{{ $request->schedule_response_note }}</p>@endif
                        @endif
                    </div>
                </div>
                @endif

                <!-- Requestor Information -->
                <div class="section">
                    <h3>Requestor Information</h3>
                    <div class="info-grid four-col">
                        <div class="info-item">
                            <div class="info-label">Name</div>
                            <div class="info-value">{{ $request->requester_name ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Designation</div>
                            <div class="info-value">{{ $request->requester_designation ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Office/Unit</div>
                            <div class="info-value">{{ $request->requester_office ?? $request->office ?? 'N/A' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Phone/Local No.</div>
                            <div class="info-value">{{ $request->phone ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Services Requested -->
                @if($request->services)
                <div class="section">
                    <h3>🔧 Services Requested</h3>
                    <div class="services-list">
                        <ul>
                            @php
                            $services = is_string($request->services) ? json_decode($request->services, true) : $request->services;
                            @endphp
                            @if(is_array($services) && count($services) > 0)
                            @foreach($services as $service)
                            <li>{{ $service }}</li>
                            @endforeach
                            @else
                            <li>No specific services selected</li>
                            @endif
                        </ul>
                    </div>
                </div>
                @endif

                <!-- Description -->
                @if($request->description)
                <div class="section">
                    <h3>Description and Details</h3>
                    <div class="description-box">{{ $request->description }}</div>
                </div>
                @endif

                <!-- Additional Information -->
                <div class="section">
                    <h3>Additional Information</h3>
                    <div class="info-grid two-col">
                        <div class="info-item">
                            <div class="info-label">Category</div>
                            <div class="info-value">{{ $request->category ?? 'General' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Property/Asset No.</div>
                            <div class="info-value">{{ $request->property_no ?? 'N/A' }}</div>
                        </div>
                    </div>
                </div>

                @if($request->attachments->isNotEmpty())
                <div class="section">
                    <h3>Attachments</h3>
                    <div class="attachment-section">
                        @foreach($request->attachments as $attachment)
                        <div style="margin-top:8px;display:flex;align-items:center;gap:8px;flex-wrap:wrap;">
                            <a href="{{ route('requests.attachments.download', [$request, $attachment]) }}" class="attachment-link">Download {{ $attachment->original_name }}</a>
                            @if($canEdit)
                            <form method="POST" action="{{ route('requests.attachments.destroy', [$request, $attachment]) }}" onsubmit="return confirm('Remove this attachment?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-secondary" style="padding:7px 9px;">Remove</button>
                            </form>
                            @endif
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                <!-- Attachment -->
                @if($request->attachment && $request->attachments->isEmpty())
                <div class="section">
                    <h3>Attachment</h3>
                    <div class="attachment-section">
                        <div class="info-label">Attached File</div>
                        <div style="margin-top: 8px;">
                            <a href="{{ route('requests.attachment', $request) }}" class="attachment-link">
                                Download Attachment
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                <div class="section">
                    <h3>Linked Records</h3>
                    <p style="margin:-5px 0 12px;color:#60717d;font-size:12px;line-height:1.5;">When an ICT incident is submitted from this request, the system prepares its preventive-maintenance checklist automatically for MIS staff.</p>
                    <div class="info-grid two-col">
                        <div class="info-item">
                            <div class="info-label">ICT Incident Reports</div>
                            <div class="info-value">
                                @forelse($request->incidentReports as $report)
                                <div><a href="{{ route('incident-reports.show', $report) }}">{{ $report->document_number }}</a> <small>{{ str($report->status)->replace('_', ' ')->title() }}</small></div>
                                @empty
                                <div>No linked incident reports yet.</div>
                                @endforelse
                            </div>
                            <small><a href="{{ route('incident-reports.create', ['service_request_id' => $request->id]) }}">Create linked incident report</a></small>
                        </div>
                        <div class="info-item">
                            <div class="info-label">PM Checklists</div>
                            <div class="info-value">
                                @forelse($request->preventiveMaintenanceChecklists as $checklist)
                                <div><a href="{{ route('pm-checklists.show', $checklist) }}">{{ $checklist->control_number }}</a> <small>{{ str($checklist->status)->replace('_', ' ')->title() }}</small></div>
                                @empty
                                <div>No linked PM checklists yet.</div>
                                @endforelse
                            </div>
                            <small>Preventive-maintenance checklists are separate records completed and updated by MIS.</small>
                        </div>
                    </div>
                </div>

                @if(in_array($request->status, ['completed', 'closed'], true))
                <div class="section">
                    <h3>Service Summary</h3>
                    <div class="info-grid four-col">
                        <div class="info-item">
                            <div class="info-label">Submitted</div>
                            <div class="info-value">{{ $request->created_at?->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Technician</div>
                            <div class="info-value">{{ $request->assignedTechnician?->full_name ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Work Completed</div>
                            <div class="info-value">{{ optional($request->work_completed_at ?? $request->date_completed)->format('M d, Y') ?? '-' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Closed</div>
                            <div class="info-value">{{ optional($request->closed_at)->format('M d, Y') ?? 'Awaiting confirmation' }}</div>
                        </div>
                        <div class="info-item">
                            <div class="info-label">Resolution Time</div>
                            <div class="info-value">{{ $request->closed_at ? $request->created_at->diffForHumans($request->closed_at, true) : '-' }}</div>
                        </div>
                    </div>
                    @if($request->comments)<div class="description-box" style="margin-top:12px;"><strong>Resolution:</strong> {{ $request->comments }}</div>@endif
                </div>
                @endif

                @if(in_array($request->status, ['awaiting_confirmation', 'completed', 'closed'], true))
                <div class="section">
                    <h3>{{ $request->status === 'awaiting_confirmation' ? 'Confirm Resolution' : 'Service Feedback' }}</h3>
                    @if($request->feedback_submitted_at)
                    <div class="feedback-summary">
                        <strong>{{ $request->feedback_rating }}/5 rating</strong> &middot; {{ $request->feedback_resolved ? 'Issue resolved' : 'Issue needs follow-up' }}
                        @if($request->feedback_comment)<div style="margin-top:6px;">{{ $request->feedback_comment }}</div>@endif
                        <small style="display:block;margin-top:6px;">Submitted {{ $request->feedback_submitted_at->format('M d, Y g:i A') }}</small>
                        @if($request->feedback_follow_up_status)
                        <small style="display:block;margin-top:6px;">MIS follow-up: {{ ucfirst($request->feedback_follow_up_status) }}@if($request->feedback_reviewed_at) &middot; Updated {{ $request->feedback_reviewed_at->format('M d, Y g:i A') }}@endif</small>
                        @endif
                    </div>
                    @if($request->status === 'closed' && $request->canBeReopenedByUser())
                    <form class="feedback-form" method="POST" action="{{ route('requests.reopen', $request) }}" style="margin-top:12px;">
                        @csrf
                        <p style="margin:0;color:#5b6d78;font-size:13px;">If the same issue returned, reopen this request for follow-up.</p>
                        <div class="feedback-row">
                            <label for="reopen_reason">Reason for reopening</label>
                            <textarea id="reopen_reason" name="reason" maxlength="2000" required></textarea>
                        </div>
                        <div class="feedback-row"><button type="submit" class="button secondary">Reopen Request</button></div>
                    </form>
                    @elseif($request->status === 'closed')
                    <p style="margin:12px 0 0;color:#5b6d78;font-size:13px;">The 14-day reopening period ended. Submit a new request if assistance is still needed.</p>
                    @endif
                    @else
                    <form class="feedback-form" method="POST" action="{{ route('requests.feedback', $request) }}">
                        @csrf
                        <p style="margin:0;color:#5b6d78;font-size:13px;">Confirm whether the service solved your concern. Selecting “Not yet” automatically reopens the request.</p>
                        <div class="feedback-row">
                            <label>Overall rating</label>
                            <div class="rating-options">
                                @foreach(range(1, 5) as $rating)
                                <label><input type="radio" name="feedback_rating" value="{{ $rating }}" required @checked(old('feedback_rating')==$rating)> {{ $rating }}</label>
                                @endforeach
                            </div>
                        </div>
                        <div class="feedback-row">
                            <label>Was your concern resolved?</label>
                            <div class="resolved-options">
                                <label><input type="radio" name="feedback_resolved" value="1" required @checked(old('feedback_resolved')==='1' )> Yes</label>
                                @if($request->canBeReopenedByUser())
                                <label><input type="radio" name="feedback_resolved" value="0" required @checked(old('feedback_resolved')==='0' )> Not yet</label>
                                @endif
                            </div>
                        </div>
                        <div class="feedback-row">
                            <label for="feedback_comment">Comment (optional)</label>
                            <textarea id="feedback_comment" name="feedback_comment" maxlength="2000" placeholder="What went well, or what still needs attention?">{{ old('feedback_comment') }}</textarea>
                        </div>
                        <div class="feedback-row"><button type="submit" class="button">Submit Feedback</button></div>
                    </form>
                    @endif
                </div>
                @endif

            </div>

            <!-- Request Actions -->
            <div class="request-footer-actions" aria-label="Request actions">
                <div class="request-navigation-actions">
                    @if($canEdit)
                    <a href="{{ route('requests.edit', $request) }}" class="btn btn-primary">Edit request</a>
                    @endif
                    <a href="{{ route('requests.index') }}" class="btn btn-secondary">&larr; Back to requests</a>
                    <a href="{{ route('dashboard') }}" class="btn btn-secondary home-link">Home</a>
                </div>
                @if($canCancel)
                <form class="cancel-request-card" method="POST" action="{{ route('requests.destroy', $request) }}" onsubmit="return confirm('Cancel this request?');">
                    @csrf
                    @method('DELETE')
                    <label for="cancellation_reason">Reason for cancellation</label>
                    <textarea id="cancellation_reason" name="cancellation_reason" maxlength="2000" rows="1" required placeholder="Briefly explain why">{{ old('cancellation_reason') }}</textarea>
                    <button type="submit" class="btn btn-secondary cancel-button">Cancel request</button>
                </form>
                @endif
            </div>
        </div>
    </main>
</body>

</html>