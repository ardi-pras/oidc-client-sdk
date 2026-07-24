<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Http;

final class HttpResponse
{
    private $statusCode;

    private $body;

    private $headers;

    public function __construct(
        int $statusCode,
        $body,
        array $headers = []
    ) {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    public function body()
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
