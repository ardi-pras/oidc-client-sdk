<?php

declare(strict_types=1);

namespace OidcClient\Support;

final class Base64Url
{
    public static function encode(string $input): string
    {
        return rtrim(strtr(base64_encode($input), '+/', '-_'), '=');
    }

    public static function decode(string $input): string
    {
        $remainder = strlen($input) % 4;
        if ($remainder !== 0) {
            $input .= str_repeat('=', 4 - $remainder);
        }

        return base64_decode(strtr($input, '-_', '+/')) ?: '';
    }
}
