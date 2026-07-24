<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use OidcClient\Domain\PKCE\PKCEPair;

final class AuthorizationRequest
{
    private $clientId;

    private $redirectUri;

    private $scope;

    private $state;

    private $pkce;

    private $additionalParameters;

    public function __construct(
        string $clientId,
        string $redirectUri,
        string $scope,
        string $state,
        PKCEPair $pkce,
        array $additionalParameters = []
    ) {
        $this->clientId = $clientId;
        $this->redirectUri = $redirectUri;
        $this->scope = $scope;
        $this->state = $state;
        $this->pkce = $pkce;
        $this->additionalParameters = $additionalParameters;
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
