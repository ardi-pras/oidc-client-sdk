<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class JwtPayload
{
    public function __construct(

        private readonly array $claims

    ) {}

    public function issuer(): ?string
    {
        return $this->claims["iss"] ?? null;
    }

    public function subject(): ?string
    {
        return $this->claims["sub"] ?? null;
    }

    public function audience(): mixed
    {
        return $this->claims["aud"] ?? null;
    }

    public function nonce(): ?string
    {
        return $this->claims["nonce"] ?? null;
    }

    public function expiration(): ?int
    {
        return $this->claims["exp"] ?? null;
    }

    public function issuedAt(): ?int
    {
        return $this->claims["iat"] ?? null;
    }

    public function all(): array
    {
        return $this->claims;
    }

    public function claim(string $name): mixed
    {
        return $this->claims[$name] ?? null;
    }
}