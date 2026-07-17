<?php

declare(strict_types=1);

namespace OidcClient\Integration\Laravel;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use OidcClient\Integration\OidcService;

final class OidcController extends Controller
{
    public function __construct(
        private readonly OidcService $oidcService
    ) {
    }

    public function login()
    {
        return redirect()->away($this->oidcService->beginAuthentication());
    }

    public function callback(Request $request)
    {
        $result = $this->oidcService->authenticate($request->all());

        if ($result->isAuthenticated()) {
            $redirectPath = config('oidc.redirect_on_success', '/dashboard');
            return redirect()->to($redirectPath);
        }

        $errorRedirectPath = config('oidc.redirect_on_failure', '/login');
        return redirect()->to($errorRedirectPath)->withErrors([
            'sso' => $result->error() ?? 'Authentication failed.'
        ]);
    }

    public function logout()
    {
        $this->oidcService->logout();
        $logoutRedirectPath = config('oidc.redirect_on_logout', '/');
        return redirect()->to($logoutRedirectPath);
    }
}
