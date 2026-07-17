<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class JwkSet
{
    /**
     * @param Jwk[] $keys
     */
    public function __construct(
        private readonly array $keys
    ) {
    }

    /**
     * @return Jwk[]
     */
    public function keys(): array
    {
        return $this->keys;
    }

    public function findByKid(string $kid): ?Jwk
    {
        foreach ($this->keys as $key) {

            if ($key->kid() === $kid) {
                return $key;
            }

        }

        return null;
    }
}