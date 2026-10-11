const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = (process.env.ADMIN_QA_BASE_URL || 'http://127.0.0.1:8010').replace(/\/$/, '');
const username = process.env.ADMIN_QA_USERNAME;
const password = process.env.ADMIN_QA_PASSWORD;
const paths = [
    '/admin/dashboard',
    '/admin/requests',
    '/admin/messenger',
    '/admin/notifications',
    '/admin/users',
    '/admin/settings',
    '/admin/operations',
    '/admin/incident-reports',
    '/admin/pm-checklists',
];

(async () => {
    if (!username || !password) throw new Error('ADMIN_QA_USERNAME and ADMIN_QA_PASSWORD are required.');
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1440, height: 900 } });
    const page = await context.newPage();
    const results = [];

    try {
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await page.getByLabel(/Username or email/i).fill(username);
        await page.getByLabel('Password', { exact: true }).fill(password);
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForLoadState('networkidle');
        const firstDeviceButton = page.getByRole('button', { name: 'Trust This Device and Continue' });
        if (await firstDeviceButton.count()) {
            await firstDeviceButton.click();
            await page.waitForLoadState('networkidle');
        }
        const landing = page.url();

        if (!new URL(landing).pathname.startsWith('/admin/')) {
            throw new Error(`Admin login did not reach an admin page: ${landing}`);
        }

        for (const path of paths) {
            const response = await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
            const axe = await new AxeBuilder({ page }).analyze();
            results.push({
                path,
                status: response?.status(),
                url: page.url(),
                title: await page.title(),
                violations: axe.violations.map((violation) => ({ id: violation.id, impact: violation.impact })),
                incomplete: axe.incomplete.map((item) => item.id),
            });
        }

        console.log(JSON.stringify({ landing, results }, null, 2));
        if (results.some((result) => result.status !== 200 || result.violations.length > 0)) process.exitCode = 1;
    } finally {
        await browser.close();
    }
})().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
});
