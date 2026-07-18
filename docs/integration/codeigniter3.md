# Panduan Lengkap Integrasi OIDC SSO dengan CodeIgniter 3 (Langkah-demi-Langkah)

Dokumen ini adalah panduan langkah-demi-langkah, sangat rinci, untuk mengintegrasikan paket OIDC SSO ke aplikasi CodeIgniter 3. Ikuti setiap langkah secara berurutan — jangan lewatkan satupun.

---

## Ringkasan singkat (apa yang akan dilakukan)

- Pasang paket SDK via Composer
- Aktifkan autoload Composer di CodeIgniter 3
- Salin/konfigurasi controller integrasi (`OidcController`)
- Tambahkan konfigurasi `application/config/oidc.php`
- Tambahkan route SSO (`sso-login`, `sso-callback`, `sso-logout`)
- Tambahkan tombol login di view dan proteksi route
- Uji end-to-end dan lakukan troubleshooting

Catatan autoload: paket sudah menyediakan PSR-4 mapping `OidcClient\` → `packages/php/core/src/` di `composer.json`. Jika Anda menggunakan namespaced controller, cukup aktifkan `composer_autoload` di CI3.

---

## 1. Prasyarat

Pastikan:

- Aplikasi CodeIgniter 3 berjalan (PHP 8.1+ direkomendasikan)
- Composer terinstal dan dapat dijalankan dari folder proyek
- Anda memiliki kredensial dari provider OIDC/SSO: `issuer` (atau authorization endpoint), `client_id`, `client_secret`, dan `redirect_uri`
- Redirect URI telah didaftarkan di provider SSO dan cocok persis

---

## 2. Instalasi paket SDK (Composer)

1. Dari root proyek CodeIgniter jalankan:

```bash
composer require oidc-client/sdk
```

2. Jika `vendor/autoload.php` belum dimuat oleh CI, aktifkan Composer autoload di `application/config/config.php`:

```php
$config['composer_autoload'] = TRUE; // atau 'vendor/autoload.php'
```

3. Pastikan autoload library `session` aktif di `application/config/autoload.php`:

```php
$autoload['libraries'] = array('session');
```

4. Setelah instalasi, jalankan:

```bash
composer dump-autoload -o
```

Catatan: Jika aplikasi Anda berjalan di shared host, pastikan PHP CLI versi yang dipakai kompatibel.

---

## Contoh `composer.json` untuk aplikasi

Jika Anda ingin mengelola dependensi aplikasi via Composer (direkomendasikan), berikut contoh `composer.json` minimal yang menunjukkan bagaimana menambah paket SDK dan mengaktifkan PSR-4 autoload bila diperlukan:

```json
{
    "name": "your-vendor/your-app",
    "require": {
        "php": "^8.1",
        "oidc-client/sdk": "^1.0"
    },
    "autoload": {
        "psr-4": {
            "App\\": "application/",
            "OidcClient\\": "vendor/oidc-client/sdk/packages/php/core/src/"
        }
    }
}
```

Catatan:

- Anda biasanya tidak perlu memetakan `OidcClient\` pada `autoload` aplikasi — paket sudah terinstall di `vendor/` dan Composer akan mengurus autoloading. Mapping ditunjukkan di atas hanya bila Anda ingin mengoverride atau memuat file sumber secara langsung selama pengembangan.
- Setelah membuat/merubah `composer.json`, jalankan `composer install` atau `composer update` lalu `composer dump-autoload -o`.

---

## 3. Struktur file dan pilihan integrasi

Anda punya dua pilihan untuk menggunakan controller integrasi:

- A. Salin controller integrasi yang disediakan paket ke `application/controllers/OidcController.php` (direkomendasikan untuk CI3 klasik).
- B. Gunakan controller yang namespaced dan biarkan Composer autoload memuatnya — ini memerlukan pengaturan autoload yang benar dan pemanggilan class fully-qualified.

Langkah-langkah di bawah ini menjelaskan cara A (paling sederhana) dan catatan untuk cara B.

---

## 4. Menyalin controller integrasi (Cara A — langkah demi langkah)

1. Copy file contoh controller dari paket ke folder aplikasi controllers:

Windows PowerShell:

```powershell
copy vendor\oidc-client\sdk\packages\php\core\src\Integration\CodeIgniter3\OidcController.php application\controllers\OidcController.php
```

Linux/macOS:

```bash
cp vendor/oidc-client/sdk/packages/php/core/src/Integration/CodeIgniter3/OidcController.php application/controllers/OidcController.php
```

2. Buka `application/controllers/OidcController.php` dan pastikan:

- Jika file mengandung namespace (`namespace OidcClient\Integration\CodeIgniter3;`), hapus namespace atau sesuaikan autoload composer pada `composer.json` untuk memuat namespace tersebut.
- Class harus bernama `OidcController` dan file harus berada di `application/controllers/OidcController.php`.
- Jika Anda menolak namespace, ubah deklarasi class menjadi: `class OidcController extends CI_Controller`.

Contoh sederhana (tanpa namespace) yang cocok di CI3:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use OidcClient\OidcClient;

class OidcController extends CI_Controller
{
        public function __construct()
        {
                parent::__construct();
                $this->load->library('session');
                $this->load->helper('url');
                $this->config->load('oidc', TRUE);
                // Inisialisasi OidcClient seperti contoh di paket
        }
}
```

---

## 5. Menambahkan konfigurasi OIDC

---

## Integrasi Sederhana (Direkomendasikan)

Untuk menyederhanakan integrasi tanpa menyalin banyak kode, gunakan wrapper `OidcService` yang disediakan paket. Cukup salin controller kecil berikut ke `application/controllers/OidcController.php`.

Contoh controller minimal yang menggunakan `OidcService`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

use OidcClient\Integration\OidcService;

class OidcController extends CI_Controller
{
    private OidcService $service;

    public function __construct()
    {
        parent::__construct();
        $this->load->library('session');
        $this->load->helper('url');
        $this->config->load('oidc', TRUE);

        $config = $this->config->item('oidc') ?? [];
        $this->service = new OidcService($config);
    }

    public function login()
    {
        redirect($this->service->beginAuthentication());
    }

    public function callback()
    {
        $result = $this->service->authenticate($this->input->get());
        if ($result->isAuthenticated()) {
            redirect($this->config->item('oidc_redirect_on_success') ?? 'dashboard');
        }

        $this->session->set_flashdata('error', $result->error() ?? 'Authentication failed.');
        redirect($this->config->item('oidc_redirect_on_failure') ?? 'login');
    }

    public function logout()
    {
        $this->service->logout();
        redirect($this->config->item('oidc_redirect_on_logout') ?? '/');
    }
}
```

1. Buat file `application/config/oidc.php` jika belum ada, lalu isi dengan contoh konfigurasi berikut:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return [
        'authorization_endpoint' => 'https://provider.example.com/authorize',
        'issuer' => null, // optional jika Anda menggunakan 'authorization_endpoint'
        'client_id' => 'your_client_id',
        'client_secret' => 'your_client_secret',
        'redirect_uri' => 'https://your-app.com/sso-callback',
        'scope' => ['openid','profile','email'],
        'oidc_redirect_on_success' => 'dashboard',
        'oidc_redirect_on_failure' => 'login',
        'oidc_redirect_on_logout' => '/',
];
```

> Pastikan `oidc_client_id`, `oidc_client_secret`, dan `oidc_redirect_uri` diisi sebelum memulai login. Nilai yang hilang akan memicu exception konfigurasi yang jelas.

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

return [
        'authorization_endpoint' => 'https://provider.example.com/authorize',
        'issuer' => null, // optional jika Anda menggunakan 'authorization_endpoint'
        'client_id' => 'your_client_id',
        'client_secret' => 'your_client_secret',
        'redirect_uri' => 'https://your-app.com/sso-callback',
        'scope' => ['openid','profile','email'],
        'oidc_redirect_on_success' => 'dashboard',
        'oidc_redirect_on_failure' => 'login',
        'oidc_redirect_on_logout' => '/',
];
```

2. Pastikan nilai `oidc_redirect_uri` persis sama dengan yang terdaftar pada provider SSO.

---

## 6. Menambahkan route SSO

Buka `application/config/routes.php` dan tambahkan:

```php
$route['sso-login'] = 'OidcController/login';
$route['sso-callback'] = 'OidcController/callback';
$route['sso-logout'] = 'OidcController/logout';
```

Jika Anda meletakkan controller dalam subfolder, sesuaikan pathnya.

---

## 7. Tombol Login di View (langkah demi langkah)

1. Edit file view login Anda, mis. `application/views/login.php`.
2. Tampilkan pesan error flash apabila ada:

```php
<?php if ($this->session->flashdata('error')): ?>
        <div class="alert alert-danger"><?= $this->session->flashdata('error') ?></div>
<?php endif; ?>
```

3. Tambahkan tombol/link menuju route login:

```php
<a href="<?= site_url('sso-login') ?>" class="btn btn-primary">Masuk dengan SSO</a>
```

4. (Opsional) Gunakan `OidcClient\Presentation\Ui::loginButton()` jika Anda memindahkan presentation layer ke autoload.

---

## 8. Proteksi halaman dan contoh controller yang menggunakan SSO

Contoh controller yang memaksa autentikasi:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Dashboard extends CI_Controller
{
        public function index()
        {
                $this->config->load('oidc', TRUE);
                $config = $this->config->item('oidc');

                $oidc = \OidcClient\OidcClient::builder()
                        ->fromArray([
                                'authorization_endpoint' => $config['authorization_endpoint'] ?? null,
                                'client_id' => $config['oidc_client_id'],
                                'client_secret' => $config['oidc_client_secret'],
                                'redirect_uri' => $config['oidc_redirect_uri'],
                                'scope' => $config['oidc_scope'] ?? ['openid','profile','email'],
                        ])
                        ->build();

                if (!$oidc->isAuthenticated()) {
                        redirect('sso-login');
                        return;
                }

                $this->load->view('dashboard');
        }
}
```

---

## Verifikasi setelah pemasangan (checklist singkat)

Setelah menjalankan `composer require oidc-client/sdk` dan `composer dump-autoload -o`, periksa hal berikut pada root proyek:

- `composer.json` berisi paket `oidc-client/sdk` pada bagian `require`.
- `composer.json` memiliki `autoload.psr-4` entry untuk `OidcClient\` yang menunjuk ke `vendor/oidc-client/sdk/packages/php/core/src/`.
- Jika aplikasi Laravel: `extra.laravel.providers` berisi `OidcClient\\Integration\\Laravel\\OidcServiceProvider`.
- Jika aplikasi CodeIgniter: `application/config/config.php` harus memiliki:

```php
$config['composer_autoload'] = TRUE;
```

Jika salah satu tidak muncul, periksa file backup yang dibuat oleh plugin:

- `composer.json.oidc.bak`
- `application/config/config.php.oidc.bak`

Lakukan `composer dump-autoload -o` lagi setelah perbaikan.

---

## 9. Logout (langkah demi langkah)

1. Akses `https://your-app.com/sso-logout` (atau route yang Anda tentukan).
2. Controller akan memanggil `$this->oidc->logout()` dan mengarahkan pengguna ke `oidc_redirect_on_logout`.

---

## 10. Pengujian end-to-end (cek manual)

1. Buka browser incognito/private.
2. Akses `https://your-app.com/sso-login` — seharusnya diarahkan ke provider SSO.
3. Login di provider SSO; provider harus mengarahkan kembali ke `sso-callback`.
4. Setelah callback, pastikan aplikasi mengarahkan ke halaman sukses (`dashboard`).

Jika langkah ini gagal, gunakan langkah troubleshooting di bagian berikut.

---

## 11. Troubleshooting (cek satu-per-satu)

- Tidak terjadi redirect ke provider:
    - Periksa `client_id` & `client_secret`.
    - Periksa `oidc_redirect_uri` — harus persis sama.
    - Jalankan `composer dump-autoload` lagi.

- Callback mengembalikan error atau tidak terautentikasi:
    - Periksa apakah session disimpan: cek `application/config/config.php` `sess_driver` dan `sess_save_path`.
    - Pastikan cookie domain/path cocok.
    - Periksa log provider untuk error pada client/redirect.

- State / nonce tidak cocok:
    - Mungkin sesi ter-reset (cek ukuran cookie, domain, dan path).

- UI atau helper tidak muncul:
    - Pastikan class Presentation di-autoload atau namespace dipanggil lengkap.

---

## 12. Catatan keamanan dan best practices

- Jangan simpan `client_secret` di repo publik; gunakan mekanisme proteksi environment.
- Gunakan HTTPS di semua environment produksi.
- Batasi `scope` hanya kebutuhan minimal.
- Selalu perbarui paket dan periksa CVE yang relevan.

---

## 13. Catatan untuk integrasi berbasis namespace (opsional)

Jika Anda ingin menggunakan controller namespaced (yang disediakan paket), lakukan:

1. Pastikan `composer.json` memiliki autoload PSR-4 yang memetakan namespace `OidcClient\Integration\CodeIgniter3` ke path paket (biasanya sudah tersedia di paket vendor).
2. Aktifkan `composer_autoload` di `application/config/config.php`.
3. Panggil controller namespaced dari route dengan full class name sesuai konfigurasi CI3 dengan HMVC atau menggunakan route closure yang memanggil class via `new`.

Jika membutuhkan, saya bisa menuliskan langkah detail untuk opsi namespace.

## 3. Konfigurasi OIDC

Buat file konfigurasi `application/config/oidc.php`:

```php
<?php
defined('BASEPATH') OR exit('No direct script access allowed');

$config['authorization_endpoint'] = 'https://service.unisayogya.ac.id/sso/authorize.php';
$config['oidc_client_id'] = 'your_client_id';
$config['oidc_client_secret'] = 'your_client_secret';
$config['oidc_redirect_uri'] = 'https://your-app.com/sso-callback';
$config['oidc_scope'] = array('openid', 'profile', 'email');

// Redirect setelah event tertentu
$config['oidc_redirect_on_success'] = 'dashboard';
$config['oidc_redirect_on_failure'] = 'login';
$config['oidc_redirect_on_logout'] = '/';
```

Catatan penting:

- `oidc_redirect_uri` harus sama persis dengan URL callback yang terdaftar di server SSO.
- `oidc_scope` biasanya minimal berisi `openid`, `profile`, dan `email`.
- Jika Anda ingin mengarahkan pengguna ke halaman tertentu setelah login atau logout, atur nilai redirect di atas.

---

## 4. Menyiapkan Route

Buka file `application/config/routes.php` lalu tambahkan route berikut:

```php
$route['sso-login'] = 'OidcController/login';
$route['sso-callback'] = 'OidcController/callback';
$route['sso-logout'] = 'OidcController/logout';
```

Dengan konfigurasi ini, URL berikut akan tersedia:

- `/sso-login` untuk memulai login SSO
- `/sso-callback` untuk menangani callback dari provider SSO
- `/sso-logout` untuk mengakhiri sesi SSO

---

## 5. Menyalin Controller Bawaan

CodeIgniter 3 tidak otomatis memuat controller integrasi dari paket. Salin file controller yang sudah disediakan ke folder aplikasi Anda:

```powershell
copy vendor/oidc-client/sdk/packages/php/core/src/Integration/CodeIgniter3/OidcController.php application/controllers/OidcController.php
```

Jika Anda menggunakan terminal Linux/macOS, gunakan:

```bash
cp vendor/oidc-client/sdk/packages/php/core/src/Integration/CodeIgniter3/OidcController.php application/controllers/OidcController.php
```

Controller ini akan menangani proses login, callback, dan logout secara otomatis.

---

## 6. Menggunakan Tombol Login di View

Contoh sederhana pada `application/views/login.php`:

```php
<?php if ($this->session->flashdata('error')): ?>
    <p style="color: red;"><?= $this->session->flashdata('error') ?></p>
<?php endif; ?>

<?= OidcClient\Presentation\Ui::loginButton(site_url('sso-login')) ?>
```

Kode di atas akan menampilkan tombol login yang sudah diberi styling modern.

---

## 7. Menampilkan Data User Setelah Login

Pada halaman dashboard atau halaman yang membutuhkan data user, Anda dapat membuat instance client lokal lalu memeriksa status autentikasi.

Contoh pada `application/views/dashboard.php`:

```php
<?php
$CI =& get_instance();
$CI->config->load('oidc', TRUE);
$config = $CI->config->item('oidc');

$oidc = \OidcClient\OidcClient::builder()
    ->fromArray(array(
        'authorization_endpoint' => $config['authorization_endpoint'] ?? null,
        'client_id' => $config['oidc_client_id'],
        'client_secret' => $config['oidc_client_secret'],
        'redirect_uri' => $config['oidc_redirect_uri'],
        'scope' => $config['oidc_scope'] ?? array('openid', 'profile', 'email')
    ))
    ->build();
?>

<?php if ($oidc->isAuthenticated()): ?>
    <?= OidcClient\Presentation\Ui::userProfileCard($oidc->user(), site_url('sso-logout')) ?>

    <?php if (ENVIRONMENT === 'development'): ?>
        <?= OidcClient\Presentation\Ui::debugDashboard($oidc->user(), $oidc->token()) ?>
    <?php endif; ?>
<?php endif; ?>
```

Komponen ini akan menampilkan:

- kartu profil pengguna
- tombol logout
- dashboard debug (hanya saat environment development)

---

## 8. Melindungi Halaman yang Membutuhkan Login

Karena CodeIgniter 3 tidak menyediakan filter bawaan untuk paket ini, Anda dapat melakukan pemeriksaan manual sebelum menampilkan halaman.

Contoh pada controller:

```php
public function index()
{
    $this->config->load('oidc', TRUE);
    $config = $this->config->item('oidc');

    $oidc = \OidcClient\OidcClient::builder()
        ->fromArray(array(
            'authorization_endpoint' => $config['authorization_endpoint'] ?? null,
            'client_id' => $config['oidc_client_id'],
            'client_secret' => $config['oidc_client_secret'],
            'redirect_uri' => $config['oidc_redirect_uri'],
            'scope' => $config['oidc_scope'] ?? array('openid', 'profile', 'email')
        ))
        ->build();

    if (!$oidc->isAuthenticated()) {
        redirect('sso-login');
    }

    $this->load->view('dashboard');
}
```

Dengan demikian, pengguna yang belum login akan otomatis diarahkan ke proses SSO.

---

## 9. Logout

Logout dapat dilakukan melalui route `/sso-logout`. Controller bawaan akan memanggil `logout()` pada SDK dan mengarahkan pengguna ke halaman yang Anda tentukan.

Contoh default redirect setelah logout:

```php
$config['oidc_redirect_on_logout'] = '/';
```

---

## 10. Troubleshooting

### Login tidak redirect ke provider

Periksa hal-hal berikut:

- `oidc_client_id` dan `oidc_client_secret` benar.
- `oidc_redirect_uri` sama dengan yang terdaftar di provider SSO.
- `composer dump-autoload` dijalankan setelah instalasi.

### Callback gagal

Biasanya disebabkan oleh:

- URL callback tidak cocok.
- State atau nonce tidak valid karena sesi hilang.
- Scope tidak mencukupi.

### Session tidak tersimpan

Pastikan:

- library `session` sudah diautoload.
- cookie browser tidak diblokir.
- aplikasi Anda menggunakan base URL yang benar.

---

## 11. Rekomendasi Keamanan

- Jangan simpan client secret di frontend.
- Gunakan HTTPS untuk semua endpoint.
- Simpan konfigurasi sensitif di environment server jika memungkinkan.
- Pastikan redirect URI selalu divalidasi dengan benar.

Dengan langkah di atas, Anda dapat mengintegrasikan SSO OIDC ke aplikasi CodeIgniter 3 secara aman dan rapi.
