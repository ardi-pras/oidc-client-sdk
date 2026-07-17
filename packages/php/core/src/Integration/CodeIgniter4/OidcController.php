<?php

declare(strict_types=1);

namespace OidcClient\Integration\CodeIgniter4;

use CodeIgniter\Controller;
use OidcClient\OidcClient;

final class OidcController extends Controller
{
    private OidcClient $oidc;

    public function initController(\CodeIgniter\HTTP\RequestInterface $request, \CodeIgniter\HTTP\ResponseInterface $response, \Psr\Log\LoggerInterface $logger)
    {
        parent::initController($request, $response, $logger);

        $config = config('Oidc');
        
        $this->oidc = OidcClient::builder()
            ->fromArray([
                'issuer' => $config->issuer ?? null,
                'client_id' => $config->clientId,
                'client_secret' => $config->clientSecret,
                'redirect_uri' => $config->redirectUri,
                'scope' => $config->scope ?? ['openid', 'profile', 'email'],
            ])
            ->build();
    }

    public function login()
    {
        return redirect()->to($this->oidc->authentication()->beginAuthentication());
    }

    public function callback()
    {
        $queryParameters = $this->request->getGet();
        $result = $this->oidc->authenticate($queryParameters);

        if ($result->isAuthenticated()) {
            $redirectSuccess = config('Oidc')->redirectOnSuccess ?? '/dashboard';
            return redirect()->to(site_url($redirectSuccess));
        }

        $redirectFailure = config('Oidc')->redirectOnFailure ?? '/login';
        return redirect()->to(site_url($redirectFailure))->with('error', $result->error() ?? 'Authentication failed.');
    }

    public function logout()
    {
        $this->oidc->logout();
        $redirectLogout = config('Oidc')->redirectOnLogout ?? '/';
        return redirect()->to(site_url($redirectLogout));
    }
}
