<?php

declare(strict_types=1);

namespace OidcClient\Domain\Discovery;

final class DiscoveryDocument
{
    private $authorizationEndpoint;

    private $tokenEndpoint;

    private $userinfoEndpoint;

    private $jwksUri;

    private $endSessionEndpoint;

    public function __construct(
        string $authorizationEndpoint,
        string $tokenEndpoint,
        ?string $userinfoEndpoint,
        ?string $jwksUri,
        ?string $endSessionEndpoint
    ) {
        $this->authorizationEndpoint = $authorizationEndpoint;
        $this->tokenEndpoint = $tokenEndpoint;
        $this->userinfoEndpoint = $userinfoEndpoint;
        $this->jwksUri = $jwksUri;
        $this->endSessionEndpoint = $endSessionEndpoint;
    }

    public function authorizationEndpoint(): string
    {
        return $this->authorizationEndpoint;
    }

    public function tokenEndpoint(): string
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

    public function endSessionEndpoint(): ?string
    {
        return $this->endSessionEndpoint;
    }
}
