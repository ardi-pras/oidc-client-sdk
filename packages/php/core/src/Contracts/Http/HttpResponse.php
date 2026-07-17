<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Http;

final class HttpResponse
{
    public function __construct(
        private readonly int $statusCode,
        private readonly array|string $body,
        private readonly array $headers = []
    ) {
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function body(): array|string
    {
        return $this->body;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200
            && $this->statusCode < 300;
    }
}