<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Http;

final class HttpRequest
{
    private $method;

    private $url;

    private $headers;

    private $body;

    public function __construct(
        string $method,
        string $url,
        array $headers = [],
        array $body = []
    ) {
        $this->method = $method;
        $this->url = $url;
        $this->headers = $headers;
        $this->body = $body;
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
