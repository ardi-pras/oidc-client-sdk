<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class Jwk
{
    private $kid;

    private $kty;

    private $alg;

    private $use;

    private $n;

    private $e;

    public function __construct(
        string $kid,
        string $kty,
        string $alg,
        string $use,
        string $n,
        string $e
    ) {
        $this->kid = $kid;
        $this->kty = $kty;
        $this->alg = $alg;
        $this->use = $use;
        $this->n = $n;
        $this->e = $e;
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
