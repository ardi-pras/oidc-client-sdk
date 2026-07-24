<?php

declare(strict_types=1);

namespace OidcClient\Domain\PKCE;

use InvalidArgumentException;

final class PKCEPair
{
    private $verifier;

    private $challenge;

    private $method;

    public function __construct(
        string $verifier,
        string $challenge,
        string $method = 'S256'
    ) {
        $this->verifier = $verifier;
        $this->challenge = $challenge;
        $this->method = $method;


        $this->validateVerifier(
            $verifier
        );


        if ($method !== 'S256') {

            throw new InvalidArgumentException(
                'Only S256 PKCE method is supported.'
            );

        }

    }



    public function verifier(): string
    {
        return $this->verifier;
    }



    public function challenge(): string
    {
        return $this->challenge;
    }



    public function method(): string
    {
        return $this->method;
    }



    public function equals(
        PKCEPair $pair
    ): bool {

        return hash_equals(
            $this->verifier,
            $pair->verifier()
        );

    }



    private function validateVerifier(
        string $verifier
    ): void {


        $length = strlen(
            $verifier
        );


        if (
            $length < 43 ||
            $length > 128
        ) {

            throw new InvalidArgumentException(
                'PKCE verifier length must be between 43 and 128 characters.'
            );

        }



        if (
            !preg_match(
                '/^[A-Za-z0-9\-._~]+$/',
                $verifier
            )
        ) {

            throw new InvalidArgumentException(
                'Invalid PKCE verifier format.'
            );

        }

    }

}
