<?php

declare(strict_types=1);

namespace OidcClient\Domain\Authentication;

use InvalidArgumentException;

final class AuthorizationResponse
{
    private $code;

    private $state;

    private $sessionState;

    private $error;

    private $errorDescription;

    public function __construct(
        ?string $code,
        ?string $state,
        ?string $sessionState = null,
        ?string $error = null,
        ?string $errorDescription = null
    ) {
        $this->code = $code;
        $this->state = $state;
        $this->sessionState = $sessionState;
        $this->error = $error;
        $this->errorDescription = $errorDescription;

        if ($error === null && $code === null) {
            throw new InvalidArgumentException(
                'Either authorization code or error must be set.'
            );
        }
    }

    public static function fromArray(array $query): self
    {
        return new self(
            $query['code'] ?? null,
            $query['state'] ?? null,
            $query['session_state'] ?? null,
            $query['error'] ?? null,
            $query['error_description'] ?? null
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
