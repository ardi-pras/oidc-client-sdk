<?php

declare(strict_types=1);

namespace OidcClient\Domain\Token;

use InvalidArgumentException;

final class TokenType
{
    const Bearer = 'Bearer';

    private $value;

    public function __construct(string $value = self::Bearer)
    {
        if ($value !== self::Bearer) {
            throw new InvalidArgumentException('Unsupported token type.');
        }

        $this->value = $value;
    }

    public static function fromValue($value): self
    {
        if ($value instanceof self) {
            return $value;
        }

        if (is_string($value) && $value === self::Bearer) {
            return new self($value);
        }

        throw new InvalidArgumentException('Unsupported token type.');
    }

    public function value(): string
    {
        return $this->value;
    }
}
