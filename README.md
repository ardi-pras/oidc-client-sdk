# Framework Agnostic OpenID Connect (OIDC) Client SDK

A lightweight, premium, and framework-agnostic OpenID Connect (OIDC) Client SDK for PHP applications to easily integrate Single Sign-On (SSO).

---

## Key Features

- **Framework Agnostic**: Clean, decoupled architecture designed to run on raw PHP, Laravel, CodeIgniter 3, CodeIgniter 4, Symfony, and more.
- **Secure PKCE Flow**: Fully automated Proof Key for Code Exchange (PKCE) flow to protect code exchanges.
- **OIDC Validation**: Strict validation for state matching, nonces, audience, token expiration, and issuers.
- **JWKS Signature Verification**: Auto-discovery of JWKS documents and native signature verification using RSA public keys (RS256).
- **Premium UI Components**: Built-in, plug-and-play HTML components with modern dark-mode, glassmorphism, and responsive CSS (Inter/JetBrains Mono fonts):
  - **SSO Login Button**: Styled animated login button.
  - **User Profile Card**: Elegant sidebar card with dynamic user initials avatar, name, email, and role badges.
  - **Developer Debug Dashboard**: Interactive debugging panel containing session metadata, collapsible tokens with copy-to-clipboard, and JSON claims inspector.

---

## Requirements

- PHP 8.1 or higher
- `curl` and `openssl` extensions enabled

---

## Installation

Install the package via Composer:

```bash
composer require oidc-client/sdk
```

---

## Quick Start

### 1. Build Client Instance

Configure the builder with your SSO provider credentials:

```php
use OidcClient\OidcClient;

$oidc = OidcClient::builder()
    ->issuer('https://service.unisayogya.ac.id/sso')
    ->clientId('your_client_id')
    ->clientSecret('your_client_secret')
    ->redirectUri('https://your-app.com/sso/callback')
    ->build();
```

### 2. Initiate Authorization Flow
Redirect the user to the Identity Provider:

```php
$oidc->login();
```

### 3. Handle SSO Callback
Parse and authenticate the callback queries on your redirect URI:

```php
$result = $oidc->authenticate($_GET);

if ($result->isAuthenticated()) {
    $user = $result->user();
    $token = $result->token();
    
    // User is authenticated! Save credentials or login session.
    echo "Welcome back, " . $user->name();
} else {
    echo "Authentication failed: " . $result->error();
}
```

---

## Framework Integration Guides

Detailed step-by-step guides are provided for the following frameworks:

- 🛠️ **[Laravel Integration Guide](docs/integration/laravel.md)**
- 🚀 **[CodeIgniter 4 Integration Guide](docs/integration/codeigniter4.md)**
- 📦 **[CodeIgniter 3 Integration Guide](docs/integration/codeigniter3.md)**

---

## UI Components Rendering

Render beautiful components using the `OidcClient\Presentation\Ui` class directly in your templates/Blade views:

```php
use OidcClient\Presentation\Ui;

// 1. Render Login Button
echo Ui::loginButton('https://your-app.com/sso/login');

// 2. Render Profile Card
echo Ui::userProfileCard($oidc->user(), 'https://your-app.com/sso/logout');

// 3. Render Debug Dashboard
echo Ui::debugDashboard($oidc->user(), $oidc->token());
```

---

## Running Tests

Verify implementation using the PHPUnit test suite:

```bash
composer install
./vendor/bin/phpunit
```
