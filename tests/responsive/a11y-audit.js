const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = process.env.A11Y_BASE_URL || 'http://127.0.0.1:8000';
const username = process.env.A11Y_USERNAME;
const password = process.env.A11Y_PASSWORD;
const authenticatedPaths = [
    '/dashboard',
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
        incomplete: result.incomplete.map((rule) => ({
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
        results.push(await auditPage(page, '/register'));

        if (!username || !password) {
            throw new Error('Set A11Y_USERNAME and A11Y_PASSWORD to run the authenticated page audit.');
        }

        await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
        await page.locator('input[name="username"]').fill(username);
        await page.locator('input[name="password"]').fill(password);
        await page.locator('input[name="password"]').press('Enter');
        await page.waitForLoadState('networkidle');
        if (new URL(page.url()).pathname.endsWith('/login')) {
            throw new Error('Authenticated accessibility audit could not sign in.');
        }

        for (const path of authenticatedPaths) {
            results.push(await auditPage(page, path));
        }

        await page.goto(`${baseUrl}/requests`, { waitUntil: 'networkidle' });
        const detailLink = page.getByRole('link', { name: 'View details' }).first();
        if (await detailLink.count()) {
            const detailUrl = await detailLink.getAttribute('href');
            const detailPath = new URL(detailUrl, baseUrl).pathname;
            const basePath = new URL(baseUrl).pathname.replace(/\/$/, '');
            const appRelativePath = detailPath.startsWith(basePath)
                ? detailPath.slice(basePath.length)
                : detailPath;
            results.push(await auditPage(page, appRelativePath));
        }

        console.log(JSON.stringify(results, null, 2));

        const violations = results.flatMap((result) => result.violations);
        const prohibitedAria = results.flatMap((result) =>
            result.incomplete
                .filter((rule) => rule.id === 'aria-prohibited-attr')
                .map((rule) => ({ path: result.path, rule }))
        );
        if (violations.length > 0 || prohibitedAria.length > 0) {
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
