<?php

declare(strict_types=1);

namespace OidcClient\Infrastructure\Http;

use OidcClient\Contracts\Http\HttpClientInterface;
use OidcClient\Contracts\Http\HttpRequest;
use OidcClient\Contracts\Http\HttpResponse;
use RuntimeException;

final class CurlHttpClient implements HttpClientInterface
{
    public function __construct(
        private bool $verifyTls = true,
        private int $timeout = 30,
        private int $connectTimeout = 10,
    ) {
    }

    public function send(
        HttpRequest $request
    ): HttpResponse {

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_SSL_VERIFYPEER => $this->verifyTls,
            CURLOPT_SSL_VERIFYHOST => $this->verifyTls ? 2 : 0,

            CURLOPT_URL => $request->url(),

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => $request->method(),

            CURLOPT_HTTPHEADER => $this->normalizeHeaders(
                $request->headers()
            ),

            CURLOPT_TIMEOUT => $this->timeout,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeout,
        ]);

        if ($request->method() === 'POST') {

            curl_setopt(
                $curl,
                CURLOPT_POSTFIELDS,
                http_build_query(
                    $request->body()
                )
            );

        }

        $response = curl_exec($curl);

        if ($response === false) {

            throw new RuntimeException(
                curl_error($curl)
            );

        }

        $status = curl_getinfo(
            $curl,
            CURLINFO_HTTP_CODE
        );

        curl_close($curl);

        $decoded = json_decode(
            $response,
            true
        );

        return new HttpResponse(
            $status,
            $decoded ?? $response
        );
    }

    private function normalizeHeaders(
        array $headers
    ): array {

        $result = [];

        foreach ($headers as $key => $value) {

            $result[] = $key . ': ' . $value;

        }

        return $result;
    }

    public function postForm(
        string $url,
        array $data
    ): array {

        $request = new HttpRequest(
            method: 'POST',
            url: $url,
            headers: [
                'Content-Type' => 'application/x-www-form-urlencoded'
            ],
            body: $data
        );

        $response = $this->send($request);

        if (!$response->isSuccess()) {
            throw new RuntimeException(
                'HTTP request failed with status ' .
                $response->statusCode()
            );
        }

        $body = $response->body();

        if (!is_array($body)) {
            throw new RuntimeException(
                'Invalid JSON response.'
            );
        }

        return $body;
    }
}
