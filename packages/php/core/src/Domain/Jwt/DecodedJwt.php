<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class DecodedJwt
{
    public function __construct(
        private readonly JwtHeader $header,
        private readonly JwtPayload $payload,
        private readonly string $signature,
        private readonly string $signingInput
    ) {
    }

    public function header(): JwtHeader
    {
        return $this->header;
    }

    public function payload(): JwtPayload
    {
        return $this->payload;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function signingInput(): string
    {
        return $this->signingInput;
    }
}