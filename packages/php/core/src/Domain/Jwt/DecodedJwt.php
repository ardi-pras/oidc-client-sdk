<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class DecodedJwt
{
    private $header;

    private $payload;

    private $signature;

    private $signingInput;

    public function __construct(
        JwtHeader $header,
        JwtPayload $payload,
        string $signature,
        string $signingInput
    ) {
        $this->header = $header;
        $this->payload = $payload;
        $this->signature = $signature;
        $this->signingInput = $signingInput;
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
