<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Http;

final class HttpRequest
{
    public function __construct(
        private readonly string $method,
        private readonly string $url,
        private readonly array $headers = [],
        private readonly array $body = []
    ) {
    }

    public function method(): string
    {
        return $this->method;
    }

    public function url(): string
    {
        return $this->url;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function body(): array
    {
        return $this->body;
    }
}