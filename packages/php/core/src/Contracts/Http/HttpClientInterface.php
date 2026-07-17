<?php

declare(strict_types=1);

namespace OidcClient\Contracts\Http;

interface HttpClientInterface
{
    public function send(
        HttpRequest $request
    ): HttpResponse;

    public function postForm(
        string $url,
        array $data
    ): array;
}
