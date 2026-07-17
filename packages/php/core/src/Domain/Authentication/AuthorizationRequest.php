<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use OidcClient\Domain\PKCE\PKCEPair;

final class AuthorizationRequest
{
    public function __construct(
        private readonly string $clientId,
        private readonly string $redirectUri,
        private readonly string $scope,
        private readonly string $state,
        private readonly PKCEPair $pkce,
        private readonly array $additionalParameters = []
    ) {
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    public function scope(): string
    {
        return $this->scope;
    }

    public function state(): string
    {
        return $this->state;
    }

    public function pkce(): PKCEPair
    {
        return $this->pkce;
    }

    /**
     * @return array<string,mixed>
     */
    public function additionalParameters(): array
    {
        return $this->additionalParameters;
    }
}