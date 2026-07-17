<?php

return [
    'client_id' => 'barcodeaset',
    'client_secret' => 'barcodeaset09213',
    'redirect_uri' => 'https://barcodeaset.unisayogya.ac.id/sso-login',
    'authorization_endpoint' => 'https://service.unisayogya.ac.id/sso/authorize.php',
    'token_endpoint' => 'https://service.unisayogya.ac.id/sso/token.php',
    'userinfo_endpoint' => 'https://service.unisayogya.ac.id/sso/userinfo.php',
    'jwks_uri' => 'https://service.unisayogya.ac.id/sso/jwks.php',
    'logout_endpoint' => 'https://service.unisayogya.ac.id/sso/logout.php',
    'scope' => [
        'openid',
        'profile',
        'email'
    ]

];