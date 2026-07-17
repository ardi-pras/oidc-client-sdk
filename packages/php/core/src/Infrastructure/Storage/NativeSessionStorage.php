<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Storage;

use OidcClient\Contracts\Storage\SessionStorageInterface;

final class NativeSessionStorage implements SessionStorageInterface
{
    public function __construct()
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start();
        }
    }

    /**
     * API baru
     */
    public function set(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    /**
     * Alias API lama
     */
    public function put(string $key, mixed $value): void
    {
        $this->set($key, $value);
    }

    public function get(
        string $key,
        mixed $default = null
    ): mixed {
        return $_SESSION[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $_SESSION);
    }

    public function remove(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Alias API lama
     */
    public function forget(string $key): void
    {
        $this->remove($key);
    }

    public function clear(): void
    {
        $_SESSION = [];
    }

    /**
     * Alias API lama
     */
    public function destroy(): void
    {
        $this->clear();
    }
}