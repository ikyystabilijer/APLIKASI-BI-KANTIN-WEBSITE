# 🍽️ E-Kantin Sekolah (`bi-kantin-web`)

Sistem informasi dan pemesanan kantin sekolah berbasis web yang dibangun menggunakan **Laravel 11** dan **Bootstrap 5**. Aplikasi ini mempermudah siswa dalam memesan makanan/minuman secara efisien serta membantu pengelola kantin dalam memantau dan memperbarui status pesanan secara *real-time*.

---

## ✨ Fitur Utama

- 🔐 **Autentikasi & Multi-Role**: Sistem login role-based untuk **Admin** dan **Siswa**.
- 👨‍🍳 **Panel Admin**:
  - Ringkasan statistik (Total Menu, Pesanan Menunggu Konfirmasi, Pesanan Masuk Hari Ini, Total Pesanan).
  - Manajemen Menu Makanan & Minuman (Tambah, Edit, Hapus / CRUD) dilengkapi validasi **Upload Gambar Menu**.
  - Monitoring pesanan masuk dengan pencatatan waktu otomatis dan pengubahan status secara instan (*Menunggu Konfirmasi*, *Sedang Diproses*, *Siap Diambil*, *Selesai*, *Dibatalkan*).
- 🧑‍🎓 **Panel Siswa**:
  - Katalog makanan & minuman dengan gambar visual menarik.
  - **Keranjang Belanja (Session Storage)**: Siswa dapat menambahkan beberapa menu sekaligus ke keranjang belanja.
  - **Checkout Aman (DB Transaction)**: Proses checkout multi-item terproteksi transaksi database untuk menjamin integritas data.
  - Monitoring status pesanan secara **real-time** (auto-refresh tanpa perlu reload halaman penuh).

---

## 🛠️ Prasyarat Sistem

Pastikan perangkat Anda telah terinstall:
- **PHP** >= 8.2
- **Composer** (Package Manager PHP)
- **MySQL / MariaDB** (bisa menggunakan XAMPP, Laragon, atau MySQL service native)
- Git (opsional)

---

## 🚀 Panduan Instalasi Langkah-demi-Langkah

Ikuti langkah-langkah berikut untuk menjalankan project ini di komputer lokal:

### 1. Clone Repository & Masuk ke Folder Project
Jika mendownload via Git:
```bash
git clone https://github.com/username-anda/bi-kantin-web.git
cd bi-kantin-web
```
*(Atau ekstrak file zip project dan buka terminal di dalam direktori `bi-kantin-web`)*.

---

### 2. Install Dependensi Composer
Jalankan perintah berikut untuk mengunduh semua library Laravel:
```bash
composer install
```

---

### 3. Buat Database MySQL
Nyalakan MySQL (misalnya melalui panel **XAMPP** atau **Laragon**), lalu buat database baru bernama `db_bikantin`:
- Melalui **phpMyAdmin**: Buat database baru bernama `db_bikantin`
- Atau melalui **MySQL CLI**:
  ```sql
  CREATE DATABASE db_bikantin;
  ```

---

### 4. Salin & Sesuaikan File Konfigurasi `.env`
Salin file `.env.example` menjadi `.env`:

**Linux / macOS:**
```bash
cp .env.example .env
```

**Windows (Command Prompt / PowerShell):**
```cmd
copy .env.example .env
```

Buka file `.env` dan pastikan konfigurasi database sudah sesuai dengan MySQL lokal Anda:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_bikantin
DB_USERNAME=root
DB_PASSWORD=
```
> *Catatan: Jika MySQL Anda menggunakan password, isi bagian `DB_PASSWORD`.*

---

### 5. Generate Application Key
Buat kunci enkripsi aplikasi Laravel:
```bash
php artisan key:generate
```

---

### 6. Jalankan Migrasi Database & Seeder
Buat seluruh tabel dan isi data awal (akun login & contoh menu) secara otomatis:
```bash
php artisan migrate --seed
```
> **Tips:** Jika ingin mereset ulang seluruh isi database dari awal, gunakan:
> ```bash
> php artisan migrate:fresh --seed
> ```

---

### 7. Buat Symlink Storage (Untuk Gambar Menu)
Hubungkan folder penyimpanan file gambar agar bisa diakses di web browser:
```bash
php artisan storage:link
```

---

### 8. Jalankan Server Development
Jalankan web server lokal Laravel:
```bash
php artisan serve
```

Aplikasi siap diakses melalui browser di:
👉 **`http://127.0.0.1:8000`** atau **`http://localhost:8000`**

---

## 🔑 Akun Uji Coba Default

Setelah menjalankan migrasi dan seeder, Anda dapat langsung login menggunakan akun berikut:

| Role | Username | Password | Deskripsi Halaman |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin_kantin` | `admin123` | Dashboard pengelolaan menu (CRUD) & pemantauan status pesanan |
| **Siswa** | `budi_siswa` | `123` | Katalog menu, pemesanan, dan monitoring status live |

---

## 🗄️ Struktur Database

Database `db_bikantin` terdiri dari 3 tabel utama:

1. **`users`**
   - `id` (Primary Key, Auto Increment)
   - `username` (VARCHAR 50)
   - `password` (VARCHAR 50)
   - `role` (ENUM: `'admin'`, `'siswa'`)
2. **`menu`**
   - `id` (Primary Key, Auto Increment)
   - `nama_menu` (VARCHAR 100)
   - `harga` (INT)
   - `kategori` (VARCHAR 50: Makanan, Minuman, Camilan, dll.)
   - `gambar` (VARCHAR 255, Path foto file - Nullable)
   - `created_at` & `updated_at` (Timestamps)
3. **`pesanan`**
   - `id` (Primary Key, Auto Increment)
   - `user_id` (Foreign Key -> `users.id`)
   - `menu_id` (Foreign Key -> `menu.id`)
   - `jumlah` (INT)
   - `total_harga` (INT)
   - `status` (VARCHAR 100, default `'Menunggu Konfirmasi'`)
   - `created_at` & `updated_at` (Timestamps)

---

## 🧪 Menjalankan Pengujian (Automated Tests)

Project ini telah dilengkapi dengan unit/feature test otomatis untuk memvalidasi alur autentikasi, CRUD menu, proteksi hak akses, dan proses pemesanan:
```bash
php artisan test
```

---

## 💡 Troubleshooting

- **Error: `Access denied for user 'root'@'localhost'`**
  - Pastikan `DB_USERNAME` dan `DB_PASSWORD` di file `.env` sudah sesuai dengan pengaturan MySQL Anda.
- **Error: `Unknown database 'db_bikantin'`**
  - Buat terlebih dahulu database `db_bikantin` di phpMyAdmin sebelum menjalankan `php artisan migrate`.
- **Gambar menu tidak muncul:**
  - Pastikan telah menjalankan `php artisan storage:link` agar folder upload dapat diakses secara publik.
- **Tampilan CSS/Icons tidak muncul:**
  - Aplikasi menggunakan CDN Bootstrap 5 dan Bootstrap Icons. Pastikan komputer Anda terhubung ke internet saat pertama kali mengakses aplikasi.
