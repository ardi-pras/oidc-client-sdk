<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Mapper;

use OidcClient\Domain\Jwt\Jwk;
use OidcClient\Domain\Jwt\JwkSet;
use InvalidArgumentException;

final class JwkMapper
{
    public function mapKey(array $data): Jwk
    {
        if (
            empty($data['kid']) ||
            empty($data['kty']) ||
            empty($data['alg']) ||
            empty($data['n']) ||
            empty($data['e'])
        ) {
            throw new InvalidArgumentException('Invalid JWK data.');
        }

        return new Jwk(
            $data['kid'],
            $data['kty'],
            $data['alg'],
            $data['use'] ?? 'sig',
            $data['n'],
            $data['e']
        );
    }

    public function mapSet(array $data): JwkSet
    {
        if (!isset($data['keys']) || !is_array($data['keys'])) {
            throw new InvalidArgumentException('Invalid JWKS data.');
        }

        $keys = [];
        foreach ($data['keys'] as $keyData) {
            try {
                $keys[] = $this->mapKey($keyData);
            } catch (InvalidArgumentException) {
                // Skip invalid or unsupported keys
            }
        }

        return new JwkSet($keys);
    }
}
