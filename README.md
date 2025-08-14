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
## 3. Konfigurasi File .env
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
## 4. Generate Application Key

```bash
php artisan key:generate

```
## 5. Migrasi Database
Jalankan migrasi tabel ke database:

```bash
php artisan migrate

```
## 6. Jalankan server Laravel:
```bash
php artisan serve
