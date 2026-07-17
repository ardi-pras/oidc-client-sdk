<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Config\OidcConfiguration;
use OidcClient\Domain\Jwt\DecodedJwt;
use RuntimeException;

final class AudienceRule implements JwtRuleInterface
{
    public function __construct(
        private readonly OidcConfiguration $config
    ) {
    }

    public function validate(
        DecodedJwt $jwt
    ): void {

        $audience = $jwt
            ->payload()
            ->audience();

        $audience = is_array($audience)
            ? $audience
            : [$audience];

        if (
            !in_array(
                $this->config->clientId(),
                $audience,
                true
            )
        ) {

            throw new RuntimeException(
                'Invalid audience.'
            );

        }
    }
}