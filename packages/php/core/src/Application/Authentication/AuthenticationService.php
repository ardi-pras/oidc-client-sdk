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
    private $config;

    private $authorization;

    private $tokenService;

    private $jwtDecoder;

    private $validator;

    private $mapper;

    private $session;

    private $jwksService;

    private $signatureVerifier;

    public function __construct(
        OidcConfiguration $config,
        AuthorizationService $authorization,
        TokenService $tokenService,
        JwtDecoder $jwtDecoder,
        IdTokenValidator $validator,
        UserMapper $mapper,
        SessionStorageInterface $session,
        ?JwksService $jwksService = null,
        ?SignatureVerifier $signatureVerifier = null
    ) {
        $this->config = $config;
        $this->authorization = $authorization;
        $this->tokenService = $tokenService;
        $this->jwtDecoder = $jwtDecoder;
        $this->validator = $validator;
        $this->mapper = $mapper;
        $this->session = $session;
        $this->jwksService = $jwksService;
        $this->signatureVerifier = $signatureVerifier;
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
