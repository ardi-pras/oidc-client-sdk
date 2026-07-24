<?php

declare(strict_types=1);

namespace OidcClient\Support;

use OidcClient\Domain\PKCE\PKCEPair;

final class PKCEGenerator
{
    public static function generate(
        int $length = 64
    ): PKCEPair {

        $verifier = self::verifier($length);

        return new PKCEPair(

            $verifier,

            self::challenge($verifier)

        );
    }

    private static function verifier(
        int $length
    ): string {

        return rtrim(

            strtr(

                base64_encode(

                    random_bytes($length)

                ),

                '+/',

                '-_'

            ),

            '='

        );
    }

    private static function challenge(
        string $verifier
    ): string {

        return rtrim(

            strtr(

                base64_encode(

                    hash(
                        'sha256',
                        $verifier,
                        true
                    )

                ),

                '+/',

                '-_'

            ),

            '='

        );
    }
}
