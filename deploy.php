<?php

require __DIR__.'/deploy-scripts/helpers.php';

deploy_check_secret();
deploy_output('DOP Meet — Deploy');

deploy_run('git pull origin main');

$composer = file_exists(deploy_base_path().'/composer.phar') ? 'php composer.phar' : 'composer';
deploy_run($composer.' install --no-dev --optimize-autoloader --no-interaction');

deploy_run('php artisan migrate --force');
deploy_run('php artisan config:clear');
deploy_run('php artisan cache:clear');
deploy_run('php artisan view:clear');
deploy_run('php artisan route:clear');
deploy_run('php artisan config:cache');
deploy_run('php artisan route:cache');

deploy_finish();
