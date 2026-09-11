<style>
    .record-review-page .content-grid { width: 100%; max-width: 1720px; }
    .record-pdf-workspace { display: grid; grid-template-columns: minmax(0, 1fr) minmax(260px, 300px); gap: 12px; align-items: start; }
    .record-pdf-sidebar { position: sticky; top: 82px; grid-column: 2; grid-row: 1; display: grid; gap: 10px; }
    .record-pdf-card { padding: 13px; border: 1px solid var(--line); border-radius: 10px; background: #fff; box-shadow: var(--shadow); }
    .record-pdf-heading { display: flex; align-items: flex-start; justify-content: space-between; gap: 8px; padding-bottom: 9px; margin-bottom: 9px; border-bottom: 1px solid var(--line); }
    .record-pdf-heading h2, .record-pdf-heading h3 { margin: 0; color: var(--ink-900); font-size: 14px; line-height: 1.35; }
    .record-pdf-heading p { margin: 3px 0 0; color: var(--ink-500); font-size: 11px; line-height: 1.4; }
    .record-pdf-id { color: var(--ink-500); font-size: 10px; font-weight: 750; letter-spacing: .04em; text-transform: uppercase; }
    .record-pdf-owner { margin: 5px 0 0; color: var(--ink-900); font-size: 14px; font-weight: 750; line-height: 1.35; }
    .record-pdf-meta { margin-top: 3px; color: var(--ink-500); font-size: 11px; line-height: 1.45; overflow-wrap: anywhere; }
    .record-pdf-facts { display: grid; gap: 7px; margin: 0; }
    .record-pdf-fact { display: grid; grid-template-columns: minmax(82px, .7fr) minmax(0, 1fr); gap: 8px; padding-bottom: 7px; border-bottom: 1px solid #edf1f4; font-size: 11px; line-height: 1.4; }
    .record-pdf-fact:last-child { padding-bottom: 0; border-bottom: 0; }
    .record-pdf-fact dt { color: var(--ink-500); font-weight: 700; }
    .record-pdf-fact dd { min-width: 0; margin: 0; color: var(--ink-800); text-align: right; overflow-wrap: anywhere; }
    .record-pdf-fact a { color: var(--brand-800); font-weight: 700; }
    .record-pdf-tags { display: flex; flex-wrap: wrap; gap: 5px; }
    .record-pdf-tag { padding: 4px 6px; border-radius: 999px; background: var(--brand-100); color: var(--brand-900); font-size: 10px; font-weight: 700; line-height: 1.25; }
    .record-pdf-actions { display: grid; grid-template-columns: 1fr; gap: 7px; }
    .record-pdf-actions .btn-primary, .record-pdf-actions .btn-ghost, .record-pdf-actions .btn-danger { width: 100%; }
    .record-pdf-preview { position: relative; grid-column: 1; grid-row: 1; min-width: 0; overflow: hidden; border: 1px solid #b8c2cc; border-radius: 10px; background: #525b65; box-shadow: 0 10px 26px rgba(15, 23, 42, .12); }
    .record-pdf-preview-head { display: flex; align-items: center; justify-content: space-between; gap: 10px; min-height: 42px; padding: 7px 11px; border-bottom: 1px solid var(--line); background: #fff; }
    .record-pdf-preview-head strong { color: var(--ink-900); font-size: 13px; }
    .record-pdf-preview-tools { display: flex; align-items: center; gap: 6px; }
    .record-pdf-preview-tools a { padding: 5px 8px; border: 1px solid var(--line); border-radius: 6px; color: var(--ink-700); background: #fff; font-size: 11px; font-weight: 700; text-decoration: none; }
    .record-pdf-preview-tools a:hover { border-color: var(--brand-700); color: var(--brand-900); }
    .record-pdf-frame { display: block; width: 100%; height: calc(100vh - 178px); min-height: 700px; border: 0; background: #525b65; opacity: .18; transition: opacity .18s ease; }
    .record-pdf-frame.is-ready { opacity: 1; }
    .record-pdf-loading { position: absolute; z-index: 2; top: 58px; left: 50%; transform: translateX(-50%); padding: 8px 11px; border-radius: 7px; color: var(--ink-700); background: rgba(255,255,255,.96); box-shadow: 0 4px 16px rgba(15,23,42,.18); font-size: 11px; font-weight: 700; pointer-events: none; }
    .record-pdf-loading[hidden] { display: none; }
    .record-pdf-fallback { padding: 10px; color: var(--ink-700); background: #fff; font-size: 12px; text-align: center; }
    .record-pdf-fallback a { color: var(--brand-900); font-weight: 750; text-decoration: underline; }
    @media (max-width: 1050px) {
        .record-pdf-workspace { grid-template-columns: 1fr; }
        .record-pdf-sidebar { position: static; grid-column: 1; grid-row: 1; grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .record-pdf-sidebar > :first-child { grid-column: 1 / -1; }
        .record-pdf-preview { grid-column: 1; grid-row: 2; }
        .record-pdf-frame { height: 76vh; min-height: 620px; }
    }
    @media (max-width: 700px) {
        .record-pdf-sidebar { grid-template-columns: 1fr; }
        .record-pdf-sidebar > :first-child { grid-column: auto; }
        .record-pdf-preview-head { align-items: flex-start; flex-direction: column; }
        .record-pdf-frame { height: 72vh; min-height: 500px; }
    }
    @media (max-width: 390px) {
        .record-pdf-frame { min-height: 430px; }
        .record-pdf-preview-tools { width: 100%; }
        .record-pdf-preview-tools a { flex: 1; text-align: center; }
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        document.querySelectorAll('.record-pdf-preview').forEach(function (preview) {
            var frame = preview.querySelector('.record-pdf-frame');
            var loading = preview.querySelector('.record-pdf-loading');
            if (!frame) return;
            frame.addEventListener('load', function () {
                frame.classList.add('is-ready');
                if (loading) loading.hidden = true;
            }, { once: true });
        });
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
