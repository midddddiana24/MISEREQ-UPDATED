<?php

$baseUrl = rtrim($argv[1] ?? 'http://127.0.0.1:8000', '/');
$requestCount = max(1, (int) ($argv[2] ?? 60));
$concurrency = max(1, min(50, (int) ($argv[3] ?? 10)));
$paths = ['/', '/login', '/register', '/forgot-password', '/health'];
$durations = [];
$statuses = [];
$completed = 0;
$started = microtime(true);

while ($completed < $requestCount) {
    $batchSize = min($concurrency, $requestCount - $completed);
    $multi = curl_multi_init();
    $handles = [];

    for ($index = 0; $index < $batchSize; $index++) {
        $path = $paths[($completed + $index) % count($paths)];
        $handle = curl_init($baseUrl.$path);
        curl_setopt_array($handle, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT => 15,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_USERAGENT => 'MIS-Load-Smoke/1.0',
        ]);
        curl_multi_add_handle($multi, $handle);
        $handles[] = $handle;
    }

    do {
        $status = curl_multi_exec($multi, $running);
        if ($running) {
            curl_multi_select($multi, 1.0);
        }
    } while ($running && $status === CURLM_OK);

    foreach ($handles as $handle) {
        $statuses[] = (int) curl_getinfo($handle, CURLINFO_HTTP_CODE);
        $durations[] = (float) curl_getinfo($handle, CURLINFO_TOTAL_TIME) * 1000;
        curl_multi_remove_handle($multi, $handle);
        curl_close($handle);
    }
    curl_multi_close($multi);
    $completed += $batchSize;
}

sort($durations);
$failures = count(array_filter($statuses, fn (int $status) => $status < 200 || $status >= 400));
$percentile = fn (float $p) => $durations[min(count($durations) - 1, (int) floor(count($durations) * $p))] ?? 0;
$elapsed = microtime(true) - $started;

echo json_encode([
    'base_url' => $baseUrl,
    'requests' => $requestCount,
    'concurrency' => $concurrency,
    'failures' => $failures,
    'requests_per_second' => round($requestCount / max($elapsed, 0.001), 2),
    'latency_ms' => [
        'average' => round(array_sum($durations) / max(count($durations), 1), 2),
        'p50' => round($percentile(0.50), 2),
        'p95' => round($percentile(0.95), 2),
        'maximum' => round(max($durations ?: [0]), 2),
    ],
], JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES).PHP_EOL;

exit($failures === 0 ? 0 : 1);
