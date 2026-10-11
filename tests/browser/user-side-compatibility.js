const { chromium, firefox, webkit } = require('playwright');

const baseUrl = (process.env.COMPAT_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const username = process.env.COMPAT_USERNAME || 'blackbox_requester';
const password = process.env.COMPAT_PASSWORD || 'RequesterPass123!';
const widths = [375, 768, 1280];
const availableEngines = { chromium, firefox, webkit };
const requestedEngines = (process.env.COMPAT_ENGINES || 'chromium,firefox,webkit').split(',');
const engines = Object.fromEntries(requestedEngines.map((name) => [name, availableEngines[name]]).filter(([, engine]) => engine));

async function signIn(page) {
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
    await page.getByLabel(/Username or email/i).fill(username);
    await page.getByLabel('Password', { exact: true }).fill(password);
    await page.getByRole('button', { name: /Sign in/i }).click();
    await page.waitForURL('**/dashboard');
}

(async () => {
    const results = [];
    let failed = false;

    for (const [engineName, engine] of Object.entries(engines)) {
        let browser;
        try {
            browser = await engine.launch({ headless: true, timeout: 30000 });
        } catch (error) {
            failed = true;
            results.push({ engine: engineName, result: 'launch-failed', message: error.message });
            continue;
        }
        try {
            for (const width of widths) {
                const context = await browser.newContext({ viewport: { width, height: width === 375 ? 812 : 900 } });
                const page = await context.newPage();
                try {
                    await signIn(page);
                    const pages = [];
                    for (const path of ['/dashboard', '/requests', '/requests/create']) {
                        const response = await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
                        const layout = await page.evaluate(() => ({
                            viewport: document.documentElement.clientWidth,
                            scrollWidth: document.documentElement.scrollWidth,
                            bodyScrollWidth: document.body.scrollWidth,
                            title: document.title,
                        }));
                        pages.push({ path, status: response?.status(), ...layout, horizontalOverflow: Math.max(layout.scrollWidth, layout.bodyScrollWidth) > layout.viewport + 1 });
                    }

                    let journey = 'render-only';
                    if (width === 1280) {
                        const marker = `Compatibility ${engineName} ${Date.now()}`;
                        await page.goto(`${baseUrl}/requests/create`, { waitUntil: 'domcontentloaded' });
                        await page.locator('details.service-category').filter({ hasText: 'ICT Equipment Services' }).locator('summary').click();
                        await page.getByLabel('Troubleshoot PC/Laptop').check();
                        await page.getByLabel(/Description\/Details of Request/).fill(marker);
                        await page.getByLabel('Property/Asset No.').fill(`COMPAT-${engineName}-${Date.now()}`);
                        await page.getByRole('button', { name: 'Submit request' }).click();
                        await page.waitForURL('**/dashboard');
                        await page.goto(`${baseUrl}/requests`, { waitUntil: 'networkidle' });
                        await page.getByRole('link', { name: 'View details' }).first().click();
                        await page.getByText(marker, { exact: true }).waitFor();
                        journey = 'login-submit-list-details passed';
                    }

                    if (width <= 860) {
                        await page.locator('[data-nav-toggle]').click();
                        await page.locator('.client-mobile-logout button').click();
                    } else {
                        await page.getByRole('button', { name: 'Log out' }).click();
                    }
                    await page.waitForURL('**/');
                    results.push({ engine: engineName, width, result: 'passed', journey, pages });
                } catch (error) {
                    failed = true;
                    results.push({ engine: engineName, width, result: 'failed', message: error.message, url: page.url() });
                } finally {
                    await context.close();
                }
            }
        } finally {
            await browser.close();
        }
    }

    console.log(JSON.stringify(results, null, 2));
    if (failed) process.exitCode = 1;
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
