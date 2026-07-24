<?php

declare(strict_types=1);

namespace OidcClient\Domain\Token;

use InvalidArgumentException;

final class Token
{
    private $accessToken;

    private $refreshToken;

    private $idToken;

    private $expiresAt;

    private $tokenType;

    private $scope;

    public function __construct(
        string $accessToken,
        ?string $refreshToken = null,
        ?string $idToken = null,
        ?int $expiresAt = null,
        $tokenType = null,
        ?string $scope = null
    ) {
        if ($accessToken === '') {
            throw new InvalidArgumentException(
                'Access token cannot be empty.'
            );
        }

        $this->accessToken = $accessToken;
        $this->refreshToken = $refreshToken;
        $this->idToken = $idToken;
        $this->expiresAt = $expiresAt;
        $this->tokenType = $tokenType instanceof TokenType
            ? $tokenType
            : TokenType::fromValue($tokenType ?: TokenType::Bearer);
        $this->scope = $scope;
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
            $this->tokenType->value(),
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
            $this->accessToken,
            $this->refreshToken,
            $this->idToken,
            $expiresAt,
            $this->tokenType,
            $this->scope
        );
    }
}
