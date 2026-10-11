const { chromium } = require('playwright');

const baseUrl = (process.env.PERF_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const username = process.env.PERF_USERNAME;
const password = process.env.PERF_PASSWORD;
const screenshotPath = process.env.PERF_SCREENSHOT_PATH;

if (!username || !password) {
    throw new Error('Set PERF_USERNAME and PERF_PASSWORD.');
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();

    try {
        await page.goto(`${baseUrl}/login`, { waitUntil: 'networkidle' });
        await page.getByLabel(/Username or email/i).fill(username);
        await page.getByLabel('Password', { exact: true }).fill(password);
        await page.getByRole('button', { name: /Sign in/i }).click();
        await page.waitForURL('**/dashboard');
        await page.goto(`${baseUrl}/requests`, { waitUntil: 'networkidle' });

        const detailHref = await page.getByRole('link', { name: 'View details' }).first().getAttribute('href');
        if (!detailHref) {
            throw new Error('No request-details link is available for the performance account.');
        }

        const detailUrl = new URL(detailHref, baseUrl).toString();
        const cdp = await context.newCDPSession(page);
        await cdp.send('Network.enable');
        await cdp.send('Network.clearBrowserCache');
        await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });
        await page.goto(detailUrl, { waitUntil: 'networkidle' });

        if (screenshotPath) {
            await page.locator('.site-header').screenshot({ path: screenshotPath });
        }

        const metrics = await page.evaluate(() => {
            const navigation = performance.getEntriesByType('navigation')[0];
            const resources = performance.getEntriesByType('resource');
            const assets = resources.map((entry) => ({
                name: new URL(entry.name).pathname,
                bytes: entry.transferSize || 0,
            }));
            const documentBytes = navigation?.transferSize || 0;
            const assetBytes = assets.reduce((sum, asset) => sum + asset.bytes, 0);
            const logos = Array.from(document.querySelectorAll('.site-header .brand-mark')).map((image) => {
                const bounds = image.getBoundingClientRect();

                return {
                    currentSrc: new URL(image.currentSrc).pathname,
                    naturalWidth: image.naturalWidth,
                    naturalHeight: image.naturalHeight,
                    renderedWidth: bounds.width,
                    renderedHeight: bounds.height,
                };
            });

            return {
                documentBytes,
                assetBytes,
                totalBytes: documentBytes + assetBytes,
                assets,
                logos,
            };
        });

        console.log(JSON.stringify({
            url: page.url(),
            ...metrics,
        }, null, 2));
    } finally {
        await browser.close();
    }
})().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
});
