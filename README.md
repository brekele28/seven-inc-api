# Project Backend Seven INC.

Panduan untuk instalasi dan menjalankan project Laravel 12.

---

## 1. Clone Repository
Buka **CMD/Terminal/GitBash**, arahkan ke folder yang diinginkan, lalu jalankan perintah:

```bash
https://github.com/brekele28/seven-inc-api.git
cd seven-inc-api

```
## 2. Install Composser
Jalankan perintah berikut untuk menginstall dependency manager untuk PHP yang dibutuhkan Laravel:
```bash
composer install

```
## 3. Project ini menggunakan Laravel Sanctum untuk autentikasi API.
Setelah menjalankan composer install, jalankan:

```bash
php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"

```
## 4. Konfigurasi File .env
Laravel menggunakan file .env untuk konfigurasi environment.
• ubah konfigurasi database menjadi:
```bash
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=seven-inc-db
DB_USERNAME=root
DB_PASSWORD=

```
## 5. Generate Application Key

```bash
php artisan key:generate

```
## 6. Migrasi Database
Jalankan migrasi tabel ke database:

```bash
php artisan migrate
```

## 7. Wajib untuk menampilkan Gambar
```bash
php artisan storage:link
```

## 8. Jalankan server Laravel:
```bash
php artisan serve
