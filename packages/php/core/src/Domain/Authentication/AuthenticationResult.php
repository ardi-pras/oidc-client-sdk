<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use OidcClient\Domain\Token\Token;
use OidcClient\Domain\User\User;

final class AuthenticationResult
{
    private function __construct(
        private readonly bool $authenticated,
        private readonly ?User $user = null,
        private readonly ?Token $token = null,
        private readonly ?string $error = null
    ) {
    }

    public static function success(
        User $user,
        Token $token
    ): self {
        return new self(
            authenticated: true,
            user: $user,
            token: $token
        );
    }

    public static function failed(
        string $message
    ): self {
        return new self(
            authenticated: false,
            error: $message
        );
    }

    public function isAuthenticated(): bool
    {
        return $this->authenticated;
    }

    public function user(): ?User
    {
        return $this->user;
    }

    public function token(): ?Token
    {
        return $this->token;
    }

    public function error(): ?string
    {
        return $this->error;
    }
}