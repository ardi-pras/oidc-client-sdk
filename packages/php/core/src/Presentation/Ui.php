<?php

declare(strict_types=1);

namespace OidcClient\Presentation;

use OidcClient\Domain\User\User;
use OidcClient\Domain\Token\Token;

final class Ui
{
    private static function getFontsAndStyles(): string
    {
        return <<<HTML
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
    .oidc-ui-font {
        font-family: 'Inter', system-ui, -apple-system, sans-serif;
        box-sizing: border-box;
    }
    .oidc-ui-font *, .oidc-ui-font *::before, .oidc-ui-font *::after {
        box-sizing: border-box;
    }
    .oidc-mono {
        font-family: 'JetBrains Mono', 'Fira Code', monospace;
    }

    /* Variables */
    :root {
        --oidc-primary: #3b82f6;
        --oidc-primary-hover: #2563eb;
        --oidc-bg-dark: #0f172a;
        --oidc-card-dark: rgba(30, 41, 59, 0.7);
        --oidc-border-dark: rgba(255, 255, 255, 0.08);
        --oidc-text-main: #f8fafc;
        --oidc-text-muted: #94a3b8;
        --oidc-success: #10b981;
        --oidc-warning: #f59e0b;
        --oidc-danger: #ef4444;
    }

    /* Modern Login Button */
    .oidc-login-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 12px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #3b82f6 0%, #1d4ed8 100%);
        color: #ffffff;
        font-weight: 600;
        font-size: 15px;
        border: none;
        border-radius: 12px;
        cursor: pointer;
        text-decoration: none;
        box-shadow: 0 4px 20px rgba(37, 99, 235, 0.25);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        position: relative;
        overflow: hidden;
    }
    .oidc-login-btn::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(
            90deg,
            transparent,
            rgba(255, 255, 255, 0.2),
            transparent
        );
        transition: 0.5s;
    }
    .oidc-login-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 24px rgba(37, 99, 235, 0.4);
    }
    .oidc-login-btn:hover::before {
        left: 100%;
    }
    .oidc-login-btn:active {
        transform: translateY(0);
        box-shadow: 0 2px 10px rgba(37, 99, 235, 0.2);
    }

    /* Glassmorphism User Card */
    .oidc-user-card {
        background: var(--oidc-card-dark);
        backdrop-filter: blur(16px);
        -webkit-backdrop-filter: blur(16px);
        border: 1px solid var(--oidc-border-dark);
        border-radius: 20px;
        padding: 24px;
        max-width: 380px;
        color: var(--oidc-text-main);
        box-shadow: 0 10px 30px rgba(0, 0, 0, 0.25);
        display: flex;
        flex-direction: column;
        gap: 20px;
    }
    .oidc-card-header {
        display: flex;
        align-items: center;
        gap: 16px;
    }
    .oidc-avatar {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 700;
        font-size: 22px;
        background: linear-gradient(135deg, #6366f1 0%, #a855f7 100%);
        color: #ffffff;
        box-shadow: 0 4px 14px rgba(168, 85, 247, 0.35);
    }
    .oidc-user-info {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .oidc-user-name {
        font-weight: 600;
        font-size: 18px;
        margin: 0;
    }
    .oidc-user-email {
        font-size: 14px;
        color: var(--oidc-text-muted);
        margin: 0;
        word-break: break-all;
    }
    .oidc-badge-container {
        display: flex;
        flex-wrap: wrap;
        gap: 6px;
    }
    .oidc-badge {
        font-size: 11px;
        font-weight: 600;
        padding: 4px 10px;
        border-radius: 9999px;
        background: rgba(59, 130, 246, 0.15);
        color: #60a5fa;
        border: 1px solid rgba(59, 130, 246, 0.25);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .oidc-logout-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        padding: 12px;
        background: rgba(239, 68, 68, 0.1);
        border: 1px solid rgba(239, 68, 68, 0.2);
        color: #f87171;
        font-weight: 600;
        font-size: 14px;
        border-radius: 10px;
        cursor: pointer;
        text-decoration: none;
        transition: all 0.2s ease;
    }
    .oidc-logout-btn:hover {
        background: var(--oidc-danger);
        color: #ffffff;
        box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
    }

    /* Developer Debug Dashboard */
    .oidc-dashboard {
        background: #0b0f19;
        border: 1px solid var(--oidc-border-dark);
        border-radius: 24px;
        color: var(--oidc-text-main);
        padding: 32px;
        max-width: 1000px;
        margin: 20px auto;
        box-shadow: 0 20px 50px rgba(0, 0, 0, 0.4);
    }
    .oidc-dashboard-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        border-bottom: 1px solid var(--oidc-border-dark);
        padding-bottom: 20px;
        margin-bottom: 24px;
    }
    .oidc-dashboard-title h3 {
        margin: 0;
        font-size: 22px;
        font-weight: 700;
        background: linear-gradient(90deg, #60a5fa, #c084fc);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
    }
    .oidc-status-indicator {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 13px;
        font-weight: 500;
        background: rgba(16, 185, 129, 0.1);
        padding: 6px 12px;
        border-radius: 20px;
        border: 1px solid rgba(16, 185, 129, 0.2);
        color: var(--oidc-success);
    }
    .oidc-status-dot {
        width: 8px;
        height: 8px;
        border-radius: 50%;
        background-color: var(--oidc-success);
        box-shadow: 0 0 8px var(--oidc-success);
    }
    .oidc-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 24px;
    }
    @media (min-width: 768px) {
        .oidc-grid {
            grid-template-columns: 1.2fr 1.8fr;
        }
    }
    .oidc-pane {
        background: rgba(30, 41, 59, 0.3);
        border: 1px solid var(--oidc-border-dark);
        border-radius: 16px;
        padding: 20px;
        display: flex;
        flex-direction: column;
        gap: 16px;
    }
    .oidc-pane-title {
        font-size: 15px;
        font-weight: 600;
        color: var(--oidc-text-muted);
        margin: 0 0 10px 0;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .oidc-field {
        display: flex;
        flex-direction: column;
        gap: 4px;
    }
    .oidc-field-label {
        font-size: 12px;
        color: var(--oidc-text-muted);
        font-weight: 500;
    }
    .oidc-field-value {
        font-size: 14px;
        font-weight: 500;
        word-break: break-all;
    }

    /* Token Box and Collapsible styles */
    .oidc-collapsible {
        border: 1px solid var(--oidc-border-dark);
        border-radius: 10px;
        overflow: hidden;
        background: rgba(15, 23, 42, 0.4);
    }
    .oidc-collapsible-trigger {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 12px 16px;
        background: rgba(30, 41, 59, 0.4);
        cursor: pointer;
        font-weight: 500;
        font-size: 14px;
        user-select: none;
        transition: background 0.2s ease;
    }
    .oidc-collapsible-trigger:hover {
        background: rgba(30, 41, 59, 0.7);
    }
    .oidc-collapsible-content {
        max-height: 0;
        overflow: hidden;
        transition: max-height 0.3s cubic-bezier(0, 1, 0, 1);
        padding: 0 16px;
    }
    .oidc-collapsible.active .oidc-collapsible-content {
        max-height: 1000px;
        transition: max-height 0.3s ease-in;
        padding: 16px;
        border-top: 1px solid var(--oidc-border-dark);
    }
    .oidc-token-container {
        display: flex;
        flex-direction: column;
        gap: 12px;
    }
    .oidc-token-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        background: rgba(15, 23, 42, 0.6);
        padding: 10px 14px;
        border-radius: 8px;
        border: 1px solid var(--oidc-border-dark);
    }
    .oidc-token-name {
        font-size: 13px;
        font-weight: 600;
    }
    .oidc-btn-copy {
        background: transparent;
        border: none;
        color: var(--oidc-text-muted);
        cursor: pointer;
        padding: 6px;
        border-radius: 6px;
        display: flex;
        align-items: center;
        justify-content: center;
        transition: all 0.2s ease;
    }
    .oidc-btn-copy:hover {
        color: #ffffff;
        background: rgba(255, 255, 255, 0.05);
    }
    .oidc-btn-copy.success {
        color: var(--oidc-success);
    }

    /* JSON claim list viewer */
    .oidc-json-tree {
        font-size: 13px;
        color: #e2e8f0;
        background: #080c14;
        padding: 16px;
        border-radius: 12px;
        border: 1px solid var(--oidc-border-dark);
        margin: 0;
        overflow-x: auto;
    }
</style>
HTML;
    }

    public static function loginButton(string $loginUrl, string $theme = 'dark'): string
    {
        $styles = self::getFontsAndStyles();
        return <<<HTML
{$styles}
<div class="oidc-ui-font">
    <a href="{$loginUrl}" class="oidc-login-btn">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 2C6.48 2 2 6.48 2 12C2 17.52 6.48 22 12 22C17.52 22 22 17.52 22 12C22 6.48 17.52 2 12 2ZM12 5C13.66 5 15 6.34 15 8C15 9.66 13.66 11 12 11C10.34 11 9 9.66 9 8C9 6.34 10.34 5 12 5ZM12 19.2C9.5 19.2 7.29 17.92 6 15.98C6.03 13.99 10 12.9 12 12.9C13.99 12.9 17.97 13.99 18 15.98C16.71 17.92 14.5 19.2 12 19.2Z" fill="currentColor"/>
        </svg>
        <span>Sign in with SSO</span>
    </a>
</div>
HTML;
    }

    public static function userProfileCard(User $user, string $logoutUrl, string $theme = 'dark'): string
    {
        $styles = self::getFontsAndStyles();
        $name = htmlspecialchars($user->name() ?? $user->username() ?? 'OIDC User');
        $email = htmlspecialchars($user->email() ?? 'No email address');

        // Grab initials
        $initials = '';
        $words = explode(' ', $name);
        foreach ($words as $word) {
            $initials .= mb_substr($word, 0, 1);
        }
        $initials = mb_strtoupper(mb_substr($initials, 0, 2));
        if ($initials === '') {
            $initials = 'US';
        }

        // Grab roles from claims
        $claims = $user->claims()->all();
        $roles = $claims['roles'] ?? [];
        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $rolesHtml = '';
        foreach ($roles as $role) {
            $rolesHtml .= '<span class="oidc-badge">' . htmlspecialchars((string) $role) . '</span>';
        }

        $rolesContainer = '';
        if ($rolesHtml !== '') {
            $rolesContainer = '<div class="oidc-badge-container">' . $rolesHtml . '</div>';
        }

        return <<<HTML
{$styles}
<div class="oidc-ui-font">
    <div class="oidc-user-card">
        <div class="oidc-card-header">
            <div class="oidc-avatar">{$initials}</div>
            <div class="oidc-user-info">
                <h4 class="oidc-user-name">{$name}</h4>
                <p class="oidc-user-email">{$email}</p>
            </div>
        </div>

        {$rolesContainer}

        <a href="{$logoutUrl}" class="oidc-logout-btn">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" class="feather feather-log-out">
                <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"></path>
                <polyline points="16 17 21 12 16 7"></polyline>
                <line x1="21" y1="12" x2="9" y2="12"></line>
            </svg>
            <span>Sign Out</span>
        </a>
    </div>
</div>
HTML;
    }

    public static function debugDashboard(User $user, Token $token, string $theme = 'dark'): string
    {
        $styles = self::getFontsAndStyles();

        $name = htmlspecialchars($user->name() ?? $user->username() ?? 'OIDC User');
        $email = htmlspecialchars($user->email() ?? 'No email address');
        $subject = htmlspecialchars($user->id());

        $accessToken = htmlspecialchars($token->accessToken());
        $idToken = htmlspecialchars($token->idToken() ?? 'None');
        $refreshToken = htmlspecialchars($token->refreshToken() ?? 'None');
        $tokenType = htmlspecialchars($token->tokenType()->value());
        $expiresIn = $token->expiresIn() ?? 0;
        $expiresAt = $token->expiresAt() ? date('Y-m-d H:i:s', $token->expiresAt()) : 'Never';
        $scopes = htmlspecialchars($token->scope() ?? 'None');

        $claims = $user->claims()->all();
        $claimsJson = htmlspecialchars(json_encode($claims, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));

        $refreshTokenHtml = '';
        if ($refreshToken !== 'None') {
            $refreshTokenHtml = <<<HTML
                    <div class="oidc-collapsible" id="col-refresh-token">
                        <div class="oidc-collapsible-trigger" onclick="toggleOidcCollapsible('col-refresh-token')">
                            <span>Refresh Token</span>
                            <span class="chevron">▼</span>
                        </div>
                        <div class="oidc-collapsible-content">
                            <div class="oidc-token-row">
                                <span class="oidc-field-value oidc-mono" style="max-height: 120px; overflow-y: auto; width: calc(100% - 40px); word-break: break-all; font-size: 11px;">{$refreshToken}</span>
                                <button class="oidc-btn-copy" onclick="copyOidcText('{$refreshToken}', this)" title="Copy Refresh Token">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>
HTML;
        }

        return <<<HTML
{$styles}
<div class="oidc-ui-font">
    <div class="oidc-dashboard">
        <div class="oidc-dashboard-title">
            <h3>OIDC SSO Developer Dashboard</h3>
            <div class="oidc-status-indicator">
                <span class="oidc-status-dot"></span>
                <span>Session Active</span>
            </div>
        </div>

        <div class="oidc-grid">
            <!-- Left Pane: Session & Token Status -->
            <div class="oidc-pane">
                <h4 class="oidc-pane-title">Session Details</h4>

                <div class="oidc-field">
                    <span class="oidc-field-label">Subject (Sub)</span>
                    <span class="oidc-field-value oidc-mono">{$subject}</span>
                </div>

                <div class="oidc-field">
                    <span class="oidc-field-label">User Name</span>
                    <span class="oidc-field-value">{$name}</span>
                </div>

                <div class="oidc-field">
                    <span class="oidc-field-label">User Email</span>
                    <span class="oidc-field-value">{$email}</span>
                </div>

                <div class="oidc-field">
                    <span class="oidc-field-label">Token Type</span>
                    <span class="oidc-field-value">{$tokenType}</span>
                </div>

                <div class="oidc-field">
                    <span class="oidc-field-label">Expires In / At</span>
                    <span class="oidc-field-value">{$expiresIn}s ({$expiresAt})</span>
                </div>

                <div class="oidc-field">
                    <span class="oidc-field-label">Authorized Scopes</span>
                    <span class="oidc-field-value oidc-mono">{$scopes}</span>
                </div>
            </div>

            <!-- Right Pane: Active Tokens & Claims -->
            <div class="oidc-pane">
                <h4 class="oidc-pane-title">Active Tokens & User Claims</h4>

                <div class="oidc-token-container">
                    <!-- Access Token -->
                    <div class="oidc-collapsible" id="col-access-token">
                        <div class="oidc-collapsible-trigger" onclick="toggleOidcCollapsible('col-access-token')">
                            <span>Access Token</span>
                            <span class="chevron">▼</span>
                        </div>
                        <div class="oidc-collapsible-content">
                            <div class="oidc-token-row">
                                <span class="oidc-field-value oidc-mono" style="max-height: 120px; overflow-y: auto; width: calc(100% - 40px); word-break: break-all; font-size: 11px;">{$accessToken}</span>
                                <button class="oidc-btn-copy" onclick="copyOidcText('{$accessToken}', this)" title="Copy Access Token">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- ID Token -->
                    <div class="oidc-collapsible" id="col-id-token">
                        <div class="oidc-collapsible-trigger" onclick="toggleOidcCollapsible('col-id-token')">
                            <span>ID Token (JWT)</span>
                            <span class="chevron">▼</span>
                        </div>
                        <div class="oidc-collapsible-content">
                            <div class="oidc-token-row">
                                <span class="oidc-field-value oidc-mono" style="max-height: 120px; overflow-y: auto; width: calc(100% - 40px); word-break: break-all; font-size: 11px;">{$idToken}</span>
                                <button class="oidc-btn-copy" onclick="copyOidcText('{$idToken}', this)" title="Copy ID Token">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="9" y="9" width="13" height="13" rx="2" ry="2"></rect><path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"></path></svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Refresh Token -->
                    {$refreshTokenHtml}

                    <!-- Parsed JWT Claims -->
                    <div class="oidc-collapsible active" id="col-user-claims">
                        <div class="oidc-collapsible-trigger" onclick="toggleOidcCollapsible('col-user-claims')">
                            <span>Parsed User Claims</span>
                            <span class="chevron">▼</span>
                        </div>
                        <div class="oidc-collapsible-content">
                            <pre class="oidc-json-tree oidc-mono">{$claimsJson}</pre>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleOidcCollapsible(id) {
    const el = document.getElementById(id);
    if (el) {
        el.classList.toggle('active');
    }
}

function copyOidcText(text, btn) {
    navigator.clipboard.writeText(text).then(() => {
        const originalSvg = btn.innerHTML;
        btn.classList.add('success');
        btn.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"></polyline></svg>';
        setTimeout(() => {
            btn.classList.remove('success');
            btn.innerHTML = originalSvg;
        }, 1500);
    }).catch(err => {
        console.error('Failed to copy token: ', err);
    });
}
</script>
HTML;
    }
}
