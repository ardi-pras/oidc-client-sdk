<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Repository;

use OidcClient\Contracts\Http\HttpClientInterface;
use OidcClient\Contracts\Repository\TokenRepositoryInterface;
use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Domain\Token\Token;
use RuntimeException;


final class HttpTokenRepository implements TokenRepositoryInterface
{

    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }

    /**
     * Exchange authorization code menjadi token
     */
    public function exchangeAuthorizationCode(
        AuthorizationResponse $response,
        AuthorizationContext $context
    ): Token {
        /**
         * Validate authorization code
         */
        if ($response->code() === null) {

            throw new RuntimeException(
                'Missing authorization code.'
            );

        }

        /**
         * Validate PKCE verifier
         *
         * RFC-7636:
         * length 43 - 128 characters
         */
        $codeVerifier = $context->codeVerifier();


        if (
            empty($codeVerifier)
            ||
            strlen($codeVerifier) < 43
            ||
            strlen($codeVerifier) > 128
        ) {

            throw new RuntimeException(
                'Invalid PKCE code verifier.'
            );

        }

        /**
         * Build token request
         */
        $payload = [

            'grant_type' =>
                'authorization_code',

            'client_id' =>
                $context->clientId(),

            'code' =>
                $response->code(),

            'redirect_uri' =>
                $context->redirectUri(),

            'code_verifier' =>
                $codeVerifier,

        ];

        /**
         * Add client secret
         *
         * Only for confidential client
         */
        if (
            $context->clientSecret() !== null
            &&
            $context->clientSecret() !== ''
        ) {

            $payload['client_secret'] =
                $context->clientSecret();

        }

        /**
         * Call token endpoint
         */
        $result = $this->http->postForm(
            $context->tokenEndpoint(),
            $payload
        );

        return $this->createToken(
            $result
        );

    }

    /**
     * Refresh access token
     */
    public function refreshToken(
        string $refreshToken,
        AuthorizationContext $context
    ): Token {


        if (
            trim($refreshToken) === ''
        ) {

            throw new RuntimeException(
                'Refresh token is empty.'
            );

        }


        $payload = [

            'grant_type' =>
                'refresh_token',

            'client_id' =>
                $context->clientId(),

            'refresh_token' =>
                $refreshToken,

        ];

        /**
         * Confidential client
         */
        if (
            $context->clientSecret() !== null
            &&
            $context->clientSecret() !== ''
        ) {

            $payload['client_secret'] =
                $context->clientSecret();

        }

        $result = $this->http->postForm(
            $context->tokenEndpoint(),
            $payload
        );

        return $this->createToken(
            $result
        );

    }





    /**
     * Convert response token menjadi Domain Token
     */
    private function createToken(
        mixed $result
    ): Token {


        if (!is_array($result)) {

            throw new RuntimeException(
                'Invalid token response.'
            );

        }



        /**
         * Handle OAuth error response
         */
        if (
            isset($result['error'])
        ) {

            throw new RuntimeException(

                $result['error_description']
                ??
                $result['error']

            );

        }



        /**
         * access_token wajib ada
         */
        if (
            empty($result['access_token'])
        ) {

            throw new RuntimeException(
                'Missing access_token.'
            );

        }



        $expiresIn = (int) ($result['expires_in'] ?? 0);

        return new Token(

            accessToken:
            $result['access_token'],


            refreshToken:
            $result['refresh_token']
            ??
            null,


            idToken:
            $result['id_token']
            ??
            null,


            expiresAt:
            $expiresIn > 0
            ? time() + $expiresIn
            : null,


            tokenType:
            isset($result['token_type'])
            ? (\OidcClient\Domain\Token\TokenType::tryFrom(ucfirst(strtolower($result['token_type']))) ?? \OidcClient\Domain\Token\TokenType::Bearer)
            : \OidcClient\Domain\Token\TokenType::Bearer,


            scope:
            $result['scope']
            ??
            null

        );

    }

}
