<?php

$envFile = __DIR__ . '/.env';
$env = [];

if (is_file($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (str_starts_with($line, '#')) {
            continue;
        }

        [$name, $value] = array_pad(explode('=', $line, 2), 2, '');
        $env[trim($name)] = trim($value);
    }
}

$scopes = array_filter(array_map('trim', explode(',', $env['SCOPE'] ?? 'openid,profile,email')));

return [
    'client_id' => $env['CLIENT_ID'] ?? '',
    'client_secret' => $env['CLIENT_SECRET'] ?? '',
    'redirect_uri' => $env['REDIRECT_URI'] ?? '',
    'authorization_endpoint' => $env['AUTHORIZATION_ENDPOINT'] ?? '',
    'token_endpoint' => $env['TOKEN_ENDPOINT'] ?? '',
    'userinfo_endpoint' => $env['USERINFO_ENDPOINT'] ?? '',
    'jwks_uri' => $env['JWKS_URI'] ?? '',
    'logout_endpoint' => $env['LOGOUT_ENDPOINT'] ?? '',
    'scope' => $scopes,
];
