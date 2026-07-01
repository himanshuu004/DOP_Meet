<?php

/**
 * Shared helpers for deploy/setup scripts (standalone, outside Laravel).
 */
function deploy_base_path(): string
{
    return dirname(__DIR__);
}

function deploy_read_env(?string $key = null, ?string $default = null): ?string
{
    static $env = null;

    if ($env === null) {
        $env = [];
        $path = deploy_base_path().'/.env';

        if (is_readable($path)) {
            foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
                $line = trim($line);

                if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                    continue;
                }

                [$name, $value] = explode('=', $line, 2);
                $env[trim($name)] = trim($value, " \t\n\r\0\x0B\"'");
            }
        }
    }

    if ($key === null) {
        return null;
    }

    return $env[$key] ?? $default;
}

function deploy_check_secret(): void
{
    $expected = deploy_read_env('DEPLOY_SECRET', 'meeting123');
    $provided = $_GET['key'] ?? '';

    if (! hash_equals((string) $expected, (string) $provided)) {
        http_response_code(403);
        echo '<h1>403 Forbidden</h1><p>Invalid deploy key.</p>';
        exit;
    }
}

function deploy_output(string $title): void
{
    header('Content-Type: text/html; charset=utf-8');
    echo '<!DOCTYPE html><html><head><meta charset="utf-8"><title>'.htmlspecialchars($title).'</title>';
    echo '<style>body{font-family:monospace;background:#111;color:#0f0;padding:20px}pre{white-space:pre-wrap;word-wrap:break-word}.cmd{color:#ff0;margin-top:1em}.err{color:#f55}</style></head><body>';
    echo '<h1>'.htmlspecialchars($title).'</h1>';
}

function deploy_shell_prefix(): string
{
    $home = deploy_base_path();

    return 'export HOME='.escapeshellarg($home)
        .' && export COMPOSER_HOME='.escapeshellarg($home.'/.composer')
        .' && ';
}

function deploy_run(string $command, bool $optional = false): void
{
    echo '<div class="cmd">$ '.htmlspecialchars($command).'</div><pre>';

    $output = [];
    $code = 0;
    exec('cd '.escapeshellarg(deploy_base_path()).' && '.deploy_shell_prefix().$command.' 2>&1', $output, $code);

    echo htmlspecialchars(implode("\n", $output));

    if ($code !== 0) {
        if ($optional) {
            echo "\n<span class=\"err\">Skipped (optional step failed, exit {$code})</span>";
        } else {
            echo "\n<span class=\"err\">Exit code: {$code}</span>";
        }
    }

    echo '</pre>';
}

function deploy_git_pull(string $branch = 'main'): void
{
    if (! is_dir(deploy_base_path().'/.git')) {
        deploy_sync_from_github(branch: $branch);

        return;
    }

    deploy_run('git pull origin '.$branch, optional: true);
}

function deploy_sync_from_github(string $repo = 'himanshuu004/DOP_Meet', string $branch = 'main'): void
{
    echo '<pre>[SYNC] Pulling latest code from GitHub ('.$repo.' @ '.$branch.')...</pre>';

    if (! class_exists(ZipArchive::class)) {
        echo '<pre><span class="err">[FAIL] ZipArchive PHP extension is required for GitHub sync.</span></pre>';

        return;
    }

    $base = deploy_base_path();
    $tmpDir = $base.'/storage/app/deploy-tmp';
    $zipPath = $tmpDir.'/repo.zip';

    if (is_dir($tmpDir)) {
        deploy_delete_directory($tmpDir);
    }
    mkdir($tmpDir, 0755, true);

    $zipUrl = 'https://github.com/'.$repo.'/archive/refs/heads/'.$branch.'.zip';
    $zipData = @file_get_contents($zipUrl);

    if ($zipData === false) {
        echo '<pre><span class="err">[FAIL] Could not download '.$zipUrl.'</span></pre>';

        return;
    }

    file_put_contents($zipPath, $zipData);

    $zip = new ZipArchive;
    if ($zip->open($zipPath) !== true) {
        echo '<pre><span class="err">[FAIL] Could not open downloaded zip.</span></pre>';

        return;
    }

    $zip->extractTo($tmpDir);
    $zip->close();
    unlink($zipPath);

    $extractedDirs = glob($tmpDir.'/*', GLOB_ONLYDIR);
    if ($extractedDirs === false || $extractedDirs === []) {
        echo '<pre><span class="err">[FAIL] Unexpected zip layout.</span></pre>';

        return;
    }

    $source = $extractedDirs[0];
    $skip = ['.env', '.git', 'vendor', 'node_modules', 'storage'];
    $copied = 0;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($source, FilesystemIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relative = substr($item->getPathname(), strlen($source) + 1);
        $top = explode('/', $relative)[0];

        if ($relative === '' || in_array($top, $skip, true)) {
            continue;
        }

        $target = $base.'/'.$relative;

        if ($item->isDir()) {
            if (! is_dir($target)) {
                mkdir($target, 0755, true);
            }

            continue;
        }

        $targetDir = dirname($target);
        if (! is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }

        if (copy($item->getPathname(), $target)) {
            $copied++;
        }
    }

    deploy_delete_directory($tmpDir);

    echo '<pre>[OK] Synced '.$copied.' files from GitHub.</pre>';

    $publicSource = $base.'/cpanel-public-meeting';
    $publicTarget = dirname($base).'/public_html/meeting';

    if (is_dir($publicSource) && is_dir($publicTarget)) {
        $publicCopied = 0;
        foreach (glob($publicSource.'/*') ?: [] as $item) {
            $name = basename($item);
            $target = $publicTarget.'/'.$name;

            if (is_dir($item)) {
                deploy_copy_directory($item, $target);
                $publicCopied++;
            } elseif (copy($item, $target)) {
                $publicCopied++;
            }
        }

        echo '<pre>[OK] Updated public_html/meeting/ ('.$publicCopied.' items).</pre>';
    }
}

function deploy_copy_directory(string $source, string $target): void
{
    if (! is_dir($target)) {
        mkdir($target, 0755, true);
    }

    foreach (glob($source.'/*') ?: [] as $item) {
        $name = basename($item);
        $dest = $target.'/'.$name;

        if (is_dir($item)) {
            deploy_copy_directory($item, $dest);
        } else {
            copy($item, $dest);
        }
    }
}

function deploy_delete_directory(string $dir): void
{
    if (! is_dir($dir)) {
        return;
    }

    foreach (glob($dir.'/*') ?: [] as $item) {
        if (is_dir($item)) {
            deploy_delete_directory($item);
        } else {
            unlink($item);
        }
    }

    rmdir($dir);
}

function deploy_composer_install(): void
{
    if (! is_dir(deploy_base_path().'/vendor')) {
        echo '<pre>[WARN] vendor/ folder missing — upload vendor.zip from your Mac before deploying.</pre>';
    }

    $composer = file_exists(deploy_base_path().'/composer.phar')
        ? 'php composer.phar'
        : 'composer';

    deploy_run($composer.' install --no-dev --optimize-autoloader --no-interaction', optional: true);
}

function deploy_ensure_app_key_line(): void
{
    $envPath = deploy_base_path().'/.env';

    if (! is_readable($envPath) || ! is_writable($envPath)) {
        echo '<pre>[WARN] Cannot update .env for APP_KEY (missing or not writable)</pre>';

        return;
    }

    $contents = file_get_contents($envPath);

    if (! preg_match('/^APP_KEY=/m', $contents)) {
        $contents = rtrim($contents)."\nAPP_KEY=\n";
        file_put_contents($envPath, $contents);
        echo '<pre>[OK] Added APP_KEY= to .env</pre>';
    }
}

function deploy_clear_bootstrap_cache(): void
{
    $cachePath = deploy_base_path().'/bootstrap/cache';
    $files = ['packages.php', 'services.php', 'config.php', 'routes-v7.php', 'events.php'];

    echo '<pre>';
    foreach ($files as $file) {
        $path = $cachePath.'/'.$file;
        if (is_file($path) && unlink($path)) {
            echo "[OK] Deleted bootstrap/cache/{$file}\n";
        }
    }
    echo "</pre>\n";
}

function deploy_finish(): void
{
    echo '<p>Done.</p></body></html>';
}
