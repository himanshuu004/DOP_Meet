<?php

require __DIR__.'/deploy-scripts/helpers.php';

deploy_check_secret();
deploy_output('DOP Meet — Git Init (one-time)');

$repo = $_GET['repo'] ?? 'https://github.com/himanshuu004/DOP_Meet.git';
$branch = $_GET['branch'] ?? 'main';

echo '<pre>';
echo "Repository: {$repo}\n";
echo "Branch: {$branch}\n\n";
echo '</pre>';

if (is_dir(deploy_base_path().'/.git')) {
    deploy_run('git remote -v');
    deploy_run('git pull origin '.$branch);
} else {
    deploy_run('git init');
    deploy_run('git remote add origin '.$repo);
    deploy_run('git fetch origin');
    deploy_run('git checkout -b '.$branch);
    deploy_run('git branch --set-upstream-to=origin/'.$branch.' '.$branch);
    deploy_run('git pull origin '.$branch);
}

if (! is_readable(deploy_base_path().'/.env') && is_readable(deploy_base_path().'/.env.example')) {
    copy(deploy_base_path().'/.env.example', deploy_base_path().'/.env');
    echo '<pre>[INFO] Created .env from .env.example — edit DB credentials before running setup.php</pre>';
}

$composer = file_exists(deploy_base_path().'/composer.phar') ? 'php composer.phar' : 'composer';
deploy_run($composer.' install --no-dev --optimize-autoloader --no-interaction');

if (! deploy_read_env('APP_KEY')) {
    deploy_run('php artisan key:generate --force');
}

deploy_finish();
