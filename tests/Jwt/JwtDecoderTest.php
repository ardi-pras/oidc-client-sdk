<?php

declare(strict_types=1);

namespace Tests\Jwt;

use OidcClient\Application\Jwt\JwtDecoder;
use PHPUnit\Framework\TestCase;

final class JwtDecoderTest extends TestCase
{
    public function testDecodeJwt(): void
    {
        $jwt =
            'eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.'
            .'eyJzdWIiOiIxMjM0NTYiLCJpc3MiOiJodHRwczovL3Nzby5jb21wYW55LmNvbSIsImV4cCI6NDA3MDkwODgwMCwiaWF0IjoxNzIwMDAwMDAwfQ.'
            .'signature';

        $decoder = new JwtDecoder();

        $decoded = $decoder->decode($jwt);

        $this->assertEquals(
            'HS256',
            $decoded->header()->algorithm()
        );

        $this->assertEquals(
            '123456',
            $decoded->payload()->subject()
        );

        $this->assertEquals(
            'https://sso.company.com',
            $decoded->payload()->issuer()
        );

        $this->assertEquals(
            4070908800,
            $decoded->payload()->expiration()
        );
    }
}