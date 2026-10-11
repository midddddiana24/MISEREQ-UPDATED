import json
import math
import os
import random
import re
import subprocess
import threading
import time
from collections import defaultdict
from concurrent.futures import ThreadPoolExecutor
from pathlib import Path

import requests


ROOT = Path(__file__).resolve().parents[2]
BASE = os.environ.get('LOAD_BASE_URL', 'http://127.0.0.1:8002').rstrip('/')
VUS = int(os.environ.get('LOAD_VUS', '15'))
DURATION = int(os.environ.get('LOAD_DURATION_SECONDS', '120'))
QUEUE = os.environ.get('LOAD_QUEUE', 'qa-user-side')
PHP = os.environ.get('LOAD_PHP', 'php')
STAMP = str(int(time.time()))
PREFIX = f'LOAD-QA-{STAMP}'
results = []
lock = threading.Lock()


def php_json(expression):
    code = (
        'require "vendor/autoload.php"; '
        '$app=require "bootstrap/app.php"; '
        '$kernel=$app->make(Illuminate\\Contracts\\Console\\Kernel::class); '
        '$kernel->bootstrap(); '
        f'echo json_encode({expression});'
    )
    output = subprocess.check_output([PHP, '-r', code], cwd=ROOT, text=True)
    return json.loads(output or 'null')


def token(response):
    match = re.search(r'name="_token"[^>]*value="([^"]+)"', response.text)
    return match.group(1) if match else None


def record(route, method, started, response=None, error=None):
    row = {
        'route': route,
        'method': method,
        'ms': round((time.perf_counter() - started) * 1000, 2),
        'status': response.status_code if response is not None else None,
        'error': type(error).__name__ if error else None,
    }
    with lock:
        results.append(row)


def timed(session, method, route, **kwargs):
    started = time.perf_counter()
    try:
        response = session.request(method, f'{BASE}{route}', timeout=45, **kwargs)
        record(route, method, started, response=response)
        return response
    except Exception as error:
        record(route, method, started, error=error)
        return None


def login(index):
    session = requests.Session()
    username = f'load_user_{index:02d}'
    page = timed(session, 'GET', '/login')
    if page is None:
        return session, False
    response = timed(
        session,
        'POST',
        '/login',
        data={'_token': token(page), 'username': username, 'password': 'LoadPass123!'},
        allow_redirects=False,
    )
    return session, bool(response is not None and response.status_code in (302, 303) and not response.headers.get('Location', '').endswith('/login'))


def submit_request(session, index):
    page = timed(session, 'GET', '/requests/create')
    if page is None or page.status_code != 200:
        return
    timed(
        session,
        'POST',
        '/requests',
        data={
            '_token': token(page),
            'requester_name': f'Load User {index:02d}',
            'requester_office': 'MIS Load Test',
            'services[]': 'troubleshoot_pc',
            'description': f'Mixed concurrent load request {index}',
            'property_no': f'{PREFIX}-{index:02d}',
            'date_request': time.strftime('%Y-%m-%d'),
            'time_request': time.strftime('%H:%M'),
        },
        allow_redirects=False,
    )


def percentile(values, value):
    if not values:
        return None
    ordered = sorted(values)
    position = max(0, min(len(ordered) - 1, math.ceil(value * len(ordered)) - 1))
    return round(ordered[position], 2)


subprocess.run([PHP, 'scripts/setup_load_accounts.php', str(VUS)], cwd=ROOT, check=True)
queue_before = php_json('['
    f'"queued"=>Illuminate\\Support\\Facades\\DB::table("jobs")->where("queue","{QUEUE}")->count(),'
    '"failed"=>Illuminate\\Support\\Facades\\DB::table("failed_jobs")->count()'
    ']')

# The login limiter is IP based. Retry throttled setup logins after its one-minute window
# so all 15 users enter the sustained authenticated phase.
sessions = {}
pending = list(range(1, VUS + 1))
login_round = 0
while pending and login_round < 3:
    login_round += 1
    next_pending = []
    for index in pending:
        session, ok = login(index)
        if ok:
            sessions[index] = session
        else:
            next_pending.append(index)
    pending = next_pending
    if pending:
        time.sleep(62)

if pending:
    raise RuntimeError(f'Unable to authenticate load users: {pending}')

barrier = threading.Barrier(VUS)
deadline = time.monotonic() + DURATION


def worker(index):
    session = sessions[index]
    barrier.wait(timeout=30)

    # Distribute initial actions so submission, dashboard, and list traffic overlap.
    if index % 3 == 0:
        submit_request(session, index)
    elif index % 3 == 1:
        timed(session, 'GET', '/dashboard')
    else:
        timed(session, 'GET', '/requests')

    submitted = index % 3 == 0
    cycle = 0
    while time.monotonic() < deadline:
        if not submitted and cycle >= 1:
            submit_request(session, index)
            submitted = True
        elif cycle % 2 == 0:
            timed(session, 'GET', '/dashboard')
        else:
            timed(session, 'GET', '/requests')
        cycle += 1
        time.sleep(random.uniform(0.2, 0.8))


started = time.time()
with ThreadPoolExecutor(max_workers=VUS) as pool:
    list(pool.map(worker, range(1, VUS + 1)))
elapsed = round(time.time() - started, 2)

# Give the isolated worker a short drain window before measuring backlog.
time.sleep(15)
queue_after = php_json('['
    f'"queued"=>Illuminate\\Support\\Facades\\DB::table("jobs")->where("queue","{QUEUE}")->count(),'
    '"failed"=>Illuminate\\Support\\Facades\\DB::table("failed_jobs")->count()'
    ']')

rows = php_json(
    f'App\\Models\\ServiceRequest::where("property_no", "like", "{PREFIX}-%")'
    '->get(["id","control_number","property_no"])->toArray()'
)
control_numbers = [row['control_number'] for row in rows]

summary = {}
for method, route in sorted({(row['method'], row['route']) for row in results}):
    route_rows = [row for row in results if row['route'] == route and row['method'] == method]
    durations = [row['ms'] for row in route_rows]
    errors = [row for row in route_rows if row['error'] or not row['status'] or row['status'] >= 400]
    summary[f'{method} {route}'] = {
        'requests': len(route_rows),
        'p50_ms': percentile(durations, 0.50),
        'p95_ms': percentile(durations, 0.95),
        'p99_ms': percentile(durations, 0.99),
        'error_rate_percent': round(100 * len(errors) / len(route_rows), 2),
        'status_counts': {str(status): sum(1 for row in route_rows if row['status'] == status) for status in sorted({row['status'] for row in route_rows}, key=lambda item: (item is None, item))},
        'timeouts': sum(1 for row in route_rows if row['error'] in ('ReadTimeout', 'ConnectTimeout')),
        'error_counts': {
            error: sum(1 for row in route_rows if row['error'] == error)
            for error in sorted({row['error'] for row in route_rows if row['error']})
        },
        'server_errors': sum(1 for row in route_rows if row['status'] and row['status'] >= 500),
    }

print(json.dumps({
    'configuration': {'vus': VUS, 'duration_seconds': DURATION, 'elapsed_seconds': elapsed, 'base_url': BASE, 'queue': QUEUE},
    'summary': summary,
    'total_requests': len(results),
    'queue_before': queue_before,
    'queue_after': queue_after,
    'created_requests': len(rows),
    'duplicate_control_numbers': len(control_numbers) - len(set(control_numbers)),
    'prefix': PREFIX,
}, indent=2, sort_keys=True))
