<?php

declare(strict_types=1);

namespace OidcClient\Application\Token;

use OidcClient\Contracts\Repository\TokenRepositoryInterface;
use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Domain\Token\Token;
use RuntimeException;

final class TokenService
{

    public function __construct(
        private readonly TokenRepositoryInterface $repository
    ) {
    }

    public function exchangeAuthorizationCode(
        AuthorizationResponse $response,
        AuthorizationContext $context
    ): Token {

        if ($response->hasError()) {

            throw new RuntimeException(
                $response->errorDescription()
                    ?? 'Authorization failed.'
            );

        }

        if (!$response->hasCode()) {
            throw new RuntimeException('Missing authorization code.');
        }

        return $this->repository->exchangeAuthorizationCode($response, $context);
    }

    public function refreshToken(
        string $refreshToken,
        AuthorizationContext $context
    ): Token {

        if (trim($refreshToken) === '') {
            throw new RuntimeException('Refresh token is empty.');
        }

        return $this->repository->refreshToken($refreshToken, $context);
    }

}