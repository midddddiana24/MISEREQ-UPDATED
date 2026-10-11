<?php

$root = dirname(__DIR__, 2);
$requirements = [
    'resources/views/welcome.blade.php' => [
        'name="viewport"',
        '@media (max-width: 900px)',
        '@media (max-width: 640px)',
        '@media (max-width: 360px)',
    ],
    'resources/views/layouts/auth.blade.php' => [
        'name="viewport"',
        '@media (max-width: 760px)',
        '@media (max-width: 380px)',
    ],
    'resources/views/admin/layout.blade.php' => [
        'name="viewport"',
        '@media (max-width: 1100px)',
        '@media (max-width: 860px)',
        '@media (max-width: 760px)',
    ],
    'resources/views/admin/system-operations.blade.php' => [
        '@media (max-width: 760px)',
        '@media (max-width: 390px)',
    ],
    'resources/views/user/partials/navigation.blade.php' => [
        '@media (max-width: 860px)',
        '@media (max-width: 560px)',
        '@media (max-width: 390px)',
    ],
    'resources/views/user/partials/theme.blade.php' => [
        '@media (max-width: 1024px)',
        '@media (max-width: 640px)',
        '@media (max-width: 390px)',
    ],
    'resources/views/user/messenger_new.blade.php' => [
        'name="viewport"',
        '@media (max-width: 1024px)',
        '@media (max-width: 700px)',
        '@media (max-width: 380px)',
        'max-width:calc(100% - 40px)',
    ],
];

$failures = [];
foreach ($requirements as $relativePath => $needles) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $contents = is_file($path) ? file_get_contents($path) : false;
    if ($contents === false) {
        $failures[] = "{$relativePath}: file missing";

        continue;
    }

    foreach ($needles as $needle) {
        if (! str_contains($contents, $needle)) {
            $failures[] = "{$relativePath}: missing {$needle}";
        }
    }
}

if ($failures) {
    fwrite(STDERR, "Responsive source audit failed:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo 'Responsive source audit passed for '
    .count($requirements)
    ." shared/public interfaces.\n";
