<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Application\Jwt\IdTokenValidator;
use OidcClient\Application\Jwt\JwtDecoder;
use OidcClient\Application\Token\TokenService;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Domain\Authentication\AuthenticationResult;
use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Infrastructure\Mapper\UserMapper;
use Throwable;
use RuntimeException;


final class CallbackHandler
{
    private $tokenService;

    private $jwtDecoder;

    private $validator;

    private $userMapper;

    private $session;

    public function __construct(
        TokenService $tokenService,
        JwtDecoder $jwtDecoder,
        IdTokenValidator $validator,
        UserMapper $userMapper,
        SessionStorageInterface $session
    ) {
        $this->tokenService = $tokenService;
        $this->jwtDecoder = $jwtDecoder;
        $this->validator = $validator;
        $this->userMapper = $userMapper;
        $this->session = $session;
    }

    public function handle(AuthorizationResponse $response, AuthorizationContext $context): AuthenticationResult
    {
        try {
            /**
             * 1. Validate OAuth State
             */
            $this->validateState($response);

            /**
             * 2. Validate PKCE verifier
             */
            $codeVerifier = $this->session->get('oidc.code_verifier');

            if ($codeVerifier === null) {
                return AuthenticationResult::failure('Missing PKCE code verifier.');
            }

            /**
             * 3. Exchange authorization code
             */
            $token = $this->tokenService->exchangeAuthorizationCode($response, $context);

            /**
             * 4. Validate ID Token
             */
            if ($token->idToken() === null) {
                return AuthenticationResult::failure('Missing id_token.');
            }

            $jwt = $this->jwtDecoder->decode($token->idToken());

            $this->validator->validate($jwt);

            /**
             * 5. Map user
             */
            $user = $this->userMapper->map($jwt);

            /**
             * 6. Save authentication session
             */
            $this->session->put('oidc.user', $user);

            $this->session->put(
                'oidc.token',
                [
                    'access_token' => $token->accessToken(),
                    'refresh_token' => $token->refreshToken(),
                    'expires_in' => $token->expiresIn(),
                    'token_type' => $token->tokenType()
                ]
            );

            $this->session->put('oidc.logged_in', true);

            /**
             * 7. Cleanup temporary OAuth data
             */
            $this->session->remove('oidc.code_verifier');

            return AuthenticationResult::success($user, $token);
        } catch (Throwable $e) {
            return AuthenticationResult::failure($e->getMessage());
        }

    }

    private function validateState(AuthorizationResponse $response): void
    {
        $expected = $this->session->get('oidc.state');

        if ($expected === null) {
            throw new RuntimeException('Missing state.');
        }

        if (!hash_equals($expected, $response->state())) {
            throw new RuntimeException('Invalid state.');
        }

        $this->session->remove('oidc.state');
    }
}
