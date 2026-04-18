# ZeroNet — Panel Manajemen Hotspot FreeRADIUS

Panel admin berbasis web untuk mengelola user hotspot, voucher, dan profil jaringan pada sistem **FreeRADIUS + MikroTik**.

Dibangun dengan **Laravel 13**, **Tailwind CSS**, dan **Alpine.js**.

---

## Daftar Isi

1. [Tentang Project](#1-tentang-project)
2. [Kebutuhan Server](#2-kebutuhan-server)
3. [Cara Install dari Nol](#3-cara-install-dari-nol)
4. [Konfigurasi Environment](#4-konfigurasi-environment)
5. [Perintah Terminal Lengkap](#5-perintah-terminal-lengkap)
6. [Deploy ke Production](#6-deploy-ke-production)
7. [Konfigurasi Nginx / Apache](#7-konfigurasi-nginx--apache)
8. [Integrasi FreeRADIUS](#8-integrasi-freeradius)
9. [Cronjob](#9-cronjob)
10. [Akun Login Default](#10-akun-login-default)
11. [Backup & Restore](#11-backup--restore)
12. [Update Project](#12-update-project)
13. [Struktur Folder Penting](#13-struktur-folder-penting)
14. [Troubleshooting](#14-troubleshooting)

---

## 1. Tentang Project

**ZeroNet** adalah panel manajemen hotspot yang terintegrasi langsung dengan database FreeRADIUS. Panel ini memungkinkan admin dan operator mengelola seluruh siklus hidup user hotspot tanpa perlu menyentuh database secara manual.

### Fitur Utama

| Modul | Deskripsi |
|-------|-----------|
| **Dashboard** | Statistik ringkas: total user aktif, voucher tersedia, log login terbaru |
| **User Hotspot** | CRUD user RADIUS, toggle aktif/nonaktif, reset password |
| **Paket / Profile** | Kelola grup RADIUS (`radgroupcheck`, `radgroupreply`) — kecepatan, bandwidth, profil MikroTik |
| **Voucher** | Generate voucher batch, cetak kartu, status aktif/expired/disabled |
| **Log Hotspot** | Riwayat login/logout dari `radpostauth` lengkap dengan NAS/IP |
| **Log Aktivitas** | Audit trail semua aksi admin (hanya role admin) |
| **Profil Admin** | Update nama, email, dan password akun panel |

### Role System

- **Admin** — akses penuh ke semua fitur termasuk hapus data dan log aktivitas
- **Operator** — akses terbatas: lihat, tambah, edit user; generate voucher; tidak bisa hapus atau akses log aktivitas

---

## 2. Kebutuhan Server

### Software Wajib

| Software | Versi Minimum | Catatan |
|----------|---------------|---------|
| **PHP** | 8.3+ | Dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `fileinfo`, `bcmath` |
| **Composer** | 2.x | Dependency manager PHP |
| **MySQL / MariaDB** | MySQL 8.0+ / MariaDB 10.6+ | Database utama |
| **Node.js** | 18+ | Build asset frontend |
| **npm** | 9+ | Ikut bersama Node.js |
| **Nginx** atau **Apache** | Terbaru | Web server |
| **FreeRADIUS** | 3.0+ | RADIUS server — harus berbagi database yang sama |

### Cek Versi di Server

```bash
php --version
composer --version
mysql --version
node --version
npm --version
nginx -v
freeradius -v
```

### Rekomendasi OS

Ubuntu 22.04 LTS atau Ubuntu 24.04 LTS (x86_64).

---

## 3. Cara Install dari Nol

### Langkah 1 — Copy Project ke Server

```bash
# Opsi A: via git
git clone https://github.com/username/zeronet.git /var/www/html/radius-admin

# Opsi B: upload manual, lalu ekstrak
unzip zeronet.zip -d /var/www/html/radius-admin
```

### Langkah 2 — Install Dependency PHP

```bash
cd /var/www/html/radius-admin
composer install --no-dev --optimize-autoloader
```

### Langkah 3 — Install Dependency Frontend

```bash
npm install
```

### Langkah 4 — Siapkan File Environment

```bash
cp .env.example .env
php artisan key:generate
```

### Langkah 5 — Konfigurasi Database

Edit file `.env`:

```env
APP_NAME=ZeroNet
APP_ENV=production
APP_DEBUG=false
APP_URL=http://ip-atau-domain-server-anda

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=radius
DB_USERNAME=radius_user
DB_PASSWORD=password_anda
```

> **Catatan:** Nama database (`radius`) harus sama dengan yang digunakan FreeRADIUS agar tabel seperti `radcheck`, `radreply`, `radgroupcheck`, `radgroupreply`, `radpostauth`, dan `radacct` bisa diakses langsung.

### Langkah 6 — Buat Database MySQL

```bash
mysql -u root -p
```

```sql
CREATE DATABASE radius CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'radius_user'@'localhost' IDENTIFIED BY 'password_anda';
GRANT ALL PRIVILEGES ON radius.* TO 'radius_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Langkah 7 — Jalankan Migrasi

```bash
php artisan migrate
```

### Langkah 8 — Jalankan Seeder Admin

```bash
php artisan db:seed --class=AdminSeeder
```

### Langkah 9 — Build Asset Frontend

```bash
npm run build
```

### Langkah 10 — Permission Folder

```bash
sudo chown -R www-data:www-data /var/www/html/radius-admin/storage
sudo chown -R www-data:www-data /var/www/html/radius-admin/bootstrap/cache
sudo chmod -R 775 /var/www/html/radius-admin/storage
sudo chmod -R 775 /var/www/html/radius-admin/bootstrap/cache
```

---

## 4. Konfigurasi Environment

File `.env` yang perlu disesuaikan untuk production:

```env
APP_NAME=ZeroNet
APP_ENV=production
APP_KEY=base64:...            # di-generate otomatis oleh php artisan key:generate
APP_DEBUG=false
APP_URL=https://domain-anda.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=radius
DB_USERNAME=radius_user
DB_PASSWORD=password_kuat_anda

SESSION_DRIVER=database
SESSION_LIFETIME=120          # timeout sesi dalam menit
```

---

## 5. Perintah Terminal Lengkap

### Setup & Migrasi

```bash
# Install semua dependency
composer install --no-dev --optimize-autoloader
npm install

# Generate application key
php artisan key:generate

# Jalankan semua migrasi
php artisan migrate

# Rollback satu batch terakhir (hati-hati di production)
php artisan migrate:rollback

# Lihat status migrasi
php artisan migrate:status

# Jalankan seeder admin
php artisan db:seed --class=AdminSeeder

# Jalankan semua seeder
php artisan db:seed
```

### Build & Asset

```bash
# Build untuk production (minified, hashed filename)
npm run build

# Mode development dengan hot reload
npm run dev
```

### Cache & Optimasi

```bash
# Cache semua (config + route + view) — wajib di production
php artisan optimize

# Cache terpisah
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Bersihkan cache (gunakan saat update config/route)
php artisan optimize:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

### Diagnosa & Debug

```bash
# Lihat semua route terdaftar
php artisan route:list

# Lihat semua command artisan
php artisan list

# Jalankan server development (bukan untuk production)
php artisan serve

# Cek koneksi database
php artisan tinker
# > DB::connection()->getPdo(); // ketik ini di prompt tinker
```

### Voucher Sync (Manual)

```bash
# Sinkronisasi status voucher dari radpostauth
php artisan vouchers:sync
```

---

## 6. Deploy ke Production

### Urutan Deploy Lengkap

```bash
cd /var/www/html/radius-admin

# 1. Install dependency PHP (tanpa dev packages)
composer install --no-dev --optimize-autoloader

# 2. Install & build frontend
npm install
npm run build

# 3. Generate key jika fresh install
php artisan key:generate

# 4. Jalankan migrasi
php artisan migrate --force

# 5. Set permission storage & cache
sudo chown -R www-data:www-data storage bootstrap/cache
sudo chmod -R 775 storage bootstrap/cache

# 6. Cache semua untuk performa optimal
php artisan optimize

# 7. Restart queue worker jika digunakan
# php artisan queue:restart
```

### Checklist Production

- [ ] `APP_ENV=production` di `.env`
- [ ] `APP_DEBUG=false` di `.env`
- [ ] `APP_URL` sudah benar (dengan https jika pakai SSL)
- [ ] `DB_PASSWORD` sudah diganti dari default
- [ ] `php artisan optimize` sudah dijalankan
- [ ] Permission `storage/` dan `bootstrap/cache/` sudah `www-data:www-data`
- [ ] `npm run build` sudah dijalankan (bukan dev)

---

## 7. Konfigurasi Nginx / Apache

### Nginx (Rekomendasi)

Buat file `/etc/nginx/sites-available/zeronet`:

```nginx
server {
    listen 80;
    server_name domain-anda.com;          # ganti dengan domain/IP server
    root /var/www/html/radius-admin/public;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    index index.php;
    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    error_page 404 /index.php;

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

```bash
# Aktifkan site dan reload Nginx
sudo ln -s /etc/nginx/sites-available/zeronet /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

### Apache

Buat file `/etc/apache2/sites-available/zeronet.conf`:

```apache
<VirtualHost *:80>
    ServerName domain-anda.com
    DocumentRoot /var/www/html/radius-admin/public

    <Directory /var/www/html/radius-admin/public>
        AllowOverride All
        Require all granted
    </Directory>

    ErrorLog ${APACHE_LOG_DIR}/zeronet_error.log
    CustomLog ${APACHE_LOG_DIR}/zeronet_access.log combined
</VirtualHost>
```

```bash
# Aktifkan mod_rewrite dan site
sudo a2enmod rewrite
sudo a2ensite zeronet.conf
sudo systemctl reload apache2
```

---

## 8. Integrasi FreeRADIUS

Panel ini berbagi database MySQL dengan FreeRADIUS. Tabel yang dipakai bersama:

| Tabel | Dikelola oleh | Keterangan |
|-------|--------------|-----------|
| `radcheck` | Panel (per user) | Atribut autentikasi per user (password, expiry) |
| `radreply` | Panel (per user) | Atribut reply per user |
| `radusergroup` | Panel | Mapping user ke grup/paket |
| `radgroupcheck` | Panel (paket) | Atribut autentikasi per grup |
| `radgroupreply` | Panel (paket) | Atribut reply per grup (kecepatan, dll) |
| `radpostauth` | FreeRADIUS (tulis) | Log setiap percobaan autentikasi |
| `radacct` | FreeRADIUS (tulis) | Log sesi aktif/selesai |

### Patch Query Postauth FreeRADIUS

Agar kolom NAS/IP tercatat di log, query postauth FreeRADIUS perlu dimodifikasi.

Edit file `/etc/freeradius/3.0/mods-config/sql/main/mysql/queries.conf`, cari bagian `postauth_query`, ubah menjadi:

```
postauth_query = "\
    INSERT INTO ${..postauth_table} \
        (username, pass, reply, authdate, nasipaddress ${..class.column_name}) \
    VALUES ( \
        '%{SQL-User-Name}', \
        '%{%{User-Password}:-%{Chap-Password}}', \
        '%{reply:Packet-Type}', \
        '%S.%M', \
        '%{%{NAS-IP-Address}:-127.0.0.1}' \
        ${..class.reply_xlat})"
```

Kemudian restart FreeRADIUS:

```bash
sudo systemctl restart freeradius
```

---

## 9. Cronjob

### Voucher Sync (Wajib)

Command `vouchers:sync` mengaktifkan voucher yang baru pertama kali digunakan dan menandai voucher yang sudah expired. Harus berjalan setiap menit.

```bash
# Buka crontab untuk user www-data
sudo crontab -u www-data -e
```

Tambahkan baris berikut:

```cron
* * * * * php /var/www/html/radius-admin/artisan vouchers:sync >> /dev/null 2>&1
```

### Laravel Scheduler (Opsional)

Jika ada job terjadwal via `app/Console/Kernel.php`:

```cron
* * * * * php /var/www/html/radius-admin/artisan schedule:run >> /dev/null 2>&1
```

### Cek Crontab Aktif

```bash
sudo crontab -u www-data -l
```

---

## 10. Akun Login Default

Akun dibuat oleh `AdminSeeder`. Segera ganti password setelah install pertama.

| Role | Username | Password |
|------|----------|----------|
| Admin | `nazrin` | `1100` |
| Operator | `sukmo` | `9090` |

### Cara Ganti Password

**Via Panel:**
Login → klik nama akun di pojok kanan atas → **Profil Saya** → tab **Ganti Password**.

**Via Artisan Tinker:**

```bash
php artisan tinker
```

```php
use App\Models\User;
use Illuminate\Support\Facades\Hash;

$user = User::where('username', 'nazrin')->first();
$user->password = Hash::make('password_baru_anda');
$user->save();
```

**Via Seeder (ubah dulu kodenya, lalu jalankan):**

Edit `database/seeders/AdminSeeder.php`, ubah nilai password, lalu:

```bash
php artisan db:seed --class=AdminSeeder
```

---

## 11. Backup & Restore

### Backup Database

```bash
# Backup database radius ke file SQL
mysqldump -u radius_user -p radius > /backup/radius_$(date +%Y%m%d_%H%M%S).sql

# Backup dengan kompresi
mysqldump -u radius_user -p radius | gzip > /backup/radius_$(date +%Y%m%d_%H%M%S).sql.gz
```

### Restore Database

```bash
# Restore dari file SQL
mysql -u radius_user -p radius < /backup/radius_20260101_120000.sql

# Restore dari file terkompresi
gunzip < /backup/radius_20260101_120000.sql.gz | mysql -u radius_user -p radius
```

### Backup File Project

```bash
# Backup folder project (tanpa node_modules dan vendor)
tar --exclude='./node_modules' --exclude='./vendor' \
    -czvf /backup/zeronet_files_$(date +%Y%m%d).tar.gz \
    /var/www/html/radius-admin
```

### Backup Otomatis (Crontab)

```bash
sudo crontab -e
```

```cron
# Backup database setiap hari jam 02:00
0 2 * * * mysqldump -u radius_user -pPASSWORD_ANDA radius | gzip > /backup/radius_$(date +\%Y\%m\%d).sql.gz
```

---

## 12. Update Project

### Urutan Update Standar

```bash
cd /var/www/html/radius-admin

# 1. Ambil update (jika pakai git)
git pull origin main

# 2. Install dependency baru (jika ada perubahan composer.json)
composer install --no-dev --optimize-autoloader

# 3. Install & build ulang frontend (jika ada perubahan package.json / JS/CSS)
npm install
npm run build

# 4. Jalankan migrasi baru (jika ada)
php artisan migrate --force

# 5. Bersihkan dan rebuild cache
php artisan optimize:clear
php artisan optimize

# 6. Fix permission (jika diperlukan)
sudo chown -R www-data:www-data storage bootstrap/cache
```

### Cek Apakah Ada Migrasi Baru

```bash
php artisan migrate:status
```

---

## 13. Struktur Folder Penting

```
/var/www/html/radius-admin/
│
├── app/
│   ├── Http/
│   │   ├── Controllers/Admin/     # Controller untuk setiap modul admin
│   │   ├── Middleware/            # EnsureAdmin, EnsureRole, SessionTimeout
│   │   └── Requests/Admin/        # Form Request validation
│   ├── Models/                    # Eloquent models (RadCheck, RadReply, Voucher, dll)
│   └── Services/                  # Business logic (RadiusUserService, VoucherService, dll)
│
├── database/
│   ├── migrations/                # Semua migrasi database
│   └── seeders/                   # AdminSeeder, DatabaseSeeder
│
├── resources/
│   ├── css/app.css                # Entry point Tailwind CSS
│   ├── js/app.js                  # Entry point Alpine.js + Flowbite
│   └── views/
│       ├── admin/                 # Semua halaman admin
│       │   ├── layouts/app.blade.php     # Layout utama (sidebar + topbar)
│       │   ├── components/              # Komponen reusable (alert, nav-item, dll)
│       │   ├── dashboard.blade.php
│       │   ├── radius-users/
│       │   ├── packages/
│       │   ├── vouchers/
│       │   ├── hotspot-logs/
│       │   ├── activity-logs/
│       │   └── profile/
│       └── auth/
│           └── login.blade.php    # Halaman login
│
├── routes/
│   ├── web.php                    # Semua route admin
│   └── auth.php                   # Route login & logout
│
├── public/
│   ├── build/                     # Asset hasil npm run build (jangan edit manual)
│   ├── favicon.svg                # Favicon WiFi icon
│   └── index.php                  # Entry point web (root document)
│
├── storage/
│   └── logs/laravel.log           # Log error aplikasi
│
├── .env                           # Konfigurasi environment (JANGAN commit ke git)
├── composer.json
├── package.json
├── tailwind.config.js
└── vite.config.js
```

---

## 14. Troubleshooting

### Error 500 / Blank Page

```bash
# Cek log error Laravel
tail -n 50 /var/www/html/radius-admin/storage/logs/laravel.log

# Cek log Nginx/Apache
tail -n 50 /var/log/nginx/error.log
# atau
tail -n 50 /var/log/apache2/error.log

# Pastikan APP_DEBUG=true sementara untuk lihat pesan error di browser
# (kembalikan ke false setelah selesai debug)
```

### CSS / Tampilan Tidak Muncul

```bash
# Pastikan asset sudah di-build
npm run build

# Cek apakah folder public/build/ ada dan terisi
ls /var/www/html/radius-admin/public/build/

# Bersihkan cache view
php artisan view:clear
```

### Login Gagal / Redirect Loop

```bash
# Cek koneksi database
php artisan tinker
# > DB::connection()->getPdo();

# Pastikan tabel sessions ada
php artisan migrate:status

# Bersihkan cache config
php artisan config:clear
php artisan cache:clear

# Pastikan APP_KEY terisi di .env
grep APP_KEY /var/www/html/radius-admin/.env
```

### Koneksi Database Gagal

```bash
# Test koneksi MySQL langsung
mysql -u radius_user -p -h 127.0.0.1 radius

# Cek status MySQL
sudo systemctl status mysql

# Cek kredensial di .env
grep DB_ /var/www/html/radius-admin/.env
```

### FreeRADIUS Tidak Sinkron / Atribut Tidak Diterapkan

```bash
# Cek status FreeRADIUS
sudo systemctl status freeradius

# Test autentikasi via radtest
radtest username password 127.0.0.1 0 testing123

# Debug FreeRADIUS (jalankan di foreground)
sudo freeradius -X

# Cek data user di database radius
mysql -u radius_user -p radius -e "
    SELECT * FROM radcheck WHERE username='namauser';
    SELECT * FROM radusergroup WHERE username='namauser';
    SELECT g.* FROM radgroupreply g
    JOIN radusergroup u ON g.groupname = u.groupname
    WHERE u.username='namauser';
"

# Sync ulang voucher secara manual
php artisan vouchers:sync
```

### Permission Denied di Storage

```bash
sudo chown -R www-data:www-data /var/www/html/radius-admin/storage
sudo chown -R www-data:www-data /var/www/html/radius-admin/bootstrap/cache
sudo chmod -R 775 /var/www/html/radius-admin/storage
sudo chmod -R 775 /var/www/html/radius-admin/bootstrap/cache
```

### Cache Lama Setelah Update

```bash
php artisan optimize:clear
php artisan optimize
```

---

*ZeroNet &copy; 2026 — Panel Manajemen Hotspot FreeRADIUS*
