const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;
const { execFileSync } = require('child_process');

const baseUrl = (process.env.AUTH_BASE_URL || 'http://localhost/mis-service-request/mis-service-request/public').replace(/\/$/, '');
const pages = [
    { name: 'login', path: '/login' },
    { name: 'register', path: '/register' },
    { name: 'forgot', path: '/forgot-password' },
    { name: 'reset', path: '/reset-password/visual-preview-token' },
    { name: '2fa-verify', preview: true },
    { name: '2fa-setup', preview: true },
];
const screens = [
    { name: 'small-phone', width: 320, height: 700 },
    { name: 'phone', width: 390, height: 844 },
    { name: 'small-tablet', width: 700, height: 1000 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'large-tablet', width: 820, height: 1180 },
    { name: 'tablet-landscape', width: 1024, height: 768 },
    { name: 'desktop', width: 1440, height: 900 },
];

(async () => {
    const browser = await chromium.launch({ headless: true });
    const results = [];

    try {
        for (const screen of screens) {
            const context = await browser.newContext({
                viewport: { width: screen.width, height: screen.height },
                isMobile: screen.width < 1100,
                hasTouch: screen.width < 1100,
                deviceScaleFactor: screen.width < 1100 ? 2 : 1,
            });
            for (const entry of pages) {
                const page = await context.newPage();
                let status = 200;
                if (entry.preview) {
                    const html = execFileSync('php', ['tests/responsive/render-auth-preview.php', entry.name], { encoding: 'utf8' })
                        .replaceAll('http://localhost/', `${baseUrl}/`);
                    if (!html.startsWith('<!DOCTYPE html>')) throw new Error(`Failed to render ${entry.name} preview`);
                    await page.setContent(html, { waitUntil: 'networkidle' });
                } else {
                    const response = await page.goto(`${baseUrl}${entry.path}`, { waitUntil: 'networkidle' });
                    status = response?.status();
                }
                const info = await page.evaluate(async () => {
                    const card = document.querySelector('.auth-content');
                    const rect = card.getBoundingClientRect();
                    const homeLinks = Array.from(document.querySelectorAll('a')).filter((link) => link.textContent.trim() === 'Back to home');
                    const sealStyle = getComputedStyle(document.body, '::before');
                    const sealUrl = sealStyle.backgroundImage.match(/url\(["']?([^"')]+)/)?.[1];
                    const seal = new Image();
                    let sealLoaded = false;
                    if (sealUrl) {
                        seal.src = sealUrl;
                        sealLoaded = await seal.decode().then(() => seal.naturalWidth > 0, () => false);
                    }
                    return {
                        pageWidth: document.documentElement.scrollWidth,
                        pageHeight: document.documentElement.scrollHeight,
                        viewportWidth: document.documentElement.clientWidth,
                        viewportHeight: innerHeight,
                        cardLeft: rect.left,
                        cardRight: rect.right,
                        cardTop: rect.top,
                        homeLinkCount: homeLinks.length,
                        homeLinkAboveCard: homeLinks.length === 1 && homeLinks[0].getBoundingClientRect().bottom <= rect.top,
                        cardShadow: getComputedStyle(card).boxShadow,
                        gradient: getComputedStyle(document.body).backgroundImage,
                        sealImage: sealStyle.backgroundImage,
                        sealRadius: sealStyle.borderRadius,
                        sealLeft: innerWidth - parseFloat(sealStyle.right) - parseFloat(sealStyle.width),
                        sealWidth: parseFloat(sealStyle.width),
                        sealOpacity: parseFloat(sealStyle.opacity),
                        logoBehindCard: Math.abs(parseFloat(sealStyle.left) - rect.right) < 2
                            && parseFloat(sealStyle.width) > 150,
                        compactCard: card.classList.contains('compact'),
                        sealLoaded,
                    };
                });
                const violations = screen.width === 390 || screen.width === 768
                    ? (await new AxeBuilder({ page }).analyze()).violations.map((violation) => violation.id)
                    : [];
                if (['phone', 'tablet', 'large-tablet', 'tablet-landscape', 'desktop'].includes(screen.name)) {
                    await page.screenshot({ path: `storage/app/auth-${entry.name}-${screen.name}-20260927.png`, fullPage: true });
                }
                let homeNavigationWorks = true;
                if (screen.name === 'phone' && ['login', 'register', 'forgot'].includes(entry.name)) {
                    await page.getByRole('link', { name: 'Back to home' }).click();
                    await page.waitForLoadState('domcontentloaded');
                    homeNavigationWorks = page.url().replace(/\/$/, '') === baseUrl;
                }
                results.push({ page: entry.name, screen: screen.name, status, ...info, violations, homeNavigationWorks });
                await page.close();
            }
            await context.close();
        }
    } finally {
        await browser.close();
    }

    console.log(JSON.stringify(results.map(({ page, screen, status, pageWidth, viewportWidth, pageHeight, viewportHeight, cardLeft, cardRight, sealLeft, sealLoaded, violations, homeNavigationWorks }) => ({ page, screen, status, pageWidth, viewportWidth, pageHeight, viewportHeight, cardLeft, cardRight, sealLeft, sealLoaded, violations, homeNavigationWorks })), null, 2));
    if (results.some((result) => result.status !== 200 || result.pageWidth > result.viewportWidth || result.cardLeft < 0 || result.cardRight > result.viewportWidth || result.cardShadow === 'none' || !result.homeLinkAboveCard || !result.homeNavigationWorks || !result.gradient.includes('linear-gradient') || !result.sealImage.includes('janiuay2-1021x1024.jpg') || (result.viewportWidth > 600 && result.sealRadius !== '50%') || !result.sealLoaded || result.violations.length || (result.viewportWidth >= 901 && (!result.logoBehindCard || Math.abs((result.cardLeft + result.cardRight) / 2 - result.viewportWidth / 2) > 2)) || (result.viewportWidth >= 601 && result.viewportWidth <= 900 && result.viewportHeight > result.viewportWidth && (Math.abs((result.cardLeft + result.cardRight) / 2 - result.viewportWidth / 2) > 2 || result.sealWidth < result.viewportWidth * .45 || result.sealOpacity < .25 || result.sealLeft >= result.cardRight || result.sealLeft + result.sealWidth <= result.cardRight)))) {
        process.exitCode = 1;
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
