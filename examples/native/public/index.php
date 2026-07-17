<?php

require __DIR__ . '/../bootstrap.php';

use OidcClient\Presentation\Ui;

$isAuthenticated = $oidc->isAuthenticated();
$user = $isAuthenticated ? $oidc->user() : null;
$token = $isAuthenticated ? $oidc->token() : null;

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>OIDC Client SDK Demo</title>
    <style>
        body {
            background-color: #090d16;
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            align-items: center;
            min-height: 100vh;
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            color: #f8fafc;
        }
        
        .container {
            width: 100%;
            max-width: 1000px;
            padding: 40px 20px;
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 32px;
        }

        .welcome-hero {
            text-align: center;
            margin-bottom: 20px;
        }

        .welcome-hero h1 {
            font-size: 36px;
            font-weight: 800;
            margin: 0 0 12px 0;
            background: linear-gradient(135deg, #60a5fa 0%, #a855f7 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .welcome-hero p {
            font-size: 16px;
            color: #94a3b8;
            margin: 0;
            max-width: 500px;
            line-height: 1.6;
        }

        .login-card {
            background: rgba(30, 41, 59, 0.4);
            border: 1px solid rgba(255, 255, 255, 0.08);
            border-radius: 24px;
            padding: 40px;
            text-align: center;
            max-width: 420px;
            width: 100%;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 24px;
        }

        .login-card-title {
            font-size: 20px;
            font-weight: 700;
            margin: 0;
        }

        .login-card-desc {
            font-size: 14px;
            color: #94a3b8;
            margin: 0;
            line-height: 1.5;
        }
        
        .info-bar {
            width: 100%;
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-radius: 12px;
            padding: 12px 20px;
            font-size: 14px;
            text-align: center;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="welcome-hero">
        <h1>OIDC Client SDK</h1>
        <p>Framework-agnostic OpenID Connect Client library for modern PHP applications.</p>
    </div>

    <?php if (!$isAuthenticated): ?>
        <!-- Render Login Interface -->
        <div class="login-card">
            <div class="login-card-title">Sign In Required</div>
            <div class="login-card-desc">Click the button below to initiate authentication flow with the SSO Identity Provider.</div>
            
            <!-- SSO Login Component -->
            <?= Ui::loginButton('login.php') ?>
        </div>
    <?php else: ?>
        <!-- Render Authenticated Profile -->
        <div style="display: flex; flex-direction: column; align-items: center; gap: 24px; width: 100%;">
            <div class="info-bar">
                ✓ Successfully authenticated. Session active.
            </div>
            
            <!-- User Profile Component -->
            <?= Ui::userProfileCard($user, 'logout.php') ?>
            
            <!-- Developer Diagnostic Dashboard Component -->
            <div style="width: 100%;">
                <?= Ui::debugDashboard($user, $token) ?>
            </div>
        </div>
    <?php endif; ?>
</div>

</body>
</html>
