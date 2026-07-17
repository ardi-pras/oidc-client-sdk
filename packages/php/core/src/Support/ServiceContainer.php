<?php

declare(strict_types=1);

namespace OidcClient\Support;

use RuntimeException;

final class ServiceContainer
{
    /**
     * @var array<class-string, object>
     */
    private array $services = [];

    public function set(
        string $id,
        object $service
    ): void {

        $this->services[$id] = $service;

    }

    public function get(
        string $id
    ): object {

        if (!isset($this->services[$id])) {

            throw new RuntimeException(
                "Service [$id] not found."
            );

        }

        return $this->services[$id];
    }

    public function has(
        string $id
    ): bool {

        return isset(
            $this->services[$id]
        );

    }
}