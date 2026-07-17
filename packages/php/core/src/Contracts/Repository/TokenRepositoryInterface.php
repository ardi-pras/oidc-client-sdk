<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Repository;

use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Domain\Token\Token;


interface TokenRepositoryInterface
{

    /**
     * Exchange authorization code
     */
    public function exchangeAuthorizationCode(
        AuthorizationResponse $response,
        AuthorizationContext $context
    ): Token;

    /**
     * Refresh expired access token
     */
    public function refreshToken(
        string $refreshToken,
        AuthorizationContext $context
    ): Token;

}