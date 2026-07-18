<?php

declare(strict_types=1);

namespace Tests;

use OidcClient\Exception\ConfigurationException;
use OidcClient\OidcClientBuilder;
use PHPUnit\Framework\TestCase;

final class OidcClientBuilderTest extends TestCase
{
    public function testFromArrayMissingRequiredValuesThrowsConfigurationException(): void
    {
        $this->expectException(ConfigurationException::class);
        $this->expectExceptionMessage('client_id is required.');

        $builder = (new OidcClientBuilder())
            ->fromArray([
                'issuer' => 'https://example.com',
                'authorization_endpoint' => 'https://example.com/authorize',
                'token_endpoint' => 'https://example.com/token',
            ]);

        $builder->build();
    }
}
