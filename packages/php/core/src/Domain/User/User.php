<?php

declare(strict_types=1);

namespace OidcClient\Domain\User;

use InvalidArgumentException;

final class User
{
    private $claims;

    public function __construct(Claims $claims)
    {
        $this->claims = $claims;

        if ($claims->subject() === null) {
            throw new InvalidArgumentException(
                'User subject (sub) is required.'
            );
        }
    }

    /**
     * OpenID Subject Identifier
     */
    public function id(): string
    {
        return $this->claims->subject();
    }

    /**
     * Username
     */
    public function username(): ?string
    {
        return $this->claims->username();
    }

    /**
     * Display Name
     */
    public function name(): ?string
    {
        return $this->claims->name();
    }

    /**
     * Email
     */
    public function email(): ?string
    {
        return $this->claims->email();
    }

    /**
     * User Claims
     */
    public function claims(): Claims
    {
        return $this->claims;
    }

    /**
     * Check claim existence.
     */
    public function hasClaim(string $key): bool
    {
        return $this->claims->has($key);
    }

    /**
     * Read custom claim.
     */
    public function claim(
        string $key,
        $default = null
    ) {
        return $this->claims->get(
            $key,
            $default
        );
    }

    /**
     * Create a new immutable User with merged claims.
     *
     * @param array<string,mixed> $claims
     */
    public function withClaims(array $claims): self
    {
        return new self(
            $this->claims->merge($claims)
        );
    }

    /**
     * Compare two users.
     */
    public function equals(User $user): bool
    {
        return $this->id() === $user->id();
    }
}
