<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class JwtHeader
{
    private $alg;

    private $typ;

    private $kid;

    public function __construct(
        string $alg,
        string $typ,
        ?string $kid
    ) {
        $this->alg = $alg;
        $this->typ = $typ;
        $this->kid = $kid;
    }

    public function algorithm(): string
    {
        return $this->alg;
    }

    public function type(): string
    {
        return $this->typ;
    }

    public function keyId(): ?string
    {
        return $this->kid;
    }

    public function kid(): ?string
    {
        return $this->kid;
    }
}
