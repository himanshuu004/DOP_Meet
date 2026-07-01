<?php

require __DIR__.'/deploy-scripts/helpers.php';

deploy_check_secret();
deploy_output('DOP Meet — Deploy');

deploy_clear_bootstrap_cache();

deploy_git_pull();
deploy_composer_install();

deploy_run('php artisan migrate --force');
deploy_run('php artisan config:clear');
deploy_run('php artisan cache:clear');
deploy_run('php artisan view:clear');
deploy_run('php artisan route:clear');
deploy_run('php artisan config:cache');
deploy_run('php artisan route:cache');

echo '<pre>[INFO] Deploy complete. Code synced from GitHub, caches rebuilt.</pre>';

deploy_finish();
