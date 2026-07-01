<?php

require __DIR__.'/deploy-scripts/helpers.php';

deploy_check_secret();
deploy_output('DOP Meet — Server Setup');

$base = deploy_base_path();
$platformFile = $base.'/vendor/composer/platform_check.php';

echo '<pre>';
echo 'PHP version: '.PHP_VERSION."\n";
echo 'Base path: '.$base."\n\n";

if (is_readable($platformFile)) {
    $platformContents = file_get_contents($platformFile);
    if (str_contains($platformContents, '8.4.1')) {
        echo "[FAIL] OLD vendor folder detected (requires PHP 8.4).\n";
        echo "       You must DELETE vendor/ and upload the NEW vendor.zip from your Mac.\n\n";
    } elseif (str_contains($platformContents, '8.3.0')) {
        echo "[OK] vendor folder is compatible with PHP 8.3.\n\n";
    }
}

$checks = [
    $base.'/storage' => 'storage/',
    $base.'/storage/logs' => 'storage/logs/',
    $base.'/storage/framework' => 'storage/framework/',
    $base.'/bootstrap/cache' => 'bootstrap/cache/',
];

foreach ($checks as $path => $label) {
    $writable = is_dir($path) && is_writable($path);
    echo ($writable ? '[OK]' : '[FAIL]')." {$label} ".($writable ? 'writable' : 'NOT writable')."\n";
}

echo "\n.env file: ".(is_readable($base.'/.env') ? '[OK] exists' : '[FAIL] missing')."\n";

if (is_readable($base.'/.env')) {
    echo 'APP_KEY: '.(deploy_read_env('APP_KEY') ? '[OK] set' : '[FAIL] missing')."\n";
    echo 'DB_DATABASE: '.deploy_read_env('DB_DATABASE', '(not set)')."\n";
}

echo '</pre>';

if (is_readable($platformFile)) {
    $platformContents = file_get_contents($platformFile);
    if (str_contains($platformContents, '8.4.1')) {
        echo '<p style="color:#f55"><strong>Stop:</strong> Upload the new <code>vendor.zip</code> from your Mac first, then reload this page.</p>';
        deploy_finish();
        exit;
    }
}

deploy_clear_bootstrap_cache();
deploy_ensure_app_key_line();
deploy_run('php artisan config:clear');

if (! deploy_read_env('APP_KEY')) {
    deploy_run('php artisan key:generate --force');
}

deploy_run('php artisan migrate --force');
deploy_run('php artisan db:seed --force');
deploy_run('php artisan storage:link');
deploy_run('php artisan config:cache');
deploy_run('php artisan route:cache');

deploy_finish();
