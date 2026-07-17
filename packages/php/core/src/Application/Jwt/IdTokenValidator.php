<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\DecodedJwt;

final class IdTokenValidator
{
    /**
     * @param JwtRuleInterface[] $rules
     */
    public function __construct(
        private readonly array $rules
    ) {
    }

    public function validate(
        DecodedJwt $jwt
    ): void {

        foreach (
            $this->rules
            as
            $rule
        ) {

            $rule->validate(
                $jwt
            );

        }

    }
}