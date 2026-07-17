<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\DecodedJwt;
use RuntimeException;

final class IssuedAtRule
    implements JwtRuleInterface
{
    public function validate(
        DecodedJwt $jwt
    ): void {

        $iat = $jwt
            ->payload()
            ->issuedAt();

        if (
            $iat === null
        ) {

            throw new RuntimeException(
                'iat claim missing.'
            );

        }

        if (
            $iat > time() + 60
        ) {

            throw new RuntimeException(
                'Invalid iat.'
            );

        }
    }
}