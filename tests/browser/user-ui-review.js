const { chromium } = require('playwright');
const AxeBuilder = require('@axe-core/playwright').default;

const baseUrl = (process.env.UI_REVIEW_BASE_URL || 'http://localhost/mis-service-request/mis-service-request/public').replace(/\/$/, '');
const username = process.env.UI_REVIEW_USERNAME;
const password = process.env.UI_REVIEW_PASSWORD;
if (!username || !password) {
    throw new Error('Set UI_REVIEW_USERNAME and UI_REVIEW_PASSWORD for a read-only QA account.');
}

const sizes = [
    { name: 'small-phone', width: 320, height: 700 },
    { name: 'phone', width: 390, height: 844 },
    { name: 'short-landscape', width: 667, height: 375 },
    { name: 'tablet', width: 768, height: 1024 },
    { name: 'tablet-landscape', width: 1024, height: 768 },
    { name: 'desktop', width: 1440, height: 900 },
].filter((size) => !process.env.UI_REVIEW_SIZES || process.env.UI_REVIEW_SIZES.split(',').includes(size.name));
const paths = (process.env.UI_REVIEW_PATHS || '/,/dashboard,/requests,/requests/create,/user/messenger,/user/settings,/incident-reports,/pm-checklists').split(',');

(async () => {
    const browser = await chromium.launch({ headless: true });
    const results = [];
    try {
        for (const size of sizes) {
            const context = await browser.newContext({ viewport: { width: size.width, height: size.height } });
            const page = await context.newPage();
            await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
            await page.getByLabel(/Username or email/i).fill(username);
            await page.getByLabel('Password', { exact: true }).fill(password);
            await page.getByRole('button', { name: /Sign in/i }).click();
            await page.waitForURL('**/dashboard', { timeout: 20000 });

            for (const path of paths) {
                const response = await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
                const layout = await page.evaluate(() => {
                    const header = document.querySelector('.public-header');
                    const heading = header?.querySelector('.public-university-name');
                    const logos = Array.from(header?.querySelectorAll('img') || []);
                    const logout = header?.querySelector('.public-logout');
                    const bound = (element) => {
                        if (!element) return null;
                        const rect = element.getBoundingClientRect();
                        return { left: rect.left, right: rect.right, top: rect.top, bottom: rect.bottom };
                    };
                    return {
                        viewport: document.documentElement.clientWidth,
                        scrollWidth: document.documentElement.scrollWidth,
                        viewportHeight: window.innerHeight,
                        pageHeight: document.documentElement.scrollHeight,
                        mainHeight: Math.round(document.querySelector('main')?.getBoundingClientRect().height || 0),
                        internalScrolls: Array.from(document.querySelectorAll('main *')).filter((item) => {
                            const style = getComputedStyle(item);
                            return ['auto', 'scroll'].includes(style.overflowY) && item.scrollHeight > item.clientHeight + 30;
                        }).map((item) => ({ className: String(item.className || '').slice(0, 80), clientHeight: item.clientHeight, scrollHeight: item.scrollHeight })).slice(0, 8),
                        sectionHeights: Array.from(document.querySelectorAll('main > section, main .settings-card, main .notification-page-list, main .request-history-scroll')).map((item) => ({
                            name: item.querySelector('h1,h2')?.textContent.trim() || String(item.className || '').slice(0, 50),
                            height: Math.round(item.getBoundingClientRect().height),
                        })).slice(0, 15),
                        heading: heading?.textContent.trim(),
                        headingBounds: bound(heading),
                        logoutBounds: bound(logout),
                        headerLogoutVisible: logout ? getComputedStyle(logout).display !== 'none' && getComputedStyle(logout.closest('.public-header-actions')).display !== 'none' : false,
                        logoLoaded: logos.map((logo) => logo.complete && logo.naturalWidth > 0),
                        navVisible: Boolean(document.querySelector('[data-nav-menu]')),
                    };
                });
                if (path === '/requests/create') {
                    const categories = page.locator('details.service-category');
                    layout.categoryCount = await categories.count();
                    layout.initiallyOpenCategories = await categories.evaluateAll((items) => items.filter((item) => item.open).length);
                    await categories.first().locator('summary').click();
                    layout.firstCategoryOpens = await categories.first().evaluate((item) => item.open);
                    layout.serviceOptionVisible = await page.getByLabel('New Network for Computer').isVisible();
                    layout.fontSizes = await page.evaluate(() => ({
                        formLabel: getComputedStyle(document.querySelector('.form-group label')).fontSize,
                        serviceOption: getComputedStyle(document.querySelector('.checkbox-item label')).fontSize,
                        helper: getComputedStyle(document.querySelector('.form-group small')).fontSize,
                    }));
                    await categories.first().locator('summary').click();
                    layout.skipLinks = await page.evaluate(() => Array.from(document.querySelectorAll('.public-skip-link')).map((link) => ({
                        top: link.getBoundingClientRect().top,
                        bottom: link.getBoundingClientRect().bottom,
                        focused: document.activeElement === link,
                    })));
                }
                if (path === '/dashboard' || path === '/requests/create' || (size.name === 'phone' && ['/requests', '/user/security', '/user/notifications', '/incident-reports', '/pm-checklists'].includes(path))) {
                    layout.axeViolations = (await new AxeBuilder({ page }).analyze()).violations.map((violation) => violation.id);
                }
                if (['/requests', '/incident-reports', '/pm-checklists'].includes(path)) {
                    layout.summaryCards = await page.locator('.stats-grid .stat-card').count();
                    layout.summaryColumns = await page.locator('.stats-grid').evaluate((grid) => getComputedStyle(grid).gridTemplateColumns.split(' ').length);
                    if (['/incident-reports', '/pm-checklists'].includes(path)) {
                        layout.summaryRows = await page.locator('.stats-grid').evaluate((grid) => {
                            const cards = Array.from(grid.querySelectorAll('.stat-card'));
                            return cards.map((card) => Math.round(card.getBoundingClientRect().top));
                        });
                        layout.searchSameRow = await page.locator('.record-search-form').evaluate((form) => {
                            const input = form.querySelector('input[type="search"]').getBoundingClientRect();
                            const button = form.querySelector('button').getBoundingClientRect();
                            return Math.abs(input.top - button.top) < 2 && input.right < button.left;
                        });
                    }
                }
                if (size.width <= 860 && path === '/') {
                    layout.mobileLogoutVisible = await page.locator('.welcome-mobile-logout button').isVisible();
                } else if (size.width <= 860) {
                    layout.mobileAlertsVisible = await page.locator('[data-notification-mobile-link]').isVisible();
                    layout.mobileAlertsLabel = await page.locator('[data-notification-mobile-link]').getAttribute('aria-label');
                    layout.mobileAlertsHref = await page.locator('[data-notification-mobile-link]').getAttribute('href');
                    await page.locator('[data-nav-toggle]').click();
                    layout.mobileLogoutVisible = await page.locator('.client-mobile-logout button').isVisible();
                    layout.menuScroll = await page.locator('[data-nav-menu]').evaluate((menu) => {
                        menu.scrollTop = menu.scrollHeight;
                        const lastButton = menu.querySelector('.client-mobile-logout button');
                        const bounds = menu.getBoundingClientRect();
                        const last = lastButton.getBoundingClientRect();
                        return {
                            clientHeight: menu.clientHeight,
                            scrollHeight: menu.scrollHeight,
                            scrollTop: menu.scrollTop,
                            maxHeight: getComputedStyle(menu).maxHeight,
                            overflowY: getComputedStyle(menu).overflowY,
                            bounds: { top: bounds.top, bottom: bounds.bottom },
                            last: { top: last.top, bottom: last.bottom },
                            scrollable: menu.scrollHeight > menu.clientHeight,
                            withinViewport: bounds.bottom <= window.innerHeight + 1,
                            logoutReachable: last.bottom <= bounds.bottom + 1 && last.top >= bounds.top - 1,
                        };
                    });
                    if (['phone', 'short-landscape'].includes(size.name) && path === '/dashboard') {
                        await page.screenshot({ path: `storage/app/user-ui-${size.name}-menu-20260928.png` });
                    }
                    await page.locator('[data-nav-toggle]').click();
                }
                if (path === '/dashboard' || (path === '/' && size.name === 'phone') || (['/user/messenger', '/user/security', '/user/notifications'].includes(path) && size.name === 'phone') || (path === '/requests/create' && ['small-phone', 'phone'].includes(size.name)) || (['/requests', '/incident-reports', '/pm-checklists'].includes(path) && ['small-phone', 'phone', 'tablet'].includes(size.name))) {
                    await page.evaluate(() => window.scrollTo(0, 0));
                    await page.screenshot({ path: `storage/app/user-ui-${size.name}-${path.slice(1).replaceAll('/', '-')}-20260928.png`, fullPage: true });
                }
                results.push({ size: size.name, path, status: response?.status(), ...layout });
            }
            await context.close();
        }
    } finally {
        await browser.close();
    }
    console.log(JSON.stringify(results, null, 2));
    if (results.some((result) => result.status !== 200 || result.scrollWidth > result.viewport + 1 || result.logoLoaded.some((loaded) => !loaded) || (result.path !== '/' && !result.navVisible) || result.axeViolations?.length || (result.path === '/requests/create' && (result.categoryCount !== 3 || !result.firstCategoryOpens || !result.serviceOptionVisible || parseFloat(result.fontSizes.formLabel) < 14 || parseFloat(result.fontSizes.serviceOption) < 14 || parseFloat(result.fontSizes.helper) < 14)) || (['/requests', '/incident-reports', '/pm-checklists'].includes(result.path) && (result.summaryCards !== (result.path === '/requests' ? 4 : 5) || (['small-phone', 'phone', 'tablet'].includes(result.size) && result.summaryColumns !== 2))) || (['/incident-reports', '/pm-checklists'].includes(result.path) && ['small-phone', 'phone'].includes(result.size) && (!result.searchSameRow || result.summaryRows[0] !== result.summaryRows[1] || result.summaryRows[2] !== result.summaryRows[3] || result.summaryRows[0] === result.summaryRows[2])) || !result.headingBounds || !result.logoutBounds || result.headingBounds.left < 0 || result.headingBounds.right > result.viewport || result.logoutBounds.left < 0 || result.logoutBounds.right > result.viewport || (['small-phone', 'phone', 'short-landscape', 'tablet'].includes(result.size) && (result.headerLogoutVisible || !result.mobileLogoutVisible || (result.path !== '/' && (!result.mobileAlertsVisible || !result.mobileAlertsLabel?.startsWith('Notifications') || !result.mobileAlertsHref?.endsWith('/user/notifications') || !result.menuScroll?.withinViewport || !result.menuScroll?.logoutReachable || (result.size === 'short-landscape' && !result.menuScroll.scrollable))))) || (['tablet-landscape', 'desktop'].includes(result.size) && !result.headerLogoutVisible))) {
        process.exitCode = 1;
    }
})().catch((error) => {
    console.error(error.message);
    process.exitCode = 1;
});
