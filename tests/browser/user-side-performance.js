const { chromium } = require('playwright');

const baseUrl = (process.env.PERF_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const username = process.env.PERF_USERNAME;
const password = process.env.PERF_PASSWORD;

if (!username || !password) {
    throw new Error('Set PERF_USERNAME and PERF_PASSWORD.');
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    const results = [];

    try {
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        await page.getByLabel(/Username or email/i).fill(username);
        await page.getByLabel('Password', { exact: true }).fill(password);
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL('**/dashboard');

        await page.goto(`${baseUrl}/requests`, { waitUntil: 'networkidle' });
        const detailHref = await page.getByRole('link', { name: 'View details' }).first().getAttribute('href');
        const detailPath = new URL(detailHref, baseUrl).pathname;
        const basePath = new URL(baseUrl).pathname.replace(/\/$/, '');
        const appRelativeDetailPath = detailPath.startsWith(basePath)
            ? detailPath.slice(basePath.length)
            : detailPath;
        const paths = [
            '/dashboard',
            '/requests',
            '/requests/create',
            appRelativeDetailPath,
            '/incident-reports',
            '/incident-reports/create',
            '/pm-checklists',
            '/user/messenger',
            '/user/profile',
            '/user/settings',
            '/user/security',
            '/user/notifications',
        ];

        for (const path of paths) {
            const started = performance.now();
            const response = await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
            const wallMs = performance.now() - started;
            const metrics = await page.evaluate(() => {
                const navigation = performance.getEntriesByType('navigation')[0];
                const resources = performance.getEntriesByType('resource');
                return {
                    ttfbMs: navigation ? navigation.responseStart : null,
                    domContentLoadedMs: navigation ? navigation.domContentLoadedEventEnd : null,
                    loadEventMs: navigation ? navigation.loadEventEnd : null,
                    documentBytes: navigation ? navigation.transferSize : 0,
                    assetCount: resources.length,
                    assetBytes: resources.reduce((sum, entry) => sum + (entry.transferSize || 0), 0),
                    assets: resources.map((entry) => ({
                        name: new URL(entry.name).pathname,
                        type: entry.initiatorType,
                        bytes: entry.transferSize || 0,
                    })),
                };
            });
            results.push({
                path,
                status: response ? response.status() : null,
                wallMs: Math.round(wallMs),
                ...metrics,
            });
        }

        const heaviest = results.reduce((current, item) =>
            (item.documentBytes + item.assetBytes) > (current.documentBytes + current.assetBytes) ? item : current
        );
        console.log(JSON.stringify({ results, heaviestPath: heaviest.path }, null, 2));
    } finally {
        await browser.close();
    }
})();
