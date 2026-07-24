<?php

declare(strict_types=1);

namespace OidcClient\Application\Client;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Application\Authentication\AuthenticationService;
use OidcClient\Application\Discovery\DiscoveryService;
use OidcClient\Application\Token\TokenService;

final class ClientContext
{
    private $configuration;

    private $authentication;

    private $discovery;

    private $token;

    public function __construct(
        OidcConfiguration $configuration,
        AuthenticationService $authentication,
        DiscoveryService $discovery,
        TokenService $token
    ) {
        $this->configuration = $configuration;
        $this->authentication = $authentication;
        $this->discovery = $discovery;
        $this->token = $token;
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
