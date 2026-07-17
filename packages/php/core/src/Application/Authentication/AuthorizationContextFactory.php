<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Support\NonceGenerator;
use OidcClient\Support\PKCEGenerator;

final class AuthorizationContextFactory
{
    public function create(): AuthorizationContext
    {
        return new AuthorizationContext(
            PKCEGenerator::generate(),
            NonceGenerator::generate(),
            bin2hex(random_bytes(32)),
            time(),
            '',
            '',
            ''
        );
    }
}
