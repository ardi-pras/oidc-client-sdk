<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Repository;

use OidcClient\Contracts\Http\HttpClientInterface;
use OidcClient\Contracts\Http\HttpRequest;
use OidcClient\Domain\Jwt\JwkSet;
use OidcClient\Infrastructure\Mapper\JwkMapper;
use RuntimeException;

final class HttpJwksRepository
{
    private $http;

    private $mapper;

    public function __construct(HttpClientInterface $http, JwkMapper $mapper = null)
    {
        $this->http = $http;
        $this->mapper = $mapper ?: new JwkMapper();
    }

    public function getJwks(string $jwksUri): JwkSet
    {
        $request = new HttpRequest('GET', $jwksUri);
        $response = $this->http->send($request);

        if (!$response->isSuccess()) {
            throw new RuntimeException('Unable to load JWKS document from: ' . $jwksUri);
        }

        $body = $response->body();

        if (!is_array($body)) {
            throw new RuntimeException('JWKS endpoint did not return JSON.');
        }

        return $this->mapper->mapSet($body);
    }
}
