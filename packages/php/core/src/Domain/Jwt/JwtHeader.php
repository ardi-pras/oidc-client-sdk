<?php 

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class JwtHeader
{
    public function __construct(

        private readonly string $alg,

        private readonly string $typ,

        private readonly ?string $kid

    ) {}

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