<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Mapper;

use OidcClient\Domain\Token\Token;
use OidcClient\Domain\Token\TokenType;
use OidcClient\Exception\InvalidTokenResponseException;

final class TokenMapper
{
    /**
     * @param array<string,mixed> $data
     */
    public function map(array $data): Token
    {
        if (!isset($data['access_token'])) {
            throw new InvalidTokenResponseException(
                'Missing access_token.'
            );
        }

        $expiresAt = null;

        if (isset($data['expires_in'])) {
            $expiresAt = time() + (int) $data['expires_in'];
        }

        $tokenType = TokenType::Bearer;

        if (
            isset($data['token_type']) &&
            strtolower($data['token_type']) !== 'bearer'
        ) {
            throw new InvalidTokenResponseException(
                'Unsupported token type.'
            );
        }

        return new Token(

            accessToken:
                $data['access_token'],

            refreshToken:
                $data['refresh_token'] ?? null,

            idToken:
                $data['id_token'] ?? null,

            expiresAt:
                $expiresAt,

            tokenType:
                $tokenType,

            scope:
                $data['scope'] ?? null
        );
    }
}