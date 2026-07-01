<?php

require __DIR__.'/deploy-scripts/helpers.php';

deploy_check_secret();
deploy_output('DOP Meet — Server Setup');

echo '<pre>';
echo 'PHP version: '.PHP_VERSION."\n";
echo 'Base path: '.deploy_base_path()."\n\n";

$checks = [
    deploy_base_path().'/storage' => 'storage/',
    deploy_base_path().'/storage/logs' => 'storage/logs/',
    deploy_base_path().'/storage/framework' => 'storage/framework/',
    deploy_base_path().'/bootstrap/cache' => 'bootstrap/cache/',
];

foreach ($checks as $path => $label) {
    $writable = is_dir($path) && is_writable($path);
    echo ($writable ? '[OK]' : '[FAIL]')." {$label} ".($writable ? 'writable' : 'NOT writable')."\n";
}

echo "\n.env file: ".(is_readable(deploy_base_path().'/.env') ? '[OK] exists' : '[FAIL] missing')."\n";

if (is_readable(deploy_base_path().'/.env')) {
    echo 'APP_KEY: '.(deploy_read_env('APP_KEY') ? '[OK] set' : '[FAIL] missing')."\n";
    echo 'DB_DATABASE: '.deploy_read_env('DB_DATABASE', '(not set)')."\n";
}

echo '</pre>';

deploy_run('php artisan migrate --force');
deploy_run('php artisan db:seed --force');
deploy_run('php artisan storage:link');
deploy_run('php artisan config:cache');
deploy_run('php artisan route:cache');

deploy_finish();
