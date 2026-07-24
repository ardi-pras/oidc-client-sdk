<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\JwkSet;
use OidcClient\Infrastructure\Repository\HttpJwksRepository;

final class JwksService
{
    private $repository;

    public function __construct(HttpJwksRepository $repository)
    {
        $this->repository = $repository;
    }

    public function getJwks(string $jwksUri): JwkSet
    {
        return $this->repository->getJwks($jwksUri);
    }
}
