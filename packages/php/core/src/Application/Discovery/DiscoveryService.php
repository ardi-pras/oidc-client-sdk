<?php

declare(strict_types=1);

namespace OidcClient\Application\Discovery;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Contracts\Repository\DiscoveryRepositoryInterface;

final class DiscoveryService
{
    private $repository;

    public function __construct(
        DiscoveryRepositoryInterface $repository
    ) {
        $this->repository = $repository;
    }

    public function discover(
        OidcConfiguration $configuration
    ): OidcConfiguration {

        $metadata = $this->repository
            ->discover(
                $configuration->issuer()
            );

        return $configuration->withEndpoints(

            authorizationEndpoint:
            $metadata->authorizationEndpoint(),

            tokenEndpoint:
            $metadata->tokenEndpoint(),

            userinfoEndpoint:
            $metadata->userinfoEndpoint(),

            jwksUri:
            $metadata->jwksUri(),

            endSessionEndpoint:
            $metadata->endSessionEndpoint()

        );
    }
}
