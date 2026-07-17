<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\DecodedJwt;

interface JwtRuleInterface
{
    public function validate(
        DecodedJwt $jwt
    ): void;
}