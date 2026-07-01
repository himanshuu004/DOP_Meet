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

function deploy_run(string $command): void
{
    echo '<div class="cmd">$ '.htmlspecialchars($command).'</div><pre>';

    $output = [];
    $code = 0;
    exec('cd '.escapeshellarg(deploy_base_path()).' && '.$command.' 2>&1', $output, $code);

    echo htmlspecialchars(implode("\n", $output));

    if ($code !== 0) {
        echo "\n<span class=\"err\">Exit code: {$code}</span>";
    }

    echo '</pre>';
}

function deploy_finish(): void
{
    echo '<p>Done.</p></body></html>';
}
