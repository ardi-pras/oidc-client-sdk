<?php

declare(strict_types=1);

namespace OidcClient;

use OidcClient\OidcClientBuilder;
use OidcClient\Domain\User\User;
use OidcClient\Application\Authentication\AuthorizationService;
use OidcClient\Application\Token\TokenService;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Config\OidcConfiguration;
use OidcClient\Application\Authentication\AuthenticationService;
use OidcClient\Domain\Authentication\AuthenticationResult;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Domain\Token\Token;

final class OidcClient
{
    private ?User $user = null;

    public function __construct(
        private readonly OidcConfiguration $configuration,
        private readonly AuthorizationService $authorization,
        private readonly AuthenticationService $authentication,
        private readonly SessionStorageInterface $session,
        private readonly TokenService $tokenService
    ) {
    }

    public function login(): never
    {
        header(
            'Location: ' . $this->authorization->buildAuthorizationUrl()
        );

        exit;
    }

    public function configuration(): OidcConfiguration
    {
        return $this->configuration;
    }

    public function authenticate(array $query): AuthenticationResult
    {
        return $this->authentication->authenticate(
            AuthorizationResponse::fromArray($query)
        );
    }

    public function authentication(): AuthenticationService
    {
        return $this->authentication;
    }

    public static function builder(): OidcClientBuilder
    {
        return new OidcClientBuilder();
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function user(): ?User
    {
        return $this->session
            ->get(
                'oidc.user'
            );
    }

    public function token(): ?Token
    {
        return $this->session
            ->get('oidc.token');
    }

    public function isAuthenticated(): bool
    {
        return (bool) 
            $this->session
                ->get(
                    'oidc.logged_in'
                );
    }

    public function logout(): void
    {
        $this->session->remove(
            'oidc.user'
        );

        $this->session->remove(
            'oidc.token'
        );

        $this->session->remove(
            'oidc.logged_in'
        );
    }

    public function refreshToken(): void
    {
        $token = $this->token();

        if ($token === null) {
            return;
        }

        // $newToken = $this->context
        //     ->token()
        //     ->refreshToken(
        //         $token->refreshToken(),
        //         $this->authorizationContext
        //     );

        // $this->session->put(
        //     'oidc.token',
        //     $newToken
        // );
    }
}
