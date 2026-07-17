<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use InvalidArgumentException;
use OidcClient\Domain\Jwt\DecodedJwt;
use OidcClient\Domain\Jwt\JwtHeader;
use OidcClient\Domain\Jwt\JwtPayload;

final class JwtDecoder
{
    public function decode(string $jwt): DecodedJwt
    {
        $parts = explode('.', $jwt);

        if (count($parts) !== 3) {
            throw new InvalidArgumentException('Invalid JWT format.');
        }

        [$headerPart, $payloadPart, $signaturePart] = $parts;

        $header = $this->decodeJson($headerPart);
        $payload = $this->decodeJson($payloadPart);

        return new DecodedJwt(
            header: new JwtHeader(
                alg: $header['alg'] ?? '',
                typ: $header['typ'] ?? 'JWT',
                kid: $header['kid'] ?? null
            ),
            payload: new JwtPayload($payload),
            signature: $signaturePart,
            signingInput: $headerPart . '.' . $payloadPart
        );
    }

    /**
     * @return array<string,mixed>
     */
    private function decodeJson(string $value): array
    {
        $decoded = $this->base64UrlDecode($value);

        $json = json_decode($decoded, true);

        if (!is_array($json)) {
            throw new InvalidArgumentException('Invalid JWT JSON.');
        }

        return $json;
    }

    private function base64UrlDecode(string $input): string
    {
        $remainder = strlen($input) % 4;

        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(
            strtr($input, '-_', '+/'),
            true
        ) ?: '';
    }
}