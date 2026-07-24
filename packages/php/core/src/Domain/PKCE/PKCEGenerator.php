<?php

declare(strict_types=1);

namespace OidcClient\Domain\PKCE;


final class PKCEGenerator
{


    public static function generate(): PKCEPair
    {


        /**
         * RFC-7636
         * 32 bytes random
         * menghasilkan 43 chars base64url
         */
        $verifier = rtrim(
            strtr(
                base64_encode(
                    random_bytes(32)
                ),
                '+/',
                '-_'
            ),
            '='
        );



        /**
         * SHA256(verifier)
         */
        $hash = hash(
            'sha256',
            $verifier,
            true
        );



        /**
         * Base64 URL encode
         */
        $challenge = rtrim(
            strtr(
                base64_encode($hash),
                '+/',
                '-_'
            ),
            '='
        );

        return new PKCEPair(
            $verifier,
            $challenge
        );

    }

}
