<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Mapper;

use OidcClient\Domain\Jwt\DecodedJwt;
use OidcClient\Domain\User\User;
use OidcClient\Domain\User\Claims;

final class UserMapper
{
    public function map(
        DecodedJwt $jwt
    ): User {

        $claimsArray = $jwt
            ->payload()
            ->all();

        return new User(
            new Claims($claimsArray)
        );

    }
}