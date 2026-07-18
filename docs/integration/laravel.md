# Panduan Lengkap Integrasi OIDC SSO dengan Laravel (Langkah-demi-Langkah)

Dokumen ini memberikan panduan rinci dan tindakan yang dapat diikuti satu-per-satu untuk mengintegrasikan paket OIDC SSO ke aplikasi Laravel (10/11).

---

## Ringkasan singkat (apa yang akan dilakukan)

- Pasang paket SDK via Composer
- Publish konfigurasi paket dan set environment variables
- Daftarkan service provider / bindings bila perlu
- Tambahkan route SSO dan middleware untuk proteksi
- Tambahkan tombol login di Blade dan tampilkan profil user
- Tes end-to-end dan debugging langkah demi langkah

Catatan autoload: paket menyertakan PSR-4 mapping `OidcClient\` → `packages/php/core/src/` pada `composer.json`, jadi namespaced classes (mis. `OidcClient\Integration\OidcService`) dapat diinject langsung setelah menjalankan `composer install` dan mengaktifkan autoload bila perlu.

---

## 1. Prasyarat

Pastikan:

- Aplikasi Laravel (10/11) berjalan
- Composer telah terinstal
- Anda mempunyai kredensial OIDC dari provider SSO: `authorization_endpoint`/`issuer`, `client_id`, `client_secret`, dan `redirect_uri`
- URL `redirect_uri` sudah didaftarkan di provider SSO dan cocok persis

---

## 2. Instalasi paket (langkah demi langkah)

1. Di folder proyek Laravel jalankan:

```bash
composer require oidc-client/sdk
```

2. Setelah selesai, update autoload:

```bash
composer dump-autoload -o
```

3. Jika paket menyediakan `OidcServiceProvider`, publish konfigurasi:

```bash
php artisan vendor:publish --tag=oidc-config
```

Perintah ini akan membuat file konfigurasi `config/oidc.php`.

---

## Contoh `composer.json` untuk aplikasi Laravel

Berikut contoh `composer.json` yang menunjukkan bagaimana menambahkan paket SDK pada aplikasi Laravel dan memastikan autoload PSR-4 untuk kode aplikasi Anda:

```json
{
    "name": "your-vendor/your-laravel-app",
    "require": {
        "php": "^8.1",
        "laravel/framework": "^10.0|^11.0",
        "oidc-client/sdk": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "app/"
        }
    },
    "extra": {
        "laravel": {
            "providers": [
                "OidcClient\\Integration\\Laravel\\OidcServiceProvider"
            ]
        }
    }
}
```

Catatan:

- Jalankan `composer require oidc-client/sdk` untuk menambahkan paket dengan cepat.
- `extra.laravel.providers` bukanlah keharusan jika paket menyediakan autodiscovery, tapi dapat membantu bila Anda ingin mendaftarkan provider secara eksplisit.

---

## 3. Konfigurasi environment dan file config

1. Tambahkan variabel berikut ke file `.env` Anda (ganti nilai sesuai provider):

```env
OIDC_AUTHORIZATION_ENDPOINT=https://provider.example.com/authorize
OIDC_CLIENT_ID=your_client_id
OIDC_CLIENT_SECRET=your_client_secret
OIDC_REDIRECT_URI=https://your-app.com/sso/callback
```

2. Periksa isi `config/oidc.php` yang dipublish — seharusnya merefer ke `env()` di atas. Contoh minimal:

```php
return [
        'authorization_endpoint' => env('OIDC_AUTHORIZATION_ENDPOINT'),
        'client_id' => env('OIDC_CLIENT_ID'),
        'client_secret' => env('OIDC_CLIENT_SECRET'),
        'redirect_uri' => env('OIDC_REDIRECT_URI'),
        'scope' => ['openid','profile','email'],
        'verify_tls' => env('OIDC_VERIFY_TLS', true),
        'redirect_on_success' => '/dashboard',
        'redirect_on_failure' => '/login',
        'redirect_on_logout' => '/',
];
```

3. Jalankan perintah konfigurasi cache (opsional, di production):

```bash
php artisan config:clear
php artisan config:cache
```

---

## 4. Registrasi Service Provider & Binding (jika diperlukan)

1. Jika paket tidak didaftarkan otomatis (autodiscovery), tambahkan provider di `config/app.php` pada `providers[]`:

```php
OidcClient\Integration\Laravel\OidcServiceProvider::class,
```

2. `OidcServiceProvider` mendaftarkan `OidcService` ke container (dan juga mengikat `OidcClient` untuk kompatibilitas).

Contoh yang disediakan oleh paket:

```php
$this->app->singleton(\OidcClient\Integration\OidcService::class, function ($app) {
    return new \OidcClient\Integration\OidcService($app['config']->get('oidc') ?? []);
});

// Optional: bind underlying client for backwards compatibility
$this->app->singleton(\OidcClient\OidcClient::class, function ($app) {
    return $app->make(\OidcClient\Integration\OidcService::class)->getClient();
});
```

3. Jika binding tidak tersedia atau Anda ingin menyesuaikannya, tambahkan binding di `AppServiceProvider::register()`.

### Otomatisasi: pembaruan `composer.json` oleh paket

Paket menyertakan Composer plugin kecil yang akan mencoba menambahkan entry `extra.laravel.providers` ke file `composer.json` root saat paket di-install atau di-update. Tujuannya: menyederhanakan pendaftaran `OidcServiceProvider` untuk aplikasi Laravel.

Hal yang perlu diketahui:

- Plugin hanya menambahkan `OidcClient\\Integration\\Laravel\\OidcServiceProvider` ke `extra.laravel.providers` jika belum ada.
- Plugin membuat backup `composer.json.oidc.bak` sebelum menulis perubahan.
- Jika `composer.json` tidak dapat dibaca atau ditulis oleh proses Composer, plugin akan menampilkan pesan dan tidak mengubah file.

Jika Anda tidak ingin perilaku ini, Anda dapat menghapus plugin dengan men-set `type` kembali pada `composer.json` atau menonaktifkannya saat memasang paket.

---

## Verifikasi setelah pemasangan (checklist singkat)

Setelah menjalankan `composer require oidc-client/sdk` dan `composer dump-autoload -o`, pastikan:

- `composer.json` root memiliki paket `oidc-client/sdk` di `require`.
- `composer.json` root memiliki `autoload.psr-4` mapping `OidcClient\` → `vendor/oidc-client/sdk/packages/php/core/src/`.
- `extra.laravel.providers` berisi `OidcClient\\Integration\\Laravel\\OidcServiceProvider` (plugin menambahkannya otomatis bila memungkinkan).
- Jika Anda mempublish config, jalankan `php artisan config:clear`.

## Rollback perubahan otomatis

Plugin membuat backup sebelum menulis; untuk mengembalikan perubahan jika perlu:

```bash
cp composer.json.oidc.bak composer.json
composer dump-autoload -o
```

Untuk CodeIgniter, jika plugin mengubah `application/config/config.php`, pulihkan backup:

```bash
cp application/config/config.php.oidc.bak application/config/config.php
```

Jika backup tidak tersedia, pulihkan file dari VCS atau cadangan Anda.

## 5. Menambahkan route SSO (langkah demi langkah)

1. Di `routes/web.php` tambahkan:

```php
use OidcClient\Integration\Laravel\OidcController;

Route::get('/sso/login', [OidcController::class, 'login'])->name('sso.login');
Route::get('/sso/callback', [OidcController::class, 'callback'])->name('sso.callback');
Route::get('/sso/logout', [OidcController::class, 'logout'])->name('sso.logout');
```

2. Jika Anda menggunakan route group (middleware web), pastikan route berada di dalam group tersebut agar session berfungsi.

---

## 6. Implementasi Blade: Tombol Login dan Menampilkan Profil

1. Tambahkan tombol login di `resources/views/auth/login.blade.php`:

```blade
<a href="{{ route('sso.login') }}" class="btn btn-primary">Masuk dengan SSO</a>
```

2. Tampilkan profil di view dashboard (contoh `resources/views/dashboard.blade.php`):

```blade
@inject('oidcService', 'OidcClient\Integration\OidcService')

@if ($oidcService->isAuthenticated())
    {!! OidcClient\Presentation\Ui::userProfileCard($oidcService->user(), route('sso.logout')) !!}
@endif
```

3. Untuk debug token/claims, tampilkan hanya di `app.debug` true.

---

## 7. Middleware kustom untuk proteksi route (langkah demi langkah)

1. Buat middleware:

```bash
php artisan make:middleware RequireOidcAuthentication
```

2. Edit `app/Http/Middleware/RequireOidcAuthentication.php`:

```php
<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OidcClient\OidcClient;

class RequireOidcAuthentication
{
        public function handle(Request $request, Closure $next)
        {
                $oidc = app(OidcClient::class);

                if (!$oidc->isAuthenticated()) {
                        return redirect()->route('sso.login');
                }

                return $next($request);
        }
}
```

3. Daftarkan middleware di `app/Http/Kernel.php`, tambahkan alias mis. `'auth.oidc' => \App\Http\Middleware\RequireOidcAuthentication::class,` dan gunakan pada route.

---

## 8. Logout dan session handling

1. Akses `/sso/logout` — controller akan memanggil `logout()` pada SDK dan mengarahkan ke `redirect_on_logout`.
2. Pastikan session driver Laravel (`SESSION_DRIVER`) bekerja (file, redis, database) agar state/nonce tersimpan.

---

## 9. Pengujian end-to-end & pemeriksaan manual

Langkah pengujian:

1. Buka browser private/incognito.
2. Kunjungi `https://your-app.com/sso/login` — harus redirect ke provider SSO.
3. Login pada provider; provider mengembalikan ke `sso/callback`.
4. Aplikasi memproses callback dan redirect ke `redirect_on_success`.

Jika gagal, ikuti langkah debugging di bagian berikut.

---

## 10. Troubleshooting (cek berurutan)

- Tidak redirect ke provider:
    - Periksa `OIDC_CLIENT_ID` dan `OIDC_CLIENT_SECRET`.
    - Periksa `OIDC_AUTHORIZATION_ENDPOINT` atau `issuer` di `config/oidc.php`.
    - Pastikan route `/sso/login` tersedia dan tidak terkena middleware yang memblok.

- Callback error / tidak terautentikasi:
    - Pastikan session Laravel menyimpan data (cek cookie `laravel_session`).
    - Jalankan `php artisan config:clear` dan `php artisan cache:clear` jika perubahan config tidak terpakai.
    - Periksa log Laravel (`storage/logs/laravel.log`) untuk exception.

- Middleware selalu redirect (loop):
    - Pastikan middleware memeriksa `$oidc->isAuthenticated()` bukan `$request->user()` kecuali Anda sinkronkan user.

- UI tidak muncul:
    - Pastikan class `OidcClient\Presentation\Ui` tersedia via autoload.

---

## 11. Debugging tingkat lanjut

- Untuk melihat parameter yang dikirim: sebelum redirect ke provider, log URL yang dikembalikan oleh `$oidc->authentication()->beginAuthentication()`.
- Untuk melihat callback payload, log `request()->all()` di route `sso/callback` sementara (hapus setelah debugging).

---

## 12. Rekomendasi keamanan

- Gunakan HTTPS untuk produksi.
- Jangan commit `.env` ke VCS.
- Batasi scope hanya yang perlu.
- Aktifkan `verify_tls` kecuali di environment dev yang terkontrol.

---

Jika Anda ingin, saya bisa menambahkan contoh unit/integration test untuk middleware dan controller ini.

## 3. Registrasi Service Provider

Paket ini menyediakan class `OidcServiceProvider` yang mendaftarkan instance `OidcClient` ke container Laravel.

### Laravel 10 dan sebelumnya
Tambahkan provider ke file `config/app.php` pada bagian `providers`:

```php
OidcClient\Integration\Laravel\OidcServiceProvider::class,
```

### Laravel 11+
Anda dapat mengaktifkan provider secara otomatis melalui `bootstrap/providers.php` jika dibutuhkan, atau biarkan Laravel memuat provider secara otomatis sesuai struktur aplikasi Anda.

Publish file konfigurasi paket:

```bash
php artisan vendor:publish --tag=oidc-config
```

Perintah tersebut akan membuat file `config/oidc.php`.

---

## 4. Konfigurasi OIDC

Tambahkan konfigurasi ke file `.env` Anda:

```env
OIDC_AUTHORIZATION_ENDPOINT=https://service.unisayogya.ac.id/sso/authorize.php
OIDC_CLIENT_ID=your_client_id
OIDC_CLIENT_SECRET=your_client_secret
OIDC_REDIRECT_URI=https://your-app.com/sso/callback
```

File konfigurasi yang dipublikasikan biasanya berisi struktur seperti berikut:

```php
<?php

return [
    'authorization_endpoint' => env('OIDC_AUTHORIZATION_ENDPOINT'),
    'client_id' => env('OIDC_CLIENT_ID'),
    'client_secret' => env('OIDC_CLIENT_SECRET'),
    'redirect_uri' => env('OIDC_REDIRECT_URI'),
    'scope' => ['openid', 'profile', 'email'],
    'verify_tls' => env('OIDC_VERIFY_TLS', true),
    'redirect_on_success' => '/dashboard',
    'redirect_on_failure' => '/login',
    'redirect_on_logout' => '/',
];
```

Catatan penting:

- `OIDC_REDIRECT_URI` harus sama persis dengan URL callback yang terdaftar di provider SSO.
- Nilai `scope` minimal berisi `openid`, `profile`, dan `email`.
- `verify_tls` disarankan aktif (`true`) kecuali Anda sedang menguji environment lokal dengan sertifikat khusus.

---

## 5. Menyiapkan Route SSO

Buka file `routes/web.php` lalu tambahkan route berikut:

```php
use OidcClient\Integration\Laravel\OidcController;

Route::get('/sso/login', [OidcController::class, 'login'])->name('sso.login');
Route::get('/sso/callback', [OidcController::class, 'callback'])->name('sso.callback');
Route::get('/sso/logout', [OidcController::class, 'logout'])->name('sso.logout');
```

Dengan konfigurasi di atas, aplikasi Anda akan memiliki:

- `/sso/login` untuk memulai login SSO
- `/sso/callback` untuk menerima callback dari provider
- `/sso/logout` untuk menghapus sesi SSO

---

## 6. Alur Login yang Terjadi

Secara umum, alur login akan berjalan seperti ini:

1. User mengakses halaman login aplikasi Anda.
2. Aplikasi mengarahkan user ke endpoint `/sso/login`.
3. Controller bawaan memanggil `beginAuthentication()` dari SDK.
4. User diarahkan ke provider SSO.
5. Setelah berhasil login, provider mengembalikan user ke `/sso/callback`.
6. Controller bawaan memproses callback dan mengarahkan user ke halaman dashboard atau halaman yang ditentukan.

---

## 7. Menampilkan Tombol Login di Blade

Contoh sederhana pada view `resources/views/auth/login.blade.php`:

```blade
<div class="flex items-center justify-center min-h-screen bg-slate-900">
    <div class="p-8 bg-slate-800 rounded-xl shadow-lg border border-slate-700">
        <h2 class="text-white text-xl font-bold mb-6">Welcome Back</h2>
        {!! OidcClient\Presentation\Ui::loginButton(route('sso.login')) !!}
    </div>
</div>
```

UI ini akan tampil dengan desain modern dan siap dipakai.

---

## 8. Menampilkan Profil User dan Debug Dashboard

Pada file Blade dashboard Anda, misalnya `resources/views/dashboard.blade.php`:

```blade
@inject('oidcService', 'OidcClient\Integration\OidcService')

@if ($oidcService->isAuthenticated())
    <div class="dashboard-sidebar">
        {!! OidcClient\Presentation\Ui::userProfileCard($oidcService->user(), route('sso.logout')) !!}
    </div>
@endif
```

Untuk menampilkan developer debug panel:

```blade
@inject('oidcService', 'OidcClient\Integration\OidcService')

@if (config('app.debug') && $oidcService->isAuthenticated())
    <div class="mt-12">
        <h4 class="text-gray-400 font-bold mb-4">SSO Debug Details</h4>
        {!! OidcClient\Presentation\Ui::debugDashboard($oidcService->user(), $oidcService->token()) !!}
    </div>
@endif
```

Panel ini sangat berguna saat Anda sedang menguji token, claim, dan metadata session.

---

## 9. Melindungi Route dengan Middleware Kustom

Karena paket ini tidak menyediakan middleware Laravel bawaan, Anda bisa membuat middleware sendiri untuk memeriksa status autentikasi.

Buat middleware:

```bash
php artisan make:middleware RequireOidcAuthentication
```

Isi file `app/Http/Middleware/RequireOidcAuthentication.php` seperti berikut:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use OidcClient\OidcClient;

class RequireOidcAuthentication
{
    public function handle(Request $request, Closure $next)
    {
        $oidc = app(OidcClient::class);

        if (!$oidc->isAuthenticated()) {
            return redirect()->route('sso.login');
        }

        return $next($request);
    }
}
```

Daftarkan middleware di `app/Http/Kernel.php` atau file middleware bawaan Laravel 11 sesuai struktur aplikasi Anda.

Lalu terapkan ke route:

```php
Route::middleware(['auth.oidc'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard');
    });
});
```

Jika Anda ingin middleware lebih fleksibel, Anda dapat menyuntikkan `OidcClient` melalui constructor atau memanggil `app(OidcClient::class)` seperti contoh di atas.

---

## 10. Logout

Logout dapat dilakukan melalui route `/sso/logout` yang sudah disediakan. Controller bawaan akan memanggil `logout()` pada SDK dan mengarahkan user ke redirect logout yang Anda tentukan.

Contoh konfigurasi default:

```php
'redirect_on_logout' => '/',
```

---

## 11. Troubleshooting

### Redirect ke login tidak berjalan

Periksa hal-hal berikut:

- `OIDC_CLIENT_ID` dan `OIDC_CLIENT_SECRET` benar.
- `OIDC_REDIRECT_URI` sesuai dengan callback yang terdaftar.
- Service provider sudah terdaftar.
- `php artisan config:clear` dijalankan setelah mengubah `.env`.

### Callback error

Biasanya disebabkan oleh:

- URL callback tidak cocok dengan pengaturan provider.
- scope tidak sesuai.
- session browser diblokir.

### UI tidak muncul

Pastikan:

- package terinstall dengan benar.
- namespace `OidcClient\Presentation\Ui` di-import atau dipanggil lengkap.
- view Blade tidak dibungkus oleh statement yang mematikan output.

---

## 12. Rekomendasi Keamanan

- Gunakan HTTPS untuk semua endpoint SSO.
- Jangan simpan secret di repository publik.
- Batasi scope hanya untuk data yang benar-benar dibutuhkan.
- Pastikan redirect URI selalu diverifikasi oleh provider SSO.

Dengan langkah-langkah di atas, integrasi OIDC SSO pada Laravel dapat dijalankan dengan rapi, aman, dan mudah dipelihara.
