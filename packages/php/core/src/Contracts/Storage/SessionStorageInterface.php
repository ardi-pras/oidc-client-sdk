<?php

namespace OidcClient\Contracts\Storage;

interface SessionStorageInterface
{
    public function set(string $key, $value): void;

    public function get(string $key, $default = null);

    public function has(string $key): bool;

    public function remove(string $key): void;

    public function clear(): void;
}