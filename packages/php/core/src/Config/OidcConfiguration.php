<?php

declare(strict_types=1);

namespace OidcClient\Config;

final class OidcConfiguration
{
    public function __construct(

        private readonly ?string $issuer,

        private readonly string $clientId,

        private readonly string $clientSecret,

        private readonly string $redirectUri,

        private readonly array $scopes,

        private readonly ?string $authorizationEndpoint = null,

        private readonly ?string $tokenEndpoint = null,

        private readonly ?string $userinfoEndpoint = null,

        private readonly ?string $jwksUri = null,

        private readonly ?string $logoutEndpoint = null,

        private readonly bool $verifyTls = true,
    ) {
    }

    public function issuer(): ?string
    {
        return $this->issuer;
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function clientSecret(): string
    {
        return $this->clientSecret;
    }

    public function redirectUri(): string
    {
        return $this->redirectUri;
    }

    public function scopes(): array
    {
        return $this->scopes;
    }

    public function authorizationEndpoint(): ?string
    {
        return $this->authorizationEndpoint;
    }

    public function tokenEndpoint(): ?string
    {
        return $this->tokenEndpoint;
    }

    public function userinfoEndpoint(): ?string
    {
        return $this->userinfoEndpoint;
    }

    public function jwksUri(): ?string
    {
        return $this->jwksUri;
    }

    public function logoutEndpoint(): ?string
    {
        return $this->logoutEndpoint;
    }

    public function verifyTls(): bool
    {
        return $this->verifyTls;
    }

    public function withTlsVerification(bool $verify): self
    {
        return new self(
            issuer: $this->issuer,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            redirectUri: $this->redirectUri,
            scopes: $this->scopes,
            authorizationEndpoint: $this->authorizationEndpoint,
            tokenEndpoint: $this->tokenEndpoint,
            userinfoEndpoint: $this->userinfoEndpoint,
            jwksUri: $this->jwksUri,
            logoutEndpoint: $this->logoutEndpoint,
            verifyTls: $verify
        );
    }

    public function withEndpoints(
        string $authorizationEndpoint,
        string $tokenEndpoint,
        ?string $userinfoEndpoint = null,
        ?string $jwksUri = null,
        ?string $logoutEndpoint = null
    ): self {
        return new self(
            issuer: $this->issuer,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            redirectUri: $this->redirectUri,
            scopes: $this->scopes,
            authorizationEndpoint: $authorizationEndpoint,
            tokenEndpoint: $tokenEndpoint,
            userinfoEndpoint: $userinfoEndpoint,
            jwksUri: $jwksUri,
            logoutEndpoint: $logoutEndpoint,
            verifyTls: $this->verifyTls
        );
    }
}