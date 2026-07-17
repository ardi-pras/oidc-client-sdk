<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Repository;

use OidcClient\Contracts\Http\HttpClientInterface;
use OidcClient\Contracts\Http\HttpRequest;
use OidcClient\Contracts\Repository\DiscoveryRepositoryInterface;
use OidcClient\Domain\Discovery\DiscoveryDocument;
use RuntimeException;

final class HttpDiscoveryRepository implements DiscoveryRepositoryInterface
{
    public function __construct(
        private readonly HttpClientInterface $http
    ) {
    }

    public function discover(
        string $issuer
    ): DiscoveryDocument {

        $request = new HttpRequest(
            'GET',
            rtrim($issuer, '/').'/.well-known/openid-configuration'
        );

        $response = $this->http->send($request);

        if (!$response->isSuccess()) {
            throw new RuntimeException('Unable to load discovery document.');
        }

        $body = $response->body();

        if (!is_array($body)) {
            throw new RuntimeException(sprintf(
                "Discovery endpoint did not return JSON.\n\nResponse:\n%s",
                $body
            ));
        }

        foreach ([
            'issuer',
            'authorization_endpoint',
            'token_endpoint',
            'jwks_uri'
        ] as $field) {
            if (!isset($body[$field])) {
                throw new RuntimeException(sprintf(
                    "Discovery document missing '%s'.\n\n%s",
                    $field,
                    json_encode($body, JSON_PRETTY_PRINT)
                ));
            }
        }

        return new DiscoveryDocument(
            authorizationEndpoint: $body['authorization_endpoint'],
            tokenEndpoint: $body['token_endpoint'],
            userinfoEndpoint: $body['userinfo_endpoint'] ?? null,
            jwksUri: $body['jwks_uri'],
            endSessionEndpoint: $body['end_session_endpoint']
                ?? $body['end_session_endpoint_uri']
                ?? null
        );
    }
}