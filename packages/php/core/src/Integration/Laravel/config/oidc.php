<?php

return [
    'issuer' => env('OIDC_ISSUER'),
    'client_id' => env('OIDC_CLIENT_ID'),
    'client_secret' => env('OIDC_CLIENT_SECRET'),
    'redirect_uri' => env('OIDC_REDIRECT_URI'),
    'scope' => ['openid', 'profile', 'email'],
    'verify_tls' => env('OIDC_VERIFY_TLS', true),
];
