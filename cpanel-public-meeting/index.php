<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Laravel root: /home/USER/dop_meet (two levels up from public_html/meeting)
$laravelRoot = dirname(__DIR__, 2).'/dop_meet';

if (file_exists($maintenance = $laravelRoot.'/storage/framework/maintenance.php')) {
    require $maintenance;
}

require $laravelRoot.'/vendor/autoload.php';

/** @var Application $app */
$app = require_once $laravelRoot.'/bootstrap/app.php';

// Public files (index.php, build/, .htaccess) live in public_html/meeting/
$app->usePublicPath(__DIR__);

$app->handleRequest(Request::capture());
