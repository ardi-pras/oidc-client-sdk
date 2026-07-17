<?php

declare(strict_types=1);

namespace OidcClient\Integration\CodeIgniter3;

use OidcClient\Integration\OidcService;

final class OidcController extends \CI_Controller
{
    private OidcService $oidcService;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->config->load('oidc', TRUE);

        $config = $this->config->item('oidc') ?? [];

        $params = [
            'authorization_endpoint' => $config['authorization_endpoint'] ?? ($config['issuer'] ?? null),
            'issuer' => $config['issuer'] ?? null,
            'client_id' => $config['oidc_client_id'] ?? ($config['client_id'] ?? null),
            'client_secret' => $config['oidc_client_secret'] ?? ($config['client_secret'] ?? null),
            'redirect_uri' => $config['oidc_redirect_uri'] ?? ($config['redirect_uri'] ?? null),
            'scope' => $config['oidc_scope'] ?? ($config['scope'] ?? ['openid', 'profile', 'email']),
        ];

        $this->oidcService = new OidcService($params);
    }

    public function login()
    {
        $loginUrl = $this->oidcService->beginAuthentication();
        redirect($loginUrl);
    }

    public function callback()
    {
        $queryParameters = $this->input->get();
        $result = $this->oidcService->authenticate($queryParameters);

        if ($result->isAuthenticated()) {
            $redirectSuccess = $this->config->item('oidc_redirect_on_success') ?? 'dashboard';
            redirect($redirectSuccess);
        }

        $this->session->set_flashdata('error', $result->error() ?? 'Authentication failed.');
        $redirectFailure = $this->config->item('oidc_redirect_on_failure') ?? 'login';
        redirect($redirectFailure);
    }

    public function logout()
    {
        $this->oidcService->logout();
        $redirectLogout = $this->config->item('oidc_redirect_on_logout') ?? '/';
        redirect($redirectLogout);
    }
}
