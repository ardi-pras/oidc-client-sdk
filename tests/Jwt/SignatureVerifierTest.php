<?php

declare(strict_types=1);

namespace Tests\Jwt;

use OidcClient\Application\Jwt\JwtDecoder;
use OidcClient\Application\Jwt\SignatureVerifier;
use OidcClient\Domain\Jwt\Jwk;
use OidcClient\Domain\Jwt\JwkSet;
use PHPUnit\Framework\TestCase;

final class SignatureVerifierTest extends TestCase
{
    public function testSignatureVerification(): void
    {
        $config = [
            "config" => "C:/Program Files/Git/usr/ssl/openssl.cnf",
            "private_key_bits" => 2048,
            "private_key_type" => OPENSSL_KEYTYPE_RSA,
        ];
        
        $privateKey = openssl_pkey_new($config);
        if ($privateKey === false) {
            $this->markTestSkipped('OpenSSL private key could not be generated. Check openssl.cnf path.');
        }

        $details = openssl_pkey_get_details($privateKey);
        $n = rtrim(strtr(base64_encode($details['rsa']['n']), '+/', '-_'), '=');
        $e = rtrim(strtr(base64_encode($details['rsa']['e']), '+/', '-_'), '=');

        $header = rtrim(strtr(base64_encode(json_encode([
            'alg' => 'RS256',
            'typ' => 'JWT',
            'kid' => 'key-1'
        ])), '+/', '-_'), '=');

        $payload = rtrim(strtr(base64_encode(json_encode([
            'iss' => 'https://sso.company.com',
            'sub' => '123456',
            'aud' => 'client-1',
            'exp' => time() + 3600,
            'iat' => time()
        ])), '+/', '-_'), '=');

        $signingInput = $header . '.' . $payload;
        openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
        $signatureBase64 = rtrim(strtr(base64_encode($signature), '+/', '-_'), '=');

        $jwt = $signingInput . '.' . $signatureBase64;

        $decoder = new JwtDecoder();
        $decoded = $decoder->decode($jwt);

        $jwk = new Jwk(
            kid: 'key-1',
            kty: 'RSA',
            alg: 'RS256',
            use: 'sig',
            n: $n,
            e: $e
        );
        $jwks = new JwkSet([$jwk]);

        $verifier = new SignatureVerifier();
        
        $this->expectNotToPerformAssertions();
        $verifier->verify($decoded, $jwks);
    }
}
