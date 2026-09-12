<?php

/**
 * Accessibility source audit.
 *
 * Static source-level checks that lock in keyboard navigation and focus
 * accessibility requirements across shared layouts and partials. Complements
 * runtime axe-core checks (see docs/operations.md) with fast, dependency-free
 * verification of the structural building blocks every page relies on.
 */
$root = dirname(__DIR__, 2);

$requirements = [
    // Shared layouts
    'resources/views/layouts/app.blade.php' => [
        'id="mainContent"',
        'tabindex="-1"',
        'a:focus-visible',
    ],
    'resources/views/layouts/auth.blade.php' => [
        'id="mainContent"',
        'tabindex="-1"',
        'a:focus-visible',
        "include('partials.public-header')",
    ],
    'resources/views/layouts/navbar.blade.php' => [
        'app-skip-link',
        'aria-expanded',
        'aria-controls',
        'aria-label="Notifications"',
        'a:focus-visible',
    ],
    'resources/views/admin/layout.blade.php' => [
        'class="skip-link"',
        'id="main-content"',
        'tabindex="-1"',
        'a:focus-visible',
        'prefers-reduced-motion',
    ],
    'resources/views/partials/header.blade.php' => [
        'href="#mainContent"',
        'href="#clientNavMenu"',
        'class="skip-link"',
    ],
    'resources/views/partials/public-header.blade.php' => [
        'href="#mainContent"',
        'public-skip-link',
    ],
    'resources/views/user/partials/navigation.blade.php' => [
        'id="clientNavMenu"',
        'tabindex="-1"',
        'aria-expanded',
        'aria-controls',
        "event.key === 'Escape'",
    ],
    'resources/views/user/partials/theme.blade.php' => [
        'a:focus-visible',
        'prefers-reduced-motion',
    ],
    'resources/views/welcome.blade.php' => [
        'id="mainContent"',
        'tabindex="-1"',
        'a:focus-visible',
    ],
];

// Every client-facing page must anchor its main landmark for skip links.
$clientPages = [
    'resources/views/user/dashboard.blade.php',
    'resources/views/user/messenger_new.blade.php',
    'resources/views/user/notifications.blade.php',
    'resources/views/user/request-create.blade.php',
    'resources/views/user/request-details-new.blade.php',
    'resources/views/user/requests/history-new.blade.php',
    'resources/views/user/security.blade.php',
    'resources/views/user/profile.blade.php',
    'resources/views/user/settings.blade.php',
    'resources/views/user/2fa-recovery-codes.blade.php',
    'resources/views/mis/incident-reports/create.blade.php',
    'resources/views/mis/incident-reports/index.blade.php',
    'resources/views/mis/incident-reports/show.blade.php',
    'resources/views/mis/pm-checklists/create.blade.php',
    'resources/views/mis/pm-checklists/index.blade.php',
    'resources/views/mis/pm-checklists/show.blade.php',
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

foreach ($clientPages as $relativePath) {
    $path = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $relativePath);
    $contents = is_file($path) ? file_get_contents($path) : false;
    if ($contents === false) {
        $failures[] = "{$relativePath}: file missing";

        continue;
    }

    if (! str_contains($contents, 'id="mainContent"') || ! str_contains($contents, 'tabindex="-1"')) {
        $failures[] = "{$relativePath}: main landmark must have id=\"mainContent\" tabindex=\"-1\"";
    }
}

if ($failures) {
    fwrite(STDERR, "Accessibility source audit failed:\n- ".implode("\n- ", $failures)."\n");
    exit(1);
}

echo 'Accessibility source audit passed for '
    .count($requirements).' shared layouts and '
    .count($clientPages)." client pages.\n";
