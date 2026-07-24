<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Domain\PKCE\PKCEGenerator;

final class AuthorizationService
{
    private $config;

    private $session;

    public function __construct(
        OidcConfiguration $config,
        SessionStorageInterface $session
    ) {
        $this->config = $config;
        $this->session = $session;
    }

    public function buildAuthorizationUrl(): string
    {
        $state = bin2hex(random_bytes(16));
        $nonce = bin2hex(random_bytes(16));
        $pkce = PKCEGenerator::generate();

        $this->session->set('oidc.authorization_context', [
            'pkce' => $pkce,
            'state' => $state,
            'nonce' => $nonce,
            'created_at' => time(),
            'client_id' => $this->config->clientId(),
            'redirect_uri' => $this->config->redirectUri(),
            'token_endpoint' => $this->config->tokenEndpoint(),
            'client_secret' => $this->config->clientSecret()
        ]);

        return $this->config->authorizationEndpoint()
            . '?'
            . http_build_query([
                'client_id' => $this->config->clientId(),
                'redirect_uri' => $this->config->redirectUri(),
                'response_type' => 'code',
                'scope' => implode(' ', $this->config->scopes()),
                'state' => $state,
                'nonce' => $nonce,
                'code_challenge' => $pkce->challenge(),
                'code_challenge_method' => 'S256'
            ]);
    }
}
