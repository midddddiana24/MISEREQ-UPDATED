<style>
    .mis-submit-feedback {
        position: fixed;
        z-index: 9999;
        right: 18px;
        bottom: 18px;
        width: min(330px, calc(100vw - 28px));
        min-height: 54px;
        padding: 12px 14px;
        display: flex;
        align-items: center;
        gap: 11px;
        color: #183447;
        border: 1px solid #bfd1dc;
        border-radius: 10px;
        background: rgba(255, 255, 255, .98);
        box-shadow: 0 12px 30px rgba(18, 50, 70, .18);
        font-family: "Segoe UI", Arial, sans-serif;
        font-size: 13px;
        font-weight: 700;
        line-height: 1.4;
        opacity: 0;
        visibility: hidden;
        transform: translateY(10px);
        transition: opacity .16s ease, transform .16s ease, visibility .16s ease;
    }

    .mis-submit-feedback.is-visible {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }

    .mis-submit-spinner,
    .mis-button-spinner {
        flex: 0 0 auto;
        border: 2px solid #c9d9e2;
        border-top-color: #174f75;
        border-radius: 50%;
        animation: mis-submit-spin .7s linear infinite;
    }

    .mis-submit-spinner { width: 20px; height: 20px; }
    .mis-button-spinner { width: 14px; height: 14px; display: inline-block; }

    button.is-submitting,
    input[type="submit"].is-submitting {
        cursor: wait !important;
        opacity: .78;
    }

    @keyframes mis-submit-spin { to { transform: rotate(360deg); } }

    @media (max-width: 480px) {
        .mis-submit-feedback { right: 10px; bottom: 10px; width: calc(100vw - 20px); }
    }

    @media (prefers-reduced-motion: reduce) {
        .mis-submit-feedback { transition: none; }
        .mis-submit-spinner,
        .mis-button-spinner { animation-duration: 1.4s; }
    }
</style>
<script data-mis-submit-feedback>
    (() => {
        if (window.__misSubmissionFeedbackInstalled) return;
        window.__misSubmissionFeedbackInstalled = true;

        const install = () => {
            const feedback = document.createElement('div');
            feedback.className = 'mis-submit-feedback';
            feedback.setAttribute('role', 'status');
            feedback.setAttribute('aria-live', 'polite');
            feedback.innerHTML = '<span class="mis-submit-spinner" aria-hidden="true"></span><span class="mis-submit-message">Processing your request...</span>';
            document.body.appendChild(feedback);

            const message = feedback.querySelector('.mis-submit-message');
            let resetTimer = null;

            const reset = () => {
                window.clearTimeout(resetTimer);
                feedback.classList.remove('is-visible');
                document.querySelectorAll('form[data-mis-submitting="true"]').forEach((form) => {
                    form.removeAttribute('data-mis-submitting');
                    form.removeAttribute('aria-busy');
                });
                document.querySelectorAll('[data-mis-original-content]').forEach((button) => {
                    if (button instanceof HTMLInputElement) {
                        button.value = button.dataset.misOriginalContent;
                    } else {
                        button.innerHTML = button.dataset.misOriginalContent;
                    }
                    button.disabled = false;
                    button.classList.remove('is-submitting');
                    button.removeAttribute('aria-busy');
                    button.removeAttribute('data-mis-original-content');
                });
            };

            document.addEventListener('submit', (event) => {
                const form = event.target;
                if (!(form instanceof HTMLFormElement)
                    || event.defaultPrevented
                    || form.dataset.noLoading !== undefined
                    || form.target === '_blank') {
                    return;
                }

                if (form.dataset.misSubmitting === 'true') {
                    event.preventDefault();
                    return;
                }

                form.dataset.misSubmitting = 'true';
                form.setAttribute('aria-busy', 'true');
                const submitter = event.submitter
                    || form.querySelector('button[type="submit"], input[type="submit"], button:not([type])');
                const uploading = form.enctype === 'multipart/form-data';
                const loadingLabel = form.dataset.loadingLabel
                    || submitter?.dataset.loadingLabel
                    || (uploading ? 'Uploading files...' : 'Saving changes...');

                if (submitter) {
                    const isInput = submitter instanceof HTMLInputElement;
                    submitter.dataset.misOriginalContent = isInput ? submitter.value : submitter.innerHTML;
                    if (isInput) {
                        submitter.value = loadingLabel;
                    } else {
                        submitter.innerHTML = '<span class="mis-button-spinner" aria-hidden="true"></span> ' + loadingLabel;
                    }
                    submitter.disabled = true;
                    submitter.classList.add('is-submitting');
                    submitter.setAttribute('aria-busy', 'true');
                }

                message.textContent = loadingLabel;
                feedback.classList.add('is-visible');
                resetTimer = window.setTimeout(reset, 120000);
            });

            window.addEventListener('pageshow', reset);
        };

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', install, { once: true });
        } else {
            install();
        }
    })();
</script>
