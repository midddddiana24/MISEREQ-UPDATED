const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = process.env.A11Y_BASE_URL || 'http://127.0.0.1:8000';
const username = process.env.A11Y_USERNAME;
const password = process.env.A11Y_PASSWORD;
const authenticatedPaths = [
    '/requests',
    '/requests/create',
    '/incident-reports',
    '/incident-reports/create',
    '/pm-checklists',
    '/user/messenger',
    '/user/settings',
    '/user/notifications',
    '/user/profile',
    '/user/security',
];

async function auditPage(page, path) {
    await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
    const result = await new AxeBuilder({ page }).analyze();

    return {
        path,
        url: page.url(),
        violations: result.violations.map((rule) => ({
            id: rule.id,
            impact: rule.impact,
            help: rule.help,
            nodes: rule.nodes.map((node) => ({
                target: node.target,
                html: node.html,
                failureSummary: node.failureSummary,
            })),
        })),
    };
}

async function main() {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext();
    const page = await context.newPage();
    const results = [];

    try {
        results.push(await auditPage(page, '/login'));

        if (!username || !password) {
            throw new Error('Set A11Y_USERNAME and A11Y_PASSWORD to run the authenticated page audit.');
        }

        await page.locator('input[name="username"]').fill(username);
        await page.locator('input[name="password"]').fill(password);
        await page.locator('input[name="password"]').press('Enter');
        await page.waitForLoadState('networkidle');

        for (const path of authenticatedPaths) {
            results.push(await auditPage(page, path));
        }

        console.log(JSON.stringify(results, null, 2));

        const violations = results.flatMap((result) => result.violations);
        if (violations.length > 0) {
            process.exitCode = 1;
        }
    } finally {
        await browser.close();
    }
}

main().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
});
