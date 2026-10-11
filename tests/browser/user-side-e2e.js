const { execFileSync } = require('child_process');
const { chromium } = require('playwright');

const baseUrl = (process.env.E2E_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const stamp = Date.now();
const username = `e2e_user_${stamp}`;
const email = `${username}@example.test`;
const password = 'E2eRequester!2026';
const requestMarker = `E2E browser request ${stamp}`;
const messageMarker = `E2E browser message ${stamp}`;

function verificationUrl() {
    const php = [
        'require "vendor/autoload.php";',
        '$app=require "bootstrap/app.php";',
        '$kernel=$app->make(Illuminate\\Contracts\\Console\\Kernel::class);',
        '$kernel->bootstrap();',
        `config(['app.url'=>'${baseUrl}']);`,
        `Illuminate\\Support\\Facades\\URL::forceRootUrl('${baseUrl}');`,
        `$user=App\\Models\\User::where('username','${username}')->firstOrFail();`,
        "echo Illuminate\\Support\\Facades\\URL::temporarySignedRoute('verification.verify', now()->addMinutes(5), ['id'=>$user->id,'hash'=>sha1($user->email)]);",
    ].join(' ');
    return execFileSync('php', ['-r', php], { encoding: 'utf8' }).trim();
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    await context.tracing.start({ screenshots: true, snapshots: true, sources: true });
    const evidence = {};

    try {
        await page.goto(`${baseUrl}/register`, { waitUntil: 'domcontentloaded' });
        await page.getByLabel('First name').fill('Browser');
        await page.getByLabel('Last name').fill('Requester');
        const office = page.getByLabel('Office / Department');
        const officeValues = await office.locator('option').evaluateAll((options) =>
            options.map((option) => option.value).filter(Boolean)
        );
        await office.selectOption(officeValues[0]);
        await page.getByLabel('Email address').fill(email);
        await page.getByLabel('Username').fill(username);
        await page.getByLabel('Password', { exact: true }).fill(password);
        await page.getByLabel('Confirm password').fill(password);
        await page.getByRole('button', { name: 'Create my account' }).click();
        await page.waitForLoadState('domcontentloaded');
        evidence.registration = { url: page.url(), username };

        if (new URL(page.url()).pathname.endsWith('/verify-email')) {
            await page.goto(verificationUrl(), { waitUntil: 'domcontentloaded' });
            evidence.emailVerification = 'signed verification route completed';
        }
        await page.waitForURL('**/dashboard');
        await page.getByRole('heading', { name: /Service dashboard/i }).waitFor();

        await page.goto(`${baseUrl}/requests/create`, { waitUntil: 'domcontentloaded' });
        await page.locator('details.service-category').filter({ hasText: 'ICT Equipment Services' }).locator('summary').click();
        await page.getByLabel('Troubleshoot PC/Laptop').check();
        await page.getByLabel(/Description\/Details of Request/).fill(requestMarker);
        await page.getByLabel('Property/Asset No.').fill(`E2E-${stamp}`);
        await page.getByLabel('Attachments (Optional)').setInputFiles({
            name: 'browser-evidence.pdf',
            mimeType: 'application/pdf',
            buffer: Buffer.from('%PDF-1.4\n% E2E browser evidence\n'),
        });
        await page.getByRole('button', { name: 'Submit request' }).click();
        await page.waitForURL('**/dashboard');
        await page.getByRole('heading', { name: /Service dashboard/i }).waitFor();
        await page.getByRole('link', { name: /1\s*Service Requests/i }).waitFor();
        evidence.submission = 'dashboard redirect and one-request total visible';

        await page.goto(`${baseUrl}/requests`, { waitUntil: 'domcontentloaded' });
        await page.getByRole('heading', { name: 'Service Requests History' }).waitFor();
        await page.getByRole('link', { name: 'View details' }).first().click();
        await page.getByText(requestMarker, { exact: true }).waitFor();
        evidence.details = { url: page.url(), markerVisible: true };

        await page.goto(`${baseUrl}/user/messenger`, { waitUntil: 'domcontentloaded' });
        await page.getByRole('heading', { name: 'Messages' }).waitFor();
        await page.getByRole('link', { name: 'Open Chat' }).click();
        await page.getByLabel('Message').fill(messageMarker);
        await page.getByRole('button', { name: 'Send', exact: true }).click();
        await page.locator('#chatMessages').getByText(messageMarker, { exact: true }).waitFor();
        evidence.messaging = 'sent message visible in chat';

        await page.getByRole('button', { name: 'Log out' }).click();
        await page.waitForURL('**/');
        evidence.logout = { url: page.url() };

        await context.tracing.stop();
        console.log(JSON.stringify({ result: 'passed', evidence }, null, 2));
    } catch (error) {
        await page.screenshot({ path: 'storage/app/user-side-e2e-failure.png', fullPage: true });
        await context.tracing.stop({ path: 'storage/app/user-side-e2e-trace.zip' });
        console.error(JSON.stringify({ result: 'failed', message: error.message, url: page.url(), evidence }, null, 2));
        process.exitCode = 1;
    } finally {
        await browser.close();
    }
})();
