<?php

declare(strict_types=1);

namespace OidcClient;

use OidcClient\Application\Authentication\AuthenticationService;
use OidcClient\Application\Authentication\AuthorizationService;
use OidcClient\Application\Discovery\DiscoveryService;
use OidcClient\Application\Jwt\AudienceRule;
use OidcClient\Application\Jwt\ExpirationRule;
use OidcClient\Application\Jwt\IdTokenValidator;
use OidcClient\Application\Jwt\IssuedAtRule;
use OidcClient\Application\Jwt\IssuerRule;
use OidcClient\Application\Jwt\JwtDecoder;
use OidcClient\Application\Token\TokenService;
use OidcClient\Application\Jwt\JwksService;
use OidcClient\Application\Jwt\SignatureVerifier;

use OidcClient\Config\OidcConfiguration;

use OidcClient\Exception\ConfigurationException;

use OidcClient\Infrastructure\Http\CurlHttpClient;
use OidcClient\Infrastructure\Mapper\UserMapper;
use OidcClient\Infrastructure\Repository\HttpDiscoveryRepository;
use OidcClient\Infrastructure\Repository\HttpTokenRepository;
use OidcClient\Infrastructure\Repository\HttpJwksRepository;
use OidcClient\Infrastructure\Storage\NativeSessionStorage;

final class OidcClientBuilder
{
    private ?string $issuer = null;

    private ?string $clientId = null;

    private ?string $clientSecret = null;

    private ?string $redirectUri = null;

    private array $scopes = [
        'openid',
        'profile',
        'email',
    ];

    private ?string $authorizationEndpoint = null;

    private ?string $tokenEndpoint = null;

    private ?string $userinfoEndpoint = null;

    private ?string $jwksUri = null;

    private ?string $logoutEndpoint = null;

    private bool $verifyTls = true;

    public function fromArray(array $config): self
    {
        $this->issuer = $config['issuer'] ?? null;
        $this->authorizationEndpoint = $config['authorization_endpoint'] ?? null;
        $this->tokenEndpoint = $config['token_endpoint'] ?? null;
        $this->userinfoEndpoint = $config['userinfo_endpoint'] ?? null;
        $this->jwksUri = $config['jwks_uri'] ?? null;
        $this->logoutEndpoint = $config['logout_endpoint'] ?? null;
        $this->clientId = $config['client_id'];
        $this->clientSecret = $config['client_secret'];
        $this->redirectUri = $config['redirect_uri'];
        $this->scopes = $config['scope'] ?? [
            'openid',
            'profile',
            'email'
        ];
        $this->verifyTls = $config['verify_tls'] ?? $config['verifyTls'] ?? true;

        return $this;
    }

    private function resolveConfiguration(
        OidcConfiguration $config,
        CurlHttpClient $http
    ): OidcConfiguration {

        if ($config->issuer() === null) {
            return $config;
        }

        $repository = new HttpDiscoveryRepository(
            $http
        );

        return (new DiscoveryService($repository))
            ->discover($config);
    }

    public function verifyTls(bool $verify): self
    {
        $this->verifyTls = $verify;

        return $this;
    }

    public function issuer(string $issuer): self
    {
        $this->issuer = $issuer !== null ? rtrim($issuer, '/') : null;
        return $this;
    }

    public function clientId(string $clientId): self
    {
        $this->clientId = $clientId;
        return $this;
    }

    public function clientSecret(string $secret): self
    {
        $this->clientSecret = $secret;
        return $this;
    }

    public function redirectUri(string $redirectUri): self
    {
        $this->redirectUri = $redirectUri;
        return $this;
    }

    public function scope(array $scope): self
    {
        $this->scopes = $scope;
        return $this;
    }

    public function authorizationEndpoint(string $url): self
    {
        $this->authorizationEndpoint = $url;

        return $this;
    }

    public function tokenEndpoint(string $url): self
    {
        $this->tokenEndpoint = $url;

        return $this;
    }

    public function userinfoEndpoint(string $url): self
    {
        $this->userinfoEndpoint = $url;

        return $this;
    }

    public function jwksUri(string $url): self
    {
        $this->jwksUri = $url;

        return $this;
    }

    public function logoutEndpoint(string $url): self
    {
        $this->logoutEndpoint = $url;

        return $this;
    }

    private function validateManualEndpoints(
        OidcConfiguration $config
    ): void {

        if ($config->authorizationEndpoint() === null) {
            throw new ConfigurationException(
                'authorizationEndpoint is required.'
            );
        }

        if ($config->tokenEndpoint() === null) {
            throw new ConfigurationException(
                'tokenEndpoint is required.'
            );
        }
    }

    private function validateConfiguration(
        OidcConfiguration $config
    ): void {

        // Wajib untuk semua mode
        if ($config->clientId() === '') {
            throw new ConfigurationException(
                'client_id is required.'
            );
        }

        if ($config->redirectUri() === '') {
            throw new ConfigurationException(
                'redirect_uri is required.'
            );
        }

        /**
         * Discovery Mode
         */
        if ($config->issuer() !== null) {
            return;
        }

        /**
         * Manual Endpoint Mode
         */
        if ($config->authorizationEndpoint() === null) {
            throw new ConfigurationException(
                'authorization_endpoint is required.'
            );
        }

        if ($config->tokenEndpoint() === null) {
            throw new ConfigurationException(
                'token_endpoint is required.'
            );
        }
    }

    public function build(): OidcClient
    {
        $config = new OidcConfiguration(
            issuer: $this->issuer,
            clientId: $this->clientId,
            clientSecret: $this->clientSecret,
            redirectUri: $this->redirectUri,
            scopes: $this->scopes,

            authorizationEndpoint: $this->authorizationEndpoint,
            tokenEndpoint: $this->tokenEndpoint,
            userinfoEndpoint: $this->userinfoEndpoint,
            jwksUri: $this->jwksUri,
            logoutEndpoint: $this->logoutEndpoint,
            verifyTls: $this->verifyTls,
        );

        $httpClient = new CurlHttpClient(
            verifyTls: $this->verifyTls
        );

        $this->validateConfiguration($config);

        $config = $this->resolveConfiguration(
            $config,
            $httpClient
        );

        $session = new NativeSessionStorage();

        $authorization = new AuthorizationService(
            $config,
            $session
        );

        $tokenRepository = new HttpTokenRepository(
            $httpClient,
            $config
        );

        $tokenService = new TokenService(
            $tokenRepository
        );

        $jwtDecoder = new JwtDecoder();

        $rules = [
            new AudienceRule($config),
            new ExpirationRule(),
            new IssuedAtRule(),
        ];

        if ($config->issuer() !== null) {
            $rules[] = new IssuerRule($config);
        }

        $validator = new IdTokenValidator($rules);

        $userMapper = new UserMapper();

        $jwksRepository = new HttpJwksRepository($httpClient);
        $jwksService = new JwksService($jwksRepository);
        $signatureVerifier = new SignatureVerifier();

        $authentication = new AuthenticationService(
            $config,
            $authorization,
            $tokenService,
            $jwtDecoder,
            $validator,
            $userMapper,
            $session,
            $jwksService,
            $signatureVerifier
        );

        return new OidcClient(
            $config,
            $authorization,
            $authentication,
            $session,
            $tokenService
        );
    }

}
