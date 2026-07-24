<?php

declare(strict_types=1);

namespace Tests\Domain\Token;

use OidcClient\Domain\Token\TokenType;
use PHPUnit\Framework\TestCase;

final class TokenTypeTest extends TestCase
{
    public function testFromValueCreatesBearerTokenType(): void
    {
        $tokenType = TokenType::fromValue(TokenType::Bearer);

        $this->assertInstanceOf(TokenType::class, $tokenType);
        $this->assertSame('Bearer', $tokenType->value());
    }
}
