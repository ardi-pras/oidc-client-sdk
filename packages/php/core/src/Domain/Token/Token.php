<?php

declare(strict_types=1);

namespace OidcClient\Domain\Token;

use InvalidArgumentException;

final class Token
{
    public function __construct(
        private readonly string $accessToken,
        private readonly ?string $refreshToken = null,
        private readonly ?string $idToken = null,
        private readonly ?int $expiresAt = null,
        private readonly TokenType $tokenType = TokenType::Bearer,
        private readonly ?string $scope = null
    ) {
        if ($accessToken === '') {
            throw new InvalidArgumentException(
                'Access token cannot be empty.'
            );
        }
    }

    public function accessToken(): string
    {
        return $this->accessToken;
    }

    public function refreshToken(): ?string
    {
        return $this->refreshToken;
    }

    public function idToken(): ?string
    {
        return $this->idToken;
    }

    public function tokenType(): TokenType
    {
        return $this->tokenType;
    }

    public function scope(): ?string
    {
        return $this->scope;
    }

    public function expiresAt(): ?int
    {
        return $this->expiresAt;
    }

    public function expiresIn(): ?int
    {
        if ($this->expiresAt === null) {
            return null;
        }

        return max(0, $this->expiresAt - time());
    }

    public function isExpired(): bool
    {
        if ($this->expiresAt === null) {
            return false;
        }

        return $this->expiresAt <= time();
    }

    public function hasRefreshToken(): bool
    {
        return !empty($this->refreshToken);
    }

    public function hasIdToken(): bool
    {
        return !empty($this->idToken);
    }

    public function bearer(): string
    {
        return sprintf(
            '%s %s',
            $this->tokenType->value,
            $this->accessToken
        );
    }

    public function equals(Token $token): bool
    {
        return hash_equals(
            $this->accessToken,
            $token->accessToken()
        );
    }

    public function withExpiresAt(int $expiresAt): self
    {
        return new self(
            accessToken: $this->accessToken,
            refreshToken: $this->refreshToken,
            idToken: $this->idToken,
            expiresAt: $expiresAt,
            tokenType: $this->tokenType,
            scope: $this->scope
        );
    }
}