const { chromium } = require('playwright');

const baseUrl = (process.env.A11Y_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const username = process.env.A11Y_USERNAME || 'blackbox_requester_b';
const password = process.env.A11Y_PASSWORD || 'RequesterBPass123!';

async function active(page) {
    return page.evaluate(() => {
        const element = document.activeElement;
        const label = element?.labels?.[0]?.textContent?.trim() || element?.getAttribute('aria-label') || element?.textContent?.trim() || '';
        return { tag: element?.tagName, id: element?.id, name: element?.getAttribute('name'), type: element?.getAttribute('type'), label: label.slice(0, 120) };
    });
}

async function tabTo(page, predicate, limit = 80) {
    for (let i = 0; i < limit; i += 1) {
        await page.keyboard.press('Tab');
        const focused = await active(page);
        if (predicate(focused)) return { tabs: i + 1, ...focused };
    }
    throw new Error('Keyboard target was not reachable within the tab limit.');
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    const evidence = { login: {}, request: {} };
    try {
        await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
        evidence.login.username = await tabTo(page, (f) => f.name === 'username');
        await page.keyboard.type(username);
        evidence.login.password = await tabTo(page, (f) => f.name === 'password');
        await page.keyboard.type(password);
        evidence.login.submit = await tabTo(page, (f) => f.tag === 'BUTTON' && /sign in/i.test(f.label));
        await page.keyboard.press('Enter');
        await page.waitForURL('**/dashboard');

        await page.goto(`${baseUrl}/requests/create`, { waitUntil: 'networkidle' });
        const requiredFields = await page.locator('input[required], select[required], textarea[required]').evaluateAll((fields) => fields.map((field) => ({
            name: field.name,
            id: field.id,
            tag: field.tagName,
            requiredProperty: field.required,
            ariaRequired: field.getAttribute('aria-required'),
            accessibleLabel: field.labels?.[0]?.textContent?.trim() || field.getAttribute('aria-label') || '',
        })));
        evidence.request.requiredFields = requiredFields;

        const cdp = await context.newCDPSession(page);
        await cdp.send('Accessibility.enable');
        const ax = await cdp.send('Accessibility.getFullAXTree');
        const interactive = ax.nodes.filter((node) => ['textbox', 'combobox', 'checkbox', 'button'].includes(node.role?.value));
        evidence.request.accessibilityTree = {
            nodeCount: ax.nodes.length,
            interactiveCount: interactive.length,
            unnamedInteractive: interactive.filter((node) => !node.name?.value).map((node) => node.role?.value),
            sample: interactive.slice(0, 12).map((node) => ({ role: node.role?.value, name: node.name?.value, required: node.properties?.find((property) => property.name === 'required')?.value?.value ?? null })),
        };

        evidence.request.service = await tabTo(page, (f) => f.type === 'checkbox' && f.id === 'ict_troubleshoot_pc');
        await page.keyboard.press('Space');
        evidence.request.description = await tabTo(page, (f) => f.name === 'description');
        await page.keyboard.type(`Keyboard accessibility request ${Date.now()}`);
        evidence.request.property = await tabTo(page, (f) => f.name === 'property_no');
        await page.keyboard.type(`KEYBOARD-${Date.now()}`);
        evidence.request.submit = await tabTo(page, (f) => f.tag === 'BUTTON' && /submit request/i.test(f.label));
        await page.keyboard.press('Enter');
        await page.waitForURL('**/dashboard');
        evidence.request.result = 'dashboard redirect after keyboard-only form interaction';

        console.log(JSON.stringify({ result: 'passed', evidence }, null, 2));
    } catch (error) {
        console.error(JSON.stringify({ result: 'failed', message: error.message, url: page.url(), evidence }, null, 2));
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
