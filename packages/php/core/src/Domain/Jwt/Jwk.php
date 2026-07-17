<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class Jwk
{
    public function __construct(
        private readonly string $kid,
        private readonly string $kty,
        private readonly string $alg,
        private readonly string $use,
        private readonly string $n,
        private readonly string $e
    ) {
    }

    public function kid(): string
    {
        return $this->kid;
    }

    public function keyType(): string
    {
        return $this->kty;
    }

    public function algorithm(): string
    {
        return $this->alg;
    }

    public function use(): string
    {
        return $this->use;
    }

    public function modulus(): string
    {
        return $this->n;
    }

    public function exponent(): string
    {
        return $this->e;
    }
}