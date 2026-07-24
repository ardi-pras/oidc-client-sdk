<?php

declare(strict_types=1);

namespace OidcClient\Domain\Jwt;

final class JwkSet
{
    private $keys;

    /**
     * @param Jwk[] $keys
     */
    public function __construct(array $keys)
    {
        $this->keys = $keys;
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
