<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use OidcClient\Domain\Token\Token;
use OidcClient\Domain\User\User;

final class AuthenticationResult
{
    private $authenticated;

    private $user;

    private $token;

    private $error;

    private function __construct(
        bool $authenticated,
        ?User $user = null,
        ?Token $token = null,
        ?string $error = null
    ) {
        $this->authenticated = $authenticated;
        $this->user = $user;
        $this->token = $token;
        $this->error = $error;
    }

    public static function success(
        User $user,
        Token $token
    ): self {
        return new self(
            true,
            $user,
            $token
        );
    }

    public static function failed(
        string $message
    ): self {
        return new self(
            false,
            null,
            null,
            $message
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
