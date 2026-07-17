<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\DecodedJwt;
use RuntimeException;

final class ExpirationRule
    implements JwtRuleInterface
{
    public function validate(
        DecodedJwt $jwt
    ): void {

        $exp = $jwt
            ->payload()
            ->expiration();

        if (
            $exp === null
        ) {

            throw new RuntimeException(
                'exp claim missing.'
            );

        }

        if (
            time() >= $exp
        ) {

            throw new RuntimeException(
                'ID Token expired.'
            );

        }
    }
}