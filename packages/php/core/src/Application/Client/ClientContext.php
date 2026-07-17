<?php

declare(strict_types=1);

namespace OidcClient\Application\Client;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Application\Authentication\AuthenticationService;
use OidcClient\Application\Discovery\DiscoveryService;
use OidcClient\Application\Token\TokenService;

final class ClientContext
{
    public function __construct(
        private readonly OidcConfiguration $configuration,
        private readonly AuthenticationService $authentication,
        private readonly DiscoveryService $discovery,
        private readonly TokenService $token
    ) {
    }

    public function configuration(): OidcConfiguration
    {
        return $this->configuration;
    }

    public function authentication(): AuthenticationService
    {
        return $this->authentication;
    }

    public function discovery(): DiscoveryService
    {
        return $this->discovery;
    }

    public function token(): TokenService
    {
        return $this->token;
    }
}