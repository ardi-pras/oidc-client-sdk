# Panduan Integrasi OIDC SSO dengan CodeIgniter 4

Dokumen ini menjelaskan langkah-langkah integrasi SDK OIDC ke aplikasi CodeIgniter 4 secara lengkap, termasuk konfigurasi, route, filter, view, dan penanganan login/logout.

---

## 1. Prasyarat

Sebelum memulai, pastikan hal-hal berikut sudah tersedia:

- Aplikasi CodeIgniter 4 sudah berjalan.
- Composer sudah terinstall.
- Anda memiliki kredensial OIDC dari penyedia SSO, termasuk `clientId`, `clientSecret`, dan `redirectUri`.
- Redirect URI aplikasi sudah terdaftar di server SSO.

---

## 2. Instalasi Paket SDK

Install paket melalui Composer:

```bash
composer require oidc-client/sdk
```

Contoh isi `composer.json` pada aplikasi Anda:

```json
{
  "require": {
    "php": "^8.1",
    "oidc-client/sdk": "^1.0"
  }
}
```

Jika Anda belum menjalankan dependency install, pastikan Composer mengunduh paket dengan benar.

---

## 3. Konfigurasi Environment dan Config

Tambahkan konfigurasi OIDC ke file `.env` Anda:

```env
oidc.authorizationEndpoint = "https://service.unisayogya.ac.id/sso/authorize.php"
oidc.clientId = "your_client_id"
oidc.clientSecret = "your_client_secret"
oidc.redirectUri = "https://your-app.com/sso/callback"
```

Buat file konfigurasi `app/Config/Oidc.php`:

```php
<?php

namespace Config;

use CodeIgniter\Config\BaseConfig;

class Oidc extends BaseConfig
{
    public string $authorizationEndpoint = '';
    public string $clientId = '';
    public string $clientSecret = '';
    public string $redirectUri = '';
    public array $scope = ['openid', 'profile', 'email'];

    public string $redirectOnSuccess = '/dashboard';
    public string $redirectOnFailure = '/login';
    public string $redirectOnLogout = '/';

    public function __construct()
    {
        parent::__construct();
        $this->authorizationEndpoint = env('oidc.authorizationEndpoint') ?? '';
        $this->clientId = env('oidc.clientId') ?? '';
        $this->clientSecret = env('oidc.clientSecret') ?? '';
        $this->redirectUri = env('oidc.redirectUri') ?? '';
    }
}
```

Nilai penting yang harus diperhatikan:

- `redirectUri` harus sama persis dengan endpoint callback yang terdaftar di provider SSO.
- `scope` biasanya berisi `openid`, `profile`, dan `email`.
- `redirectOnSuccess`, `redirectOnFailure`, dan `redirectOnLogout` mengatur navigasi setelah event tertentu.

---

## 4. Menyiapkan Route SSO

Buka file `app/Config/Routes.php` lalu tambahkan route berikut:

```php
$routes->get('sso/login', '\OidcClient\Integration\CodeIgniter4\OidcController::login');
$routes->get('sso/callback', '\OidcClient\Integration\CodeIgniter4\OidcController::callback');
$routes->get('sso/logout', '\OidcClient\Integration\CodeIgniter4\OidcController::logout');
```

Setelah itu, path berikut akan tersedia:

- `/sso/login` untuk memulai login
- `/sso/callback` untuk menerima callback dari provider
- `/sso/logout` untuk mengakhiri sesi SSO

---

## 5. Melindungi Route dengan Filter

CodeIgniter 4 menyediakan filter yang dapat dipasang ke route tertentu. Paket ini sudah menyediakan class `OidcFilter`.

Daftarkan alias filter di `app/Config/Filters.php`:

```php
public array $aliases = [
    'sso' => \OidcClient\Integration\CodeIgniter4\OidcFilter::class,
];
```

Lalu terapkan ke route yang ingin dilindungi:

```php
$routes->get('dashboard', 'Dashboard::index', ['filter' => 'sso']);
```

Jika pengguna belum login, filter akan otomatis mengarahkan mereka ke route `/sso/login`.

---

## 6. Menampilkan Tombol Login di View

Contoh pada file `app/Views/login.php`:

```php
<?= \OidcClient\Presentation\Ui::loginButton(site_url('sso/login')) ?>
```

Tombol ini akan menampilkan UI login yang sudah didesain.

---

## 7. Menampilkan Profil Pengguna Setelah Login

Contoh pada `app/Views/dashboard.php`:

```php
<?php
$oidc = \OidcClient\OidcClient::builder()
    ->fromArray((array) config('Oidc'))
    ->build();
?>

<?php if ($oidc->isAuthenticated()): ?>
    <?= \OidcClient\Presentation\Ui::userProfileCard($oidc->user(), site_url('sso/logout')) ?>

    <?php if (CI_DEBUG): ?>
        <?= \OidcClient\Presentation\Ui::debugDashboard($oidc->user(), $oidc->token()) ?>
    <?php endif; ?>
<?php endif; ?>
```

Komponen yang tampil antara lain:

- card profil pengguna
- tombol logout
- dashboard debug jika mode development aktif

---

## 8. Langkah Alur Login yang Sebenarnya

Secara umum alur yang terjadi adalah:

1. User membuka halaman login.
2. Aplikasi mengarahkan user ke provider SSO melalui route `/sso/login`.
3. Provider SSO mengembalikan user ke URL callback `/sso/callback`.
4. Controller bawaan memproses query callback dan mengautentikasi user.
5. Jika berhasil, user diarahkan ke halaman dashboard atau redirect yang Anda tentukan.

---

## 9. Logout

Logout dapat dipanggil melalui route `/sso/logout`. Controller bawaan akan memanggil `logout()` dari SDK dan mengirim user ke redirect logout yang sudah dikonfigurasi.

Contoh konfigurasi:

```php
public string $redirectOnLogout = '/';
```

---

## 10. Troubleshooting

### Redirect tidak berjalan

Periksa:

- `oidc.redirectUri` pada `.env` sesuai dengan URL aplikasi.
- Route `/sso/login` dan `/sso/callback` memang terdaftar.
- URL callback sudah diizinkan di provider SSO.

### Filter selalu mengarahkan ke login

Hal ini biasanya terjadi karena:

- user belum melewati proses login SSO
- session tidak tersimpan dengan baik
- cookie browser diblokir

### Error autentikasi

Cek nilai:

- `clientId`
- `clientSecret`
- `issuer`
- `scope`

Pastikan juga server SSO menerima scope yang diminta.

---

## 11. Rekomendasi Keamanan

- Gunakan HTTPS untuk seluruh alur SSO.
- Jangan simpan secret di frontend atau repository publik.
- Pastikan redirect URI dipasang dengan benar.
- Batasi scope hanya yang benar-benar diperlukan.

Dengan pendekatan di atas, integrasi SSO OIDC pada CodeIgniter 4 dapat dijalankan dengan aman dan terstruktur.
