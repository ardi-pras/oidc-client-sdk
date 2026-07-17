<?php

declare(strict_types=1);

namespace OidcClient\Integration;

use OidcClient\OidcClient;

final class OidcService
{
    private OidcClient $client;

    public function __construct(array $config = [])
    {
        $this->client = OidcClient::builder()
            ->fromArray($config)
            ->build();
    }

    public function getClient(): OidcClient
    {
        return $this->client;
    }

    public function beginAuthentication(): string
    {
        return $this->client->authentication()->beginAuthentication();
    }

    public function authenticate(array $parameters)
    {
        return $this->client->authenticate($parameters);
    }

    public function logout(): void
    {
        $this->client->logout();
    }

    public function isAuthenticated(): bool
    {
        return method_exists($this->client, 'isAuthenticated') && $this->client->isAuthenticated();
    }

    public function user()
    {
        return method_exists($this->client, 'user') ? $this->client->user() : null;
    }

    public function token()
    {
        return method_exists($this->client, 'token') ? $this->client->token() : null;
    }
}
