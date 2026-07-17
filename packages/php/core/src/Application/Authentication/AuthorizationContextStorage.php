<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Domain\Authentication\AuthorizationContext;

final class AuthorizationContextStorage
{
    private const SESSION_KEY = 'oidc.authorization';

    public function __construct(
        private readonly SessionStorageInterface $storage
    ) {
    }

    public function save(
        AuthorizationContext $context
    ): void {
        $this->storage->set(
            self::SESSION_KEY,
            serialize($context)
        );
    }

    public function load(): ?AuthorizationContext
    {
        $value = $this->storage->get(
            self::SESSION_KEY
        );

        if ($value === null) {
            return null;
        }

        $context = unserialize($value);

        return $context instanceof AuthorizationContext
            ? $context
            : null;
    }

    public function clear(): void
    {
        $this->storage->remove(
            self::SESSION_KEY
        );
    }
}
