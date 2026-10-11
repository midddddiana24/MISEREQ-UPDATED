const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = (process.env.WELCOME_BASE_URL || 'http://localhost/mis-service-request/mis-service-request/public').replace(/\/$/, '');
const sizes = [
    { width: 1440, height: 900, name: 'desktop' },
    { width: 1024, height: 768, name: 'tablet-landscape', mobile: true },
    { width: 820, height: 1180, name: 'large-tablet', mobile: true },
    { width: 768, height: 1024, name: 'tablet', mobile: true },
    { width: 844, height: 390, name: 'phone-landscape', mobile: true },
    { width: 390, height: 844, name: 'phone', mobile: true },
    { width: 320, height: 700, name: 'small-phone', mobile: true },
];

(async () => {
    const browser = await chromium.launch({ headless: true });
    const results = [];

    try {
        for (const size of sizes) {
            const context = await browser.newContext({
                viewport: { width: size.width, height: size.height },
                isMobile: Boolean(size.mobile),
                hasTouch: Boolean(size.mobile),
                deviceScaleFactor: size.mobile ? 2 : 1,
            });
            const page = await context.newPage();
            const response = await page.goto(`${baseUrl}/`, { waitUntil: 'networkidle' });
            await page.screenshot({ path: `storage/app/welcome-${size.name}-20260927.png`, fullPage: true });
            const layout = await page.evaluate(() => ({
                viewportWidth: document.documentElement.clientWidth,
                pageWidth: document.documentElement.scrollWidth,
                viewportHeight: window.innerHeight,
                pageHeight: document.documentElement.scrollHeight,
                titleVisible: (() => {
                    const rect = document.querySelector('.welcome-copy h1').getBoundingClientRect();
                    return rect.left >= 0 && rect.right <= innerWidth;
                })(),
                sealLoaded: document.querySelector('.welcome-seal').complete && document.querySelector('.welcome-seal').naturalWidth > 0,
                sealVisible: getComputedStyle(document.querySelector('.seal-wrap')).display !== 'none',
                sealRect: (() => {
                    const rect = document.querySelector('.welcome-seal').getBoundingClientRect();
                    return { left: rect.left, right: rect.right, top: rect.top, bottom: rect.bottom };
                })(),
                copyRect: (() => {
                    const rect = document.querySelector('.welcome-copy').getBoundingClientRect();
                    return { left: rect.left, right: rect.right, top: rect.top, bottom: rect.bottom };
                })(),
                buttons: Array.from(document.querySelectorAll('.welcome-actions .button')).map((button) => {
                    const rect = button.getBoundingClientRect();
                    return { text: button.textContent.trim(), width: rect.width, height: rect.height, left: rect.left, right: rect.right };
                }),
                assistanceTop: document.querySelector('.walk-in').getBoundingClientRect().top,
                buttonsBottom: document.querySelector('.welcome-actions').getBoundingClientRect().bottom,
            }));
            const accessibility = (await new AxeBuilder({ page }).analyze()).violations.map((violation) => ({
                id: violation.id,
                nodes: violation.nodes.map((node) => ({ target: node.target, summary: node.failureSummary })),
            }));
            let linksWork = true;
            const linkDestinations = {};
            if (size.mobile) {
                await page.getByRole('link', { name: 'Sign in' }).tap();
                await page.waitForURL('**/login');
                linkDestinations.signIn = page.url();
                linksWork = page.url().endsWith('/login');
                await page.goto(`${baseUrl}/`, { waitUntil: 'domcontentloaded' });
                await page.getByRole('link', { name: 'Create account' }).tap();
                await page.waitForURL('**/register');
                linkDestinations.createAccount = page.url();
                linksWork = linksWork && page.url().endsWith('/register');
            }
            results.push({ ...size, status: response?.status(), ...layout, accessibilityViolations: accessibility, linksWork, linkDestinations });
            await context.close();
        }
    } finally {
        await browser.close();
    }

    console.log(JSON.stringify(results, null, 2));
    if (results.some((result) => result.status !== 200 || result.pageWidth > result.viewportWidth || !result.titleVisible || !result.sealLoaded || result.accessibilityViolations?.length || !result.linksWork || result.buttons.some((button) => button.height < 44 || button.left < 0 || button.right > result.viewportWidth) || result.assistanceTop < result.buttonsBottom || (result.width <= 640 && (!result.sealVisible || result.sealRect.left < result.viewportWidth * .4 || result.sealRect.left > result.viewportWidth * .65 || result.sealRect.right <= result.viewportWidth)) || (result.width > 640 && result.sealVisible && result.copyRect.right > result.sealRect.left && result.copyRect.left < result.sealRect.right && result.copyRect.bottom > result.sealRect.top && result.copyRect.top < result.sealRect.bottom))) {
        process.exitCode = 1;
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
