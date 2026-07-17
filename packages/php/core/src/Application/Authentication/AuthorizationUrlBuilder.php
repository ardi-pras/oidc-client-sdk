<?php

declare(strict_types=1);

namespace OidcClient\Application\Authentication;

use OidcClient\Domain\Authentication\AuthorizationRequest;
use OidcClient\Domain\Endpoint\Endpoint;

final class AuthorizationUrlBuilder
{
    public function build(
        Endpoint $endpoint,
        AuthorizationRequest $request
    ): string
    {
        return $endpoint->url().'?'.http_build_query([ 
                'client_id' => $request->clientId(),
                'redirect_uri' => $request->redirectUri(),
                'response_type' => 'code',
                'scope' => $request->scope(),
                'state' => $request->state(),
                'code_challenge' => $request->pkce()->challenge(),
                'code_challenge_method'=> 'S256',
                ...$request->additionalParameters()
            ]);
    }
}