<?php

declare(strict_types=1);

namespace OidcClient\Domain\Discovery;

final class DiscoveryDocument
{
    public function __construct(
        private readonly string $authorizationEndpoint,
        private readonly string $tokenEndpoint,
        private readonly ?string $userinfoEndpoint,
        private readonly ?string $jwksUri,
        private readonly ?string $endSessionEndpoint
    ) {
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