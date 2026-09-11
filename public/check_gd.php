<?php

use Dompdf\Dompdf;

// Diagnostic script to check GD extension availability
header('Content-Type: text/plain');

echo '=== PHP EXTENSION CHECK ==='.PHP_EOL;
echo 'GD Extension: '.(extension_loaded('gd') ? '✓ ENABLED' : '✗ DISABLED').PHP_EOL;
echo 'PHP Version: '.PHP_VERSION.PHP_EOL;
echo 'SAPI: '.php_sapi_name().PHP_EOL;
echo 'PHP INI: '.php_ini_loaded_file().PHP_EOL;
echo PHP_EOL;

echo '=== DOMPDF TEST ==='.PHP_EOL;

if (! extension_loaded('gd')) {
    echo '✗ ERROR: GD extension is not loaded!'.PHP_EOL;
    echo "Please check your php.ini file and ensure 'extension=gd' is uncommented.".PHP_EOL;
    exit(1);
}

try {
    require_once __DIR__.'/../vendor/autoload.php';

    $dompdf = new Dompdf;
    $dompdf->loadHtml('<h1>Test</h1>');
    $dompdf->setPaper('A4');
    $dompdf->render();

    echo '✓ DomPDF initialization: SUCCESS'.PHP_EOL;
    echo '✓ PDF can be generated successfully!'.PHP_EOL;
} catch (Exception $e) {
    echo '✗ DomPDF Error: '.$e->getMessage().PHP_EOL;
    exit(1);
}
