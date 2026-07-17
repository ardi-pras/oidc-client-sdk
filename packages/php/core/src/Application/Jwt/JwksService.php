<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\JwkSet;
use OidcClient\Infrastructure\Repository\HttpJwksRepository;

final class JwksService
{
    public function __construct(
        private readonly HttpJwksRepository $repository
    ) {
    }

    public function getJwks(string $jwksUri): JwkSet
    {
        return $this->repository->getJwks($jwksUri);
    }
}
