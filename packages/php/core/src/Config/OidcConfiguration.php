<?php

declare(strict_types=1);

namespace OidcClient\Config;

final class OidcConfiguration
{
    private $issuer;

    private $clientId;

    private $clientSecret;

    private $redirectUri;

    private $scopes;

    private $authorizationEndpoint;

    private $tokenEndpoint;

    private $userinfoEndpoint;

    private $jwksUri;

    private $logoutEndpoint;

    private $verifyTls;

    public function __construct(
        ?string $issuer = null,
        string $clientId,
        string $clientSecret,
        string $redirectUri,
        array $scopes,
        ?string $authorizationEndpoint = null,
        ?string $tokenEndpoint = null,
        ?string $userinfoEndpoint = null,
        ?string $jwksUri = null,
        ?string $logoutEndpoint = null,
        bool $verifyTls = true
    ) {
        $this->issuer = $issuer;
        $this->clientId = $clientId;
        $this->clientSecret = $clientSecret;
        $this->redirectUri = $redirectUri;
        $this->scopes = $scopes;
        $this->authorizationEndpoint = $authorizationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->userinfoEndpoint = $userinfoEndpoint;
        $this->jwksUri = $jwksUri;
        $this->logoutEndpoint = $logoutEndpoint;
        $this->verifyTls = $verifyTls;
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
            $this->issuer,
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->scopes,
            $this->authorizationEndpoint,
            $this->tokenEndpoint,
            $this->userinfoEndpoint,
            $this->jwksUri,
            $this->logoutEndpoint,
            $verify
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
            $this->issuer,
            $this->clientId,
            $this->clientSecret,
            $this->redirectUri,
            $this->scopes,
            $authorizationEndpoint,
            $tokenEndpoint,
            $userinfoEndpoint,
            $jwksUri,
            $logoutEndpoint,
            $this->verifyTls
        );
    }
}
