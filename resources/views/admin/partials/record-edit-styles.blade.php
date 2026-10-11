<style>
    .admin-record-edit { width: 100%; max-width: 1380px; margin: 0 auto; padding: 0; overflow: hidden; }
    .admin-record-edit .incident-edit-form,
    .admin-record-edit .pm-edit-form { display: block; }
    .admin-record-edit .incident-edit-form .incident-sheet,
    .admin-record-edit .pm-edit-form .pm-sheet {
        display: grid;
        grid-template-columns: minmax(0, 1.15fr) minmax(0, .85fr);
        gap: 12px;
        width: 100%;
        max-width: none;
        margin: 0;
        padding: 14px;
        overflow: visible;
        border: 0;
        border-radius: 0;
        background: #f8fafb;
        box-shadow: none;
    }
    .admin-record-edit .incident-sheet-header,
    .admin-record-edit .pm-sheet-header {
        grid-column: 1 / -1;
        display: grid;
        grid-template-columns: 64px minmax(0, 1fr) auto;
        align-items: center;
        gap: 14px;
        margin: -14px -14px 2px;
        padding: 14px 18px;
        border-bottom: 1px solid var(--line, #d8dee8);
        background: #f8fafb;
    }
    .admin-record-edit .incident-sheet-logo,
    .admin-record-edit .pm-sheet-logo { padding: 0; border: 0; background: transparent; }
    .admin-record-edit .incident-sheet-logo img,
    .admin-record-edit .pm-sheet-logo img { width: 56px; height: 56px; object-fit: contain; }
    .admin-record-edit .incident-sheet-title,
    .admin-record-edit .pm-sheet-title { padding: 0; text-align: center; }
    .admin-record-edit .incident-sheet-title h3,
    .admin-record-edit .pm-sheet-title h3 { color: var(--ink-900, #14202b); font-size: 17px; letter-spacing: .035em; text-transform: uppercase; }
    .admin-record-edit .incident-sheet-title span,
    .admin-record-edit .pm-sheet-title span { margin: 0 0 3px; font-size: 11px; letter-spacing: .04em; }
    .admin-record-edit .incident-sheet-title p,
    .admin-record-edit .pm-sheet-title p { display: none; }
    .admin-record-edit .incident-sheet-meta,
    .admin-record-edit .pm-sheet-meta {
        display: block;
        grid-column: auto;
        padding: 7px 9px;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 7px;
        color: var(--ink-700, #334155);
        font-size: 11px;
        line-height: 1.45;
        text-align: right;
    }
    .admin-record-edit .incident-sheet-meta strong,
    .admin-record-edit .pm-sheet-meta strong { display: block; font-size: 11px; }
    .admin-record-edit .incident-sheet-meta span,
    .admin-record-edit .pm-sheet-meta span { display: block; margin: 0; font-size: 11px; }
    .admin-record-edit .incident-sheet-meta div,
    .admin-record-edit .pm-sheet-meta div { padding: 0; border: 0; }
    .admin-record-edit .incident-sheet-meta strong,
    .admin-record-edit .pm-sheet-meta strong { color: var(--ink-900, #14202b); text-transform: none; }
    .admin-record-edit .incident-edit-form .incident-form-section,
    .admin-record-edit .pm-edit-form .pm-form-section {
        grid-column: auto;
        min-width: 0;
        padding: 0;
        overflow: hidden;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 9px;
        background: #fff;
    }
    .admin-record-edit .incident-edit-form .incident-workflow,
    .admin-record-edit .incident-edit-form .incident-reporter,
    .admin-record-edit .incident-edit-form .incident-nature,
    .admin-record-edit .incident-edit-form .incident-details,
    .admin-record-edit .incident-edit-form .incident-mis-section,
    .admin-record-edit .incident-edit-form .incident-signoff,
    .admin-record-edit .pm-edit-form .pm-workflow,
    .admin-record-edit .pm-edit-form .pm-form-section:nth-child(3),
    .admin-record-edit .pm-edit-form .pm-form-section:nth-child(4),
    .admin-record-edit .pm-edit-form .pm-form-section:nth-child(5),
    .admin-record-edit .pm-edit-form .pm-form-section:nth-child(6),
    .admin-record-edit .pm-edit-form .pm-form-section:nth-child(7) { grid-column: 1 / -1; }
    .admin-record-edit .incident-section-heading,
    .admin-record-edit .pm-section-heading {
        display: block;
        margin: 0;
        padding: 8px 11px;
        border-bottom: 1px solid var(--line, #d8dee8);
        background: #f4f7f9;
    }
    .admin-record-edit .incident-section-heading h4,
    .admin-record-edit .pm-section-heading h4 {
        color: var(--ink-900, #14202b);
        font-size: 12px;
        font-weight: 750;
        letter-spacing: .05em;
        text-transform: uppercase;
    }
    .admin-record-edit .incident-section-heading p,
    .admin-record-edit .pm-section-heading p { margin-top: 3px; font-size: 11px; }
    .admin-record-edit .incident-section-tag,
    .admin-record-edit .pm-section-tag { display: none; }
    .admin-record-edit .incident-form-section > .incident-fields,
    .admin-record-edit .incident-form-section > .incident-nature-grid,
    .admin-record-edit .incident-form-section > .field,
    .admin-record-edit .pm-form-section > .pm-fields,
    .admin-record-edit .pm-form-section > .pm-equipment-grid,
    .admin-record-edit .pm-form-section > .pm-checklist-grid,
    .admin-record-edit .pm-form-section > .pm-findings-grid,
    .admin-record-edit .pm-form-section > .pm-signoff-grid { margin: 0; padding: 11px; }
    .admin-record-edit .incident-form-section > .incident-fields[style] { margin-top: 0 !important; padding-top: 0; }
    .admin-record-edit .incident-fields,
    .admin-record-edit .pm-fields { gap: 12px; }
    .admin-record-edit .incident-fields .field label,
    .admin-record-edit .pm-fields .field label { font-size: 12px; }
    .admin-record-edit .incident-sheet input:not([type="checkbox"]):not([type="radio"]),
    .admin-record-edit .incident-sheet select,
    .admin-record-edit .pm-sheet input:not([type="checkbox"]):not([type="radio"]),
    .admin-record-edit .pm-sheet select { min-height: 39px; padding: 8px 9px; font-size: 13px; }
    .admin-record-edit .incident-sheet textarea,
    .admin-record-edit .pm-sheet textarea { min-height: 100px; padding: 9px; font-size: 13px; }
    .admin-record-edit .incident-nature-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    .admin-record-edit .incident-nature-option { grid-template-columns: 17px minmax(0, 1fr); font-size: 12px; }
    .admin-record-edit .incident-nature-option span { grid-column: 2; text-align: left; }
    .admin-record-edit .pm-equipment-grid { grid-template-columns: 1fr; border: 0; border-radius: 0; gap: 8px; }
    .admin-record-edit .pm-equipment-group { padding: 9px; border: 1px solid var(--line, #d8dee8); border-radius: 8px; }
    .admin-record-edit .pm-equipment-group + .pm-equipment-group { border-left: 1px solid var(--line, #d8dee8); }
    .admin-record-edit .pm-equipment-title { justify-content: flex-start; font-size: 12px; }
    .admin-record-edit .pm-radio-row { justify-content: flex-start; }
    .admin-record-edit .pm-checklist-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 8px; border: 0; border-radius: 0; }
    .admin-record-edit .pm-check-column,
    .admin-record-edit .pm-check-column + .pm-check-column { padding: 9px; border: 1px solid var(--line, #d8dee8); border-radius: 8px; }
    .admin-record-edit .pm-check-option { font-size: 11px; }
    .admin-record-edit .pm-signoff-grid { gap: 10px; }
    .admin-record-edit-footer {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 12px;
        margin: 0 14px 14px;
        padding: 11px 12px;
        border: 1px solid var(--line, #d8dee8);
        border-radius: 9px;
        background: #f8fafb;
    }
    .admin-record-edit-footer p { margin: 0; color: var(--ink-500, #64748b); font-size: 11px; line-height: 1.45; }
    .admin-record-edit-footer > div { display: flex; flex: none; gap: 7px; }
    @media (max-width: 1100px) {
        .admin-record-edit .incident-edit-form .incident-sheet,
        .admin-record-edit .pm-edit-form .pm-sheet { grid-template-columns: 1fr; }
        .admin-record-edit .incident-edit-form .incident-form-section,
        .admin-record-edit .pm-edit-form .pm-form-section { grid-column: 1; }
        .admin-record-edit .pm-checklist-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); }
    }
    @media (max-width: 680px) {
        .admin-record-edit .incident-edit-form .incident-sheet,
        .admin-record-edit .pm-edit-form .pm-sheet { gap: 10px; padding: 10px; }
        .admin-record-edit .incident-sheet-header,
        .admin-record-edit .pm-sheet-header { grid-template-columns: 48px minmax(0, 1fr); gap: 10px; margin: -10px -10px 0; padding: 12px; }
        .admin-record-edit .incident-sheet-logo img,
        .admin-record-edit .pm-sheet-logo img { width: 44px; height: 44px; }
        .admin-record-edit .incident-sheet-title,
        .admin-record-edit .pm-sheet-title { text-align: left; }
        .admin-record-edit .incident-sheet-title h3,
        .admin-record-edit .pm-sheet-title h3 { font-size: 14px; }
        .admin-record-edit .incident-sheet-meta,
        .admin-record-edit .pm-sheet-meta { grid-column: 1 / -1; text-align: left; }
        .admin-record-edit .incident-nature-grid,
        .admin-record-edit .pm-checklist-grid,
        .admin-record-edit .pm-findings-grid,
        .admin-record-edit .pm-equipment-fields { grid-template-columns: 1fr; }
        .admin-record-edit-footer { align-items: stretch; flex-direction: column; margin: 0 10px 10px; }
        .admin-record-edit-footer > div { display: grid; grid-template-columns: 1fr 1fr; width: 100%; }
    }
</style>
