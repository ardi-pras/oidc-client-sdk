<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Repository;

use OidcClient\Domain\Token\Token;
use OidcClient\Domain\User\User;

interface UserRepositoryInterface
{
    public function userInfo(
        Token $token
    ): User;
}