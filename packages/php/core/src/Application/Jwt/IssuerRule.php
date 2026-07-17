<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Domain\Jwt\DecodedJwt;
use RuntimeException;

final class IssuerRule implements JwtRuleInterface
{
    public function __construct(
        private readonly OidcConfiguration $config
    ) {
    }

    public function validate(
        DecodedJwt $jwt
    ): void {

        if (
            $jwt
                ->payload()
                ->issuer()

            !==

            $this->config
                ->issuer()
        ) {

            throw new RuntimeException(
                'Invalid issuer.'
            );

        }

    }
}