<?php

declare(strict_types=1);

namespace OidcClient\Domain\Endpoint;

use InvalidArgumentException;

final class Endpoint
{
    public function __construct(
        private readonly string $url
    ) {
        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            throw new InvalidArgumentException(
                sprintf('Invalid endpoint URL: %s', $url)
            );
        }
    }

    /**
     * Get endpoint URL.
     */
    public function url(): string
    {
        return $this->url;
    }

    /**
     * Get endpoint host.
     */
    public function host(): string
    {
        return parse_url($this->url, PHP_URL_HOST) ?? '';
    }

    /**
     * Get endpoint scheme.
     */
    public function scheme(): string
    {
        return parse_url($this->url, PHP_URL_SCHEME) ?? '';
    }

    /**
     * Get endpoint path.
     */
    public function path(): string
    {
        return parse_url($this->url, PHP_URL_PATH) ?? '';
    }

    /**
     * Compare with another endpoint.
     */
    public function equals(Endpoint $endpoint): bool
    {
        return $this->url === $endpoint->url();
    }

    /**
     * String representation.
     */
    public function __toString(): string
    {
        return $this->url;
    }
}