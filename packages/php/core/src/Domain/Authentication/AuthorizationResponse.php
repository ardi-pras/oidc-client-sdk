<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use InvalidArgumentException;

final class AuthorizationResponse
{
    public function __construct(
        private readonly ?string $code,
        private readonly ?string $state,
        private readonly ?string $sessionState = null,
        private readonly ?string $error = null,
        private readonly ?string $errorDescription = null
    ) {
        if ($error === null && $code === null) {
            throw new InvalidArgumentException(
                'Either authorization code or error must be set.'
            );
        }
    }

    public static function fromArray(array $query): self
    {
        return new self(
            code: $query['code'] ?? null,
            state: $query['state'] ?? null,
            sessionState: $query['session_state'] ?? null,
            error: $query['error'] ?? null,
            errorDescription: $query['error_description'] ?? null
        );
    }

    public function code(): ?string
    {
        return $this->code;
    }

    public function state(): ?string
    {
        return $this->state;
    }

    public function sessionState(): ?string
    {
        return $this->sessionState;
    }

    public function error(): ?string
    {
        return $this->error;
    }

    public function errorDescription(): ?string
    {
        return $this->errorDescription;
    }

    public function hasError(): bool
    {
        return $this->error !== null;
    }

    public function hasCode(): bool
    {
        return $this->code !== null && $this->code !== '';
    }
}