<?php

declare(strict_types=1);

namespace Tests\Application;

use OidcClient\Application\Authentication\AuthenticationService;
use OidcClient\Application\Authentication\AuthorizationService;
use OidcClient\Application\Jwt\AudienceRule;
use OidcClient\Application\Jwt\ExpirationRule;
use OidcClient\Application\Jwt\IdTokenValidator;
use OidcClient\Application\Jwt\IssuedAtRule;
use OidcClient\Application\Jwt\IssuerRule;
use OidcClient\Application\Jwt\JwksService;
use OidcClient\Application\Jwt\JwtDecoder;
use OidcClient\Application\Jwt\SignatureVerifier;
use OidcClient\Application\Token\TokenService;
use OidcClient\Config\OidcConfiguration;
use OidcClient\Contracts\Http\HttpClientInterface;
use OidcClient\Contracts\Http\HttpResponse;
use OidcClient\Contracts\Repository\TokenRepositoryInterface;
use OidcClient\Contracts\Storage\SessionStorageInterface;
use OidcClient\Domain\Authentication\AuthorizationResponse;
use OidcClient\Domain\Token\Token;
use OidcClient\Domain\Token\TokenType;
use OidcClient\Infrastructure\Mapper\UserMapper;
use OidcClient\Infrastructure\Repository\HttpJwksRepository;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;

final class InMemorySessionStorage implements SessionStorageInterface
{
    private $data = [];

    public function set(string $key, $value): void
    {
        $this->data[$key] = $value;
    }

    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->data);
    }

    public function remove(string $key): void
    {
        unset($this->data[$key]);
    }

    public function clear(): void
    {
        $this->data = [];
    }
}

final class AuthenticationServiceTest extends TestCase
{
    private $session;
    private $config;
    private $tokenRepositoryMock;
    private $httpClientMock;

    protected function setUp(): void
    {
        $this->session = new InMemorySessionStorage();
        $this->config = new OidcConfiguration(
            'https://sso.company.com',
            'client-1',
            'secret-1',
            'https://client.com/callback',
            ['openid', 'profile'],
            'https://sso.company.com/authorize',
            'https://sso.company.com/token',
            'https://sso.company.com/userinfo',
            'https://sso.company.com/jwks',
            'https://sso.company.com/logout'
        );

        $this->tokenRepositoryMock = $this->createMock(TokenRepositoryInterface::class);
        $this->httpClientMock = $this->createMock(HttpClientInterface::class);
    }

    private function createJwt(string $nonce, ?\OpenSSLAsymmetricKey $privateKey, string $kid = 'key-1'): string
    {
        $header = rtrim(strtr(base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => $kid
        ])), '+/', '-_'), '=');

        $payload = rtrim(strtr(base64_encode(json_encode([
            'iss' => 'https://sso.company.com',
            'sub' => '123456',
            'aud' => 'client-1',
            'exp' => time() + 3600,
            'iat' => time(),
            'nonce' => $nonce
        ])), '+/', '-_'), '=');

        $signingInput = $header . '.' . $payload;

        if ($privateKey !== null) {
            openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $signatureBase64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');
        } else {
            $signatureBase64 = 'dummy-signature';
        }

        return $signingInput . '.' . $signatureBase64;
    }

    public function testAuthenticateSuccess(): void
    {
        // 1. Generate RSA key pair for signature validation
        $opensslConfig = [
            "config" => "C:/Program Files/Git/usr/ssl/openssl.cnf",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        $privateKey = openssl_pkey_new($opensslConfig);
        if ($privateKey === false) {
            $this->markTestSkipped('OpenSSL private key could not be generated. Check openssl.cnf path.');
        }

        $details = openssl_pkey_get_details($privateKey);
        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        // 2. Set authorization context using AuthorizationService
        $authorizationService = new AuthorizationService($this->config, $this->session);
        $authorizationService->buildAuthorizationUrl();

        $contextData = $this->session->get('oidc.authorization_context');
        $state = $contextData['state'];
        $nonce = $contextData['nonce'];

        // 3. Generate correct JWT
        $jwt = $this->createJwt($nonce, $privateKey);

        // 4. Mock token repository response
        $tokenResponse = new Token(
            'access-token-123',
            'refresh-token-123',
            $jwt,
            time() + 3600,
            TokenType::Bearer
        );

        $this->tokenRepositoryMock->method('exchangeAuthorizationCode')
            ->willReturn($tokenResponse);

        // 5. Mock HttpClient response for JWKS retrieval
        $jwksResponseData = [
            'keys' => [
                [
                    'kid' => 'key-1',
                    'kty' => 'RSA',
                    'alg' => 'RS256',
                    'use' => 'sig',
                    'n' => $n,
                    'e' => $e
                ]
            ]
        ];
        $httpResponse = new HttpResponse(200, $jwksResponseData);
        $this->httpClientMock->method('send')
            ->willReturn($httpResponse);

        // Assemble services
        $tokenService = new TokenService($this->tokenRepositoryMock);
        $jwtDecoder = new JwtDecoder();
        $rules = [
            new AudienceRule($this->config),
            new ExpirationRule(),
            new IssuedAtRule(),
            new IssuerRule($this->config),
        ];
        $validator = new IdTokenValidator($rules);
        $userMapper = new UserMapper();
        $signatureVerifier = new SignatureVerifier();
        $jwksRepository = new HttpJwksRepository($this->httpClientMock);
        $jwksService = new JwksService($jwksRepository);

        $authService = new AuthenticationService(
            $this->config,
            $authorizationService,
            $tokenService,
            $jwtDecoder,
            $validator,
            $userMapper,
            $this->session,
            $jwksService,
            $signatureVerifier
        );

        $response = new AuthorizationResponse(
            'auth-code-123',
            $state
        );

        $result = $authService->authenticate($response);

        $this->assertTrue($result->isAuthenticated());
        $this->assertNull($result->error());
        $this->assertEquals('123456', $result->user()->id());
        $this->assertEquals('access-token-123', $result->token()->accessToken());
    }

    public function testAuthenticateInvalidState(): void
    {
        $authorizationService = new AuthorizationService($this->config, $this->session);
        $authorizationService->buildAuthorizationUrl();

        // Assemble services
        $tokenService = new TokenService($this->tokenRepositoryMock);
        $jwtDecoder = new JwtDecoder();
        $validator = new IdTokenValidator([]);
        $userMapper = new UserMapper();

        $authService = new AuthenticationService(
            $this->config,
            $authorizationService,
            $tokenService,
            $jwtDecoder,
            $validator,
            $userMapper,
            $this->session
        );

        // AuthorizationResponse with mismatched state
        $response = new AuthorizationResponse(
            'auth-code-123',
            'mismatched-state'
        );

        $result = $authService->authenticate($response);

        $this->assertFalse($result->isAuthenticated());
        $this->assertEquals('Invalid state.', $result->error());
    }

    public function testAuthenticateInvalidNonce(): void
    {
        $authorizationService = new AuthorizationService($this->config, $this->session);
        $authorizationService->buildAuthorizationUrl();

        $contextData = $this->session->get('oidc.authorization_context');
        $state = $contextData['state'];

        // Create JWT with mismatched nonce 'mismatched-nonce'
        $jwt = $this->createJwt('mismatched-nonce', null);

        $tokenResponse = new Token(
            'access-token-123',
            'refresh-token-123',
            $jwt,
            time() + 3600,
            TokenType::Bearer
        );

        $this->tokenRepositoryMock->method('exchangeAuthorizationCode')
            ->willReturn($tokenResponse);

        // Assemble services
        $tokenService = new TokenService($this->tokenRepositoryMock);
        $jwtDecoder = new JwtDecoder();
        $validator = new IdTokenValidator([]);
        $userMapper = new UserMapper();

        $authService = new AuthenticationService(
            $this->config,
            $authorizationService,
            $tokenService,
            $jwtDecoder,
            $validator,
            $userMapper,
            $this->session
        );

        $response = new AuthorizationResponse(
            'auth-code-123',
            $state
        );

        $result = $authService->authenticate($response);

        $this->assertFalse($result->isAuthenticated());
        $this->assertEquals('Invalid nonce.', $result->error());
    }
}
