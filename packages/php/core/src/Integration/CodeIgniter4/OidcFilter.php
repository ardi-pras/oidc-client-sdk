<?php

declare(strict_types=1);

namespace OidcClient\Integration\CodeIgniter4;

use CodeIgniter\Filters\FilterInterface;
use CodeIgniter\HTTP\RequestInterface;
use CodeIgniter\HTTP\ResponseInterface;
use OidcClient\OidcClient;

final class OidcFilter implements FilterInterface
{
    public function before(RequestInterface $request, $arguments = null)
    {
        $config = config('Oidc');
        
        $oidc = OidcClient::builder()
            ->fromArray([
                'issuer' => $config->issuer ?? null,
                'client_id' => $config->clientId,
                'client_secret' => $config->clientSecret,
                'redirect_uri' => $config->redirectUri,
                'scope' => $config->scope ?? ['openid', 'profile', 'email'],
            ])
            ->build();

        if (!$oidc->isAuthenticated()) {
            return redirect()->to(site_url('sso/login'));
        }
    }

    public function after(RequestInterface $request, ResponseInterface $response, $arguments = null)
    {
        // Do nothing
    }
}
