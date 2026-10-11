const { chromium } = require('playwright');

const baseUrl = (process.env.USABILITY_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 375, height: 812 } });
    const page = await context.newPage();
    const evidence = {};
    try {
        await page.goto(`${baseUrl}/register`, { waitUntil: 'networkidle' });
        evidence.registration = await page.evaluate(() => {
            const fields = Array.from(document.querySelectorAll('input, select'));
            return {
                fieldCount: fields.length,
                requiredCount: fields.filter((field) => field.required).length,
                unlabeled: fields.filter((field) => field.type !== 'hidden' && !(field.labels?.length || field.getAttribute('aria-label'))).map((field) => field.name),
                autocompleteMissing: fields.filter((field) => ['text', 'email', 'password'].includes(field.type) && !field.autocomplete).map((field) => field.name),
                departmentControl: document.querySelector('select[name="office"]')?.tagName || null,
                passwordGuidance: document.body.innerText.match(/.{0,50}(password|characters).{0,100}/ig)?.slice(0, 3) || [],
            };
        });
        await page.getByRole('button', { name: 'Create my account' }).click();
        evidence.registration.nativeValidation = await page.evaluate(() => ({
            invalidField: document.activeElement?.getAttribute('name'),
            validationMessage: document.activeElement?.validationMessage || '',
        }));

        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await page.getByLabel(/Username or email/i).fill('blackbox_requester_b');
        await page.getByLabel('Password', { exact: true }).fill('RequesterBPass123!');
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL('**/dashboard');
        await page.goto(`${baseUrl}/requests/create`, { waitUntil: 'networkidle' });
        evidence.requestForm = await page.evaluate(() => ({
            serviceChoices: document.querySelectorAll('input[name="services[]"]').length,
            visibleHeadings: Array.from(document.querySelectorAll('h1,h2,h3,legend')).map((node) => node.textContent.trim()).filter(Boolean),
            helpText: Array.from(document.querySelectorAll('.help-text,.form-help,small')).map((node) => node.textContent.trim()).filter(Boolean).slice(0, 10),
            draftActions: Array.from(document.querySelectorAll('button')).map((node) => node.textContent.trim()).filter((text) => /draft/i.test(text)),
        }));
        const marker = `Usability confirmation ${Date.now()}`;
        await page.getByLabel('Troubleshoot PC/Laptop').check();
        await page.getByLabel(/Description\/Details of Request/).fill(marker);
        await page.getByLabel('Property/Asset No.').fill(`USABILITY-${Date.now()}`);
        await page.getByRole('button', { name: 'Submit request' }).click();
        await page.waitForURL('**/dashboard');
        evidence.afterSubmission = await page.evaluate(() => ({
            url: location.href,
            alerts: Array.from(document.querySelectorAll('[role="alert"], .alert, .success-message, .flash-message')).map((node) => node.textContent.trim()).filter(Boolean),
            successTextMatches: (document.body.innerText.match(/request.{0,40}(submitted|created|success)/ig) || []).slice(0, 5),
        }));
        console.log(JSON.stringify({ result: 'completed', evidence }, null, 2));
    } finally {
        await browser.close();
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
