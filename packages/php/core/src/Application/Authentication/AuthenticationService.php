<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Application\Jwt\JwtDecoder;
use OidcClient\Application\Jwt\IdTokenValidator;
use OidcClient\Domain\Authentication\AuthenticationResult;
use OidcClient\Domain\Authentication\AuthorizationContext;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Application\Authentication\AuthorizationService;
use OidcClient\Infrastructure\Mapper\UserMapper;
use OidcClient\Application\Token\TokenService;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Config\OidcConfiguration;
use OidcClient\Application\Jwt\JwksService;
use OidcClient\Application\Jwt\SignatureVerifier;

final class AuthenticationService
{
    public function __construct(
        private readonly OidcConfiguration $config,
        private readonly AuthorizationService $authorization,
        private readonly TokenService $tokenService,
        private readonly JwtDecoder $jwtDecoder,
        private readonly IdTokenValidator $validator,
        private readonly UserMapper $mapper,
        private readonly SessionStorageInterface $session,
        private readonly ?JwksService $jwksService = null,
        private readonly ?SignatureVerifier $signatureVerifier = null
    ) {
    }

    public function beginAuthentication(): string
    {
        return $this->authorization
            ->buildAuthorizationUrl();
    }

    public function authorizationUrl(): string
    {
        return $this->beginAuthentication();
    }

    public function authenticate(
        AuthorizationResponse $response
    ): AuthenticationResult {
        $context = AuthorizationContext::fromSession(
            $this->session
        );

        if (
            !$context->validateState(
                $response->state()
            )
        ) {
            return AuthenticationResult::failed('Invalid state.');
        }

        $token = $this->tokenService->exchangeAuthorizationCode(
            $response,
            $context
        );

        $jwt = $this->jwtDecoder->decode(
            $token->idToken()
        );

        $this->validator
            ->validate(
                $jwt
            );

        $expectedNonce = $context->nonce();
        $actualNonce = $jwt->payload()->nonce();

        if ($expectedNonce !== null && $expectedNonce !== '') {
            if ($actualNonce !== null && !hash_equals($expectedNonce, $actualNonce)) {
                return AuthenticationResult::failed('Invalid nonce.');
            }
        }

        if ($this->jwksService !== null && $this->signatureVerifier !== null) {
            $jwksUri = $this->config->jwksUri();
            if ($jwksUri !== null && $jwksUri !== '') {
                $jwks = $this->jwksService->getJwks($jwksUri);
                $this->signatureVerifier->verify($jwt, $jwks);
            }
        }

        $user = $this->mapper
            ->map(
                $jwt
            );

        $this->session->set(
            'oidc.user',
            $user
        );

        $this->session->set(
            'oidc.token',
            $token
        );

        $this->session->set(
            'oidc.logged_in',
            true
        );

        return AuthenticationResult::success(
            $user,
            $token
        );
    }
}
