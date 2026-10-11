const { execFileSync } = require('child_process');
const { chromium } = require('playwright');

const baseUrl = (process.env.PERF_BASE_URL || 'http://127.0.0.1:8002').replace(/\/$/, '');
const username = 'perf_scale_20260924';
const password = 'PerformanceScale!2026';

function php(code) {
    return execFileSync('php', ['-r', `require "vendor/autoload.php"; $app=require "bootstrap/app.php"; $kernel=$app->make(Illuminate\\Contracts\\Console\\Kernel::class); $kernel->bootstrap(); ${code}`], { encoding: 'utf8' }).trim();
}

function reset(count) {
    php(`
        $user=App\\Models\\User::updateOrCreate(['username'=>'${username}'],[
            'first_name'=>'Performance','last_name'=>'Scale','email'=>'${username}@example.test',
            'office'=>'MIS Office','password'=>Illuminate\\Support\\Facades\\Hash::make('${password}'),
            'role'=>'user','is_admin'=>false,'is_active'=>true,'email_verified_at'=>now()
        ]);
        Illuminate\\Support\\Facades\\DB::table('users')->where('id',$user->id)->update([
            'email_verified_at'=>now(),'failed_login_attempts'=>0,'locked_until'=>null,'must_change_password'=>false,
            'google2fa_secret'=>null,'two_factor_recovery_code'=>null,'two_fa_enabled_at'=>null
        ]);
        App\\Models\\ServiceRequest::where('user_id',$user->id)->delete();
        for($i=1;$i<=${count};$i++) App\\Models\\ServiceRequest::withoutEvents(fn()=>App\\Models\\ServiceRequest::create([
            'control_number'=>'PERF-SCALE-${count}-'.str_pad((string)$i,3,'0',STR_PAD_LEFT).'-'.bin2hex(random_bytes(2)),
            'user_id'=>$user->id,'requester_name'=>'Performance Scale','requester_office'=>'MIS Office','office'=>'MIS Office',
            'services'=>['troubleshoot_pc'],'description'=>'Scale fixture '.$i,'status'=>'pending',
            'date_of_request'=>now()->toDateString(),'time_of_request'=>now()->format('H:i:s')
        ]));
    `);
}

async function measure(browser, path) {
    const context = await browser.newContext({ viewport: { width: 1280, height: 800 } });
    const page = await context.newPage();
    await page.goto(`${baseUrl}/login`, { waitUntil: 'domcontentloaded' });
    await page.getByLabel(/Username or email/i).fill(username);
    await page.getByLabel('Password', { exact: true }).fill(password);
    await page.getByRole('button', { name: /Sign in/i }).click();
    await page.waitForLoadState('domcontentloaded');
    if (!page.url().endsWith('/dashboard')) {
        const message = await page.locator('body').innerText();
        throw new Error(`Performance account login stopped at ${page.url()}: ${message.slice(0, 500)}`);
    }
    const cdp = await context.newCDPSession(page);
    await cdp.send('Network.enable');
    await cdp.send('Network.clearBrowserCache');
    await cdp.send('Network.setCacheDisabled', { cacheDisabled: true });
    const response = await page.goto(`${baseUrl}${path}`, { waitUntil: 'networkidle' });
    const metrics = await page.evaluate(() => {
        const navigation = performance.getEntriesByType('navigation')[0];
        const resources = performance.getEntriesByType('resource');
        return {
            documentBytes: navigation?.transferSize || 0,
            assetBytes: resources.reduce((sum, resource) => sum + (resource.transferSize || 0), 0),
            resources: resources.length,
        };
    });
    await context.close();
    return { path, status: response?.status(), ...metrics, totalBytes: metrics.documentBytes + metrics.assetBytes };
}

(async () => {
    const browser = await chromium.launch({ headless: true });
    const results = {};
    try {
        reset(1);
        results.oneRecord = {
            dashboard: await measure(browser, '/dashboard'),
            requests: await measure(browser, '/requests'),
        };
        reset(21);
        results.twentyOneRecords = {
            dashboard: await measure(browser, '/dashboard'),
            requests: await measure(browser, '/requests'),
        };
        console.log(JSON.stringify(results, null, 2));
    } finally {
        await browser.close();
        php(`$user=App\\Models\\User::where('username','${username}')->first(); if($user){ App\\Models\\ServiceRequest::where('user_id',$user->id)->delete(); $user->delete(); }`);
    }
})().catch((error) => {
    console.error(error);
    process.exitCode = 1;
});
