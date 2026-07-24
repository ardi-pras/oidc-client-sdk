<?php

declare(strict_types=1);

namespace OidcClient\Domain\User;

use ArrayIterator;
use IteratorAggregate;
use JsonSerializable;
use Traversable;

final class Claims implements IteratorAggregate, JsonSerializable
{
    private $claims;

    /**
     * @param array<string,mixed> $claims
     */
    public function __construct(array $claims)
    {
        $this->claims = $claims;
    }

    /**
     * Get all claims.
     *
     * @return array<string,mixed>
     */
    public function all(): array
    {
        return $this->claims;
    }

    /**
     * Get claim by key.
     */
    public function get(
        string $key,
        $default = null
    ) {
        return $this->claims[$key] ?? $default;
    }

    /**
     * Check claim exists.
     */
    public function has(string $key): bool
    {
        return array_key_exists(
            $key,
            $this->claims
        );
    }

    public function subject(): ?string
    {
        return $this->get('sub');
    }

    public function issuer(): ?string
    {
        return $this->get('iss');
    }

    public function audience()
    {
        return $this->get('aud');
    }

    public function email(): ?string
    {
        return $this->get('email');
    }

    public function username(): ?string
    {
        return $this->get('preferred_username')
            ?? $this->get('username');
    }

    public function name(): ?string
    {
        return $this->get('name');
    }

    public function givenName(): ?string
    {
        return $this->get('given_name');
    }

    public function familyName(): ?string
    {
        return $this->get('family_name');
    }

    public function issuedAt(): ?int
    {
        return $this->get('iat');
    }

    public function expiresAt(): ?int
    {
        return $this->get('exp');
    }

    public function notBefore(): ?int
    {
        return $this->get('nbf');
    }

    /**
     * Returns true if the token has expired.
     */
    public function isExpired(): bool
    {
        $exp = $this->expiresAt();

        return $exp !== null && $exp <= time();
    }

    /**
     * Returns remaining lifetime in seconds.
     */
    public function expiresIn(): ?int
    {
        $exp = $this->expiresAt();

        if ($exp === null) {
            return null;
        }

        return max(0, $exp - time());
    }

    /**
     * Create a new Claims instance with additional values.
     *
     * @param array<string,mixed> $claims
     */
    public function merge(array $claims): self
    {
        return new self(
            array_merge(
                $this->claims,
                $claims
            )
        );
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator(
            $this->claims
        );
    }

    public function jsonSerialize(): array
    {
        return $this->claims;
    }
}
