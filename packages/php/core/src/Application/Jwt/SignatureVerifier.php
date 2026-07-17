<?php

declare(strict_types=1);

namespace OidcClient\Application\Jwt;

use OidcClient\Domain\Jwt\DecodedJwt;
use OidcClient\Domain\Jwt\JwkSet;
use OidcClient\Support\Base64Url;
use RuntimeException;

final class SignatureVerifier
{
    public function verify(DecodedJwt $jwt, JwkSet $jwks): void
    {
        $kid = $jwt->header()->kid();
        if ($kid === null) {
            throw new RuntimeException('JWT header is missing "kid" (Key ID).');
        }

        $jwk = $jwks->findByKid($kid);
        if ($jwk === null) {
            throw new RuntimeException('No matching key found in JWKS for kid: ' . $kid);
        }

        if ($jwk->keyType() !== 'RSA') {
            throw new RuntimeException('Unsupported key type: ' . $jwk->keyType() . '. Only RSA keys are supported.');
        }

        $pem = $this->jwkToPem($jwk->modulus(), $jwk->exponent());

        $algorithm = $jwt->header()->algorithm();
        if ($algorithm !== 'RS256') {
            throw new RuntimeException('Unsupported algorithm: ' . $algorithm . '. Only RS256 is supported.');
        }

        $signature = Base64Url::decode($jwt->signature());
        $data = $jwt->signingInput();

        $verify = openssl_verify($data, $signature, $pem, OPENSSL_ALGO_SHA256);

        if ($verify !== 1) {
            throw new RuntimeException('Invalid JWT signature.');
        }
    }

    private function jwkToPem(string $n, string $e): string
    {
        $modulus = Base64Url::decode($n);
        $exponent = Base64Url::decode($e);

        $buildLength = function (int $len): string {
            if ($len < 0x80) {
                return chr($len);
            }
            $parts = [];
            while ($len > 0) {
                array_unshift($parts, chr($len & 0xFF));
                $len >>= 8;
            }
            return chr(0x80 | count($parts)) . implode('', $parts);
        };

        $buildSequence = function (string $data) use ($buildLength): string {
            return chr(0x30) . $buildLength(strlen($data)) . $data;
        };

        $buildInteger = function (string $data) use ($buildLength): string {
            if (ord($data[0]) & 0x80) {
                $data = chr(0x00) . $data;
            }
            return chr(0x02) . $buildLength(strlen($data)) . $data;
        };

        $rsaPublicKey = $buildSequence(
            $buildInteger($modulus) .
            $buildInteger($exponent)
        );

        $algorithmIdentifier = pack('H*', '300d06092a864886f70d0101010500'); 
        $subjectPublicKey = chr(0x03) . $buildLength(strlen($rsaPublicKey) + 1) . chr(0x00) . $rsaPublicKey;

        $der = $buildSequence($algorithmIdentifier . $subjectPublicKey);

        return "-----BEGIN PUBLIC KEY-----\n" .
            chunk_split(base64_encode($der), 64, "\n") .
            "-----END PUBLIC KEY-----";
    }
}
