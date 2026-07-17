<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Repository;

use OidcClient\Domain\Discovery\DiscoveryDocument;

interface DiscoveryRepositoryInterface
{
    public function discover(
        string $issuer
    ): DiscoveryDocument;
}