<?php

declare(strict_types=1);

session_start();

require __DIR__.'/../../vendor/autoload.php';

$config = require __DIR__.'/config.php';

$builder = OidcClient\OidcClient::builder()
    ->clientId($config['client_id'])
    ->clientSecret($config['client_secret'])
    ->redirectUri($config['redirect_uri'])
    ->scope($config['scope'])
    ->verifyTls(false);

// Discovery Mode
if (!empty($config['issuer'])) {

    $builder->issuer($config['issuer']);

}
// Manual Endpoint Mode
else {

    $builder
        ->authorizationEndpoint($config['authorization_endpoint'])
        ->tokenEndpoint($config['token_endpoint']);

    if (!empty($config['userinfo_endpoint'])) {
        $builder->userinfoEndpoint($config['userinfo_endpoint']);
    }

    if (!empty($config['jwks_uri'])) {
        $builder->jwksUri($config['jwks_uri']);
    }

    if (!empty($config['logout_endpoint'])) {
        $builder->logoutEndpoint($config['logout_endpoint']);
    }
}

$oidc = $builder->build();