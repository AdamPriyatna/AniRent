# 🎌 AniRent — Sistem Penyewaan Peralatan Anime & Kreator

<div align="center">

![AniRent Banner](docs/banner.png)

**Aplikasi web berbasis Laravel + Vue 3 + Inertia.js untuk menyewa peralatan bertema anime**

[![Laravel](https://img.shields.io/badge/Laravel-12.x-FF2D20?style=flat&logo=laravel)](https://laravel.com)
[![Vue](https://img.shields.io/badge/Vue-3.x-4FC08D?style=flat&logo=vue.js)](https://vuejs.org)
[![Inertia](https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=flat)](https://inertiajs.com)
[![Tailwind](https://img.shields.io/badge/Tailwind-3.x-38BDF8?style=flat&logo=tailwindcss)](https://tailwindcss.com)

</div>

---

## 👥 Nama Kelompok

**Kelompok [Nomor Kelompok]**

---

## 👨‍💻 Nama Team

| No | Nama | NIM/NIP | Role |
|----|------|---------|------|
| 1  | [Adam Priyatna] | [102042300188] | Project Manager / Full Stack |
| 2  | [Adam Priyatna] | [102042300188] | Backend Developer |
| 3  | [Adam Priyatna] | [102042300188] | Frontend Developer |
| 4  | [Adam Priyatna] | [102042300188] | Database Designer |

---

## 📋 Nama Project

### **AniRent** — *Sistem Penyewaan Peralatan Anime & Kreator Berbasis Web*

AniRent adalah aplikasi web berbasis Laravel yang digunakan untuk menyewa berbagai jenis peralatan bertema anime, seperti cosplay, koleksi manga, game, dan alat kreator. Sistem ini dilengkapi dengan fitur booking dan bundle (paket), serta aturan bisnis seperti batas peminjaman dan denda keterlambatan.

---

## ✨ List Fitur

### 🔐 Autentikasi & Keamanan
- Login wajib untuk mengakses seluruh halaman web
- Registrasi akun anggota baru
- Role-based access control: **Admin** dan **Anggota**
- Middleware proteksi halaman admin
- Logout dan manajemen sesi

### 👤 Manajemen Profil
- Satu user hanya memiliki **satu profil** (relasi 1-to-1)
- Setiap user dapat melihat dan mengubah profilnya sendiri
- Data profil: nama, foto, alamat, nomor telepon, NIM/NIP

### 🗂️ Manajemen Kategori *(Admin Only)*
- Tambah, edit, hapus kategori
- Validasi: kategori yang masih dipakai unit tidak bisa dihapus
- Tampilan grid dengan jumlah unit per kategori

### 📦 Manajemen Unit *(Admin Only)*
- Tambah unit dengan **kode unit unik** (tidak boleh duplikat)
- Satu nama unit bisa lebih dari 1 item (dibedakan via kode unit)
- Setiap unit dapat memiliki **multiple kategori**
- Edit data unit termasuk foto dan kategori
- Hapus unit (validasi: tidak bisa hapus jika sedang dipinjam)
- Status unit: *Tersedia*, *Dipinjam*, *Rusak*
- **Pencarian unit** berdasarkan nama

### 🎭 Manajemen Bundle *(Admin Only)*
- Tambah paket sewa berisi beberapa unit sekaligus
- Bundle dihitung sebagai **1 item pinjaman**
- Edit dan hapus bundle
- Status bundle: *Tersedia*, *Disewa*

### 👥 Manajemen User/Anggota *(Admin Only)*
- Tambah, edit, hapus akun anggota
- Lihat daftar semua anggota terdaftar

### 📅 Booking / Peminjaman
- Anggota dapat meminjam unit atau bundle yang berstatus *Tersedia*
- **Maksimal 2 item aktif** per anggota
- Durasi pinjam **maksimal 5 hari**
- Status booking: `booked` → `active` → `returned` / `late`
- Anggota hanya dapat melihat pinjaman **miliknya sendiri**
- Admin dapat melihat **seluruh data booking**

### 🔄 Pengembalian Unit *(Admin Only)*
- Hanya Admin yang dapat memproses pengembalian
- Anggota wajib menghubungi admin untuk pengembalian
- Sistem otomatis mengubah status unit menjadi *Tersedia* setelah dikembalikan

### 💰 Denda Keterlambatan
- Denda otomatis dihitung jika pengembalian melewati **5 hari**
- Denda dihitung per hari keterlambatan
- Admin dapat mengelola status denda: *Belum Bayar* / *Lunas*

### 📊 Riwayat & Laporan *(Admin Only)*
- Admin dapat melihat riwayat peminjaman seluruh anggota
- Admin dapat **mencetak** riwayat peminjaman dalam format PDF
- Filter riwayat berdasarkan anggota, tanggal, dan status

### ✅ Validasi
- Semua field wajib memiliki validasi `required`
- Kode unit unik (tidak boleh duplikat)
- Format email valid
- Ukuran foto maksimal 2MB
- Batas maksimal pinjaman per anggota
- Batas durasi pinjaman

---

## 🗄️ Skema Database (ERD)

> Screenshot ERD disisipkan di sini setelah project selesai

![ERD AniRent](docs/erd.png)

### Deskripsi Tabel

| Tabel | Deskripsi |
|-------|-----------|
| `users` | Akun login dengan role admin/anggota |
| `profiles` | Profil detail user, relasi 1-to-1 dengan users |
| `categories` | Kategori unit (Cosplay, Koleksi, Game, Kreator) |
| `units` | Data unit dengan kode unik per item |
| `unit_categories` | Pivot many-to-many unit ↔ kategori |
| `bundles` | Paket sewa berisi beberapa unit |
| `bundle_units` | Pivot many-to-many bundle ↔ unit |
| `bookings` | Transaksi peminjaman unit atau bundle |
| `fines` | Denda keterlambatan pengembalian |

---

## 🎬 Demo Website

> Video demo akan disisipkan di sini setelah project selesai

[![Demo Video](docs/thumbnail.png)](docs/demo.mp4)

**Alur demo mencakup:**
1. Login sebagai Admin dan Anggota
2. Registrasi akun anggota baru
3. Admin menambah kategori, unit, dan bundle
4. Anggota mencari dan meminjam unit
5. Admin memproses pengembalian unit
6. Sistem menghitung denda otomatis
7. Admin mencetak riwayat peminjaman (PDF)
8. Edit profil user

---

## 🛠️ Tech Stack

| Layer | Teknologi |
|-------|-----------|
| Backend | Laravel 12 (PHP 8.2) |
| Frontend | Vue 3 + Inertia.js |
| Styling | Tailwind CSS |
| Database | MySQL |
| Authentication | Laravel Breeze |
| PDF | barryvdh/laravel-dompdf |
| Build Tool | Vite |

---

## 🗂️ Kategori Unit

| Kode Prefix | Kategori | Contoh Unit |
|-------------|----------|-------------|
| `COS-` | Cosplay | Kostum Naruto, Katana Tanjiro |
| `COL-` | Koleksi (Manga/Novel) | Naruto Vol 1-10 |
| `GAME-` | Game & Console | PS4 Naruto Storm 4 |
| `CRT-` | Peralatan Kreator | Wacom Tablet CTL-472 |

---

## 📁 Struktur Folder

```
anirent/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Admin/
│   │   │   │   ├── AdminUnitController.php
│   │   │   │   ├── AdminCategoryController.php
│   │   │   │   ├── AdminUserController.php
│   │   │   │   ├── AdminBundleController.php
│   │   │   │   └── AdminBookingController.php
│   │   │   ├── Auth/
│   │   │   ├── BookingController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── ProfileController.php
│   │   │   └── UnitController.php
│   │   └── Middleware/
│   │       └── AdminMiddleware.php
│   └── Models/
│       ├── User.php
│       ├── Profile.php
│       ├── Unit.php
│       ├── Category.php
│       ├── Bundle.php
│       ├── Booking.php
│       └── Fine.php
├── database/
│   ├── migrations/
│   └── seeders/
│       ├── UserSeeder.php
│       ├── CategorySeeder.php
│       ├── UnitSeeder.php
│       └── BundleSeeder.php
├── resources/
│   └── js/
│       ├── Layouts/
│       │   └── AuthenticatedLayout.vue
│       └── Pages/
│           ├── Auth/
│           │   ├── Login.vue
│           │   └── Register.vue
│           ├── Dashboard.vue
│           ├── Units/
│           │   └── Index.vue
│           ├── Bookings/
│           │   └── MyBookings.vue
│           ├── Profile/
│           └── Admin/
│               ├── Units/
│               │   ├── Index.vue
│               │   ├── Create.vue
│               │   └── Edit.vue
│               ├── Categories/
│               │   └── Index.vue
│               ├── Users/
│               │   └── Index.vue
│               ├── Bundles/
│               │   └── Index.vue
│               └── Bookings/
│                   ├── Index.vue
│                   └── History.vue
└── routes/
    └── web.php
```

---

## 🚀 Cara Menjalankan Project

### Prasyarat
- PHP >= 8.2
- Composer
- Node.js >= 18 + NPM
- MySQL
- Git

### Langkah Instalasi

```bash
# 1. Clone repository
git clone https://github.com/username/anirent.git
cd anirent

# 2. Install dependencies PHP
composer install

# 3. Install dependencies Node
npm install

# 4. Copy file environment
cp .env.example .env

# 5. Generate application key
php artisan key:generate

# 6. Konfigurasi database di .env
# DB_DATABASE=anirent_db
# DB_USERNAME=root
# DB_PASSWORD=

# 7. Buat database di MySQL
# CREATE DATABASE anirent_db;

# 8. Jalankan migrasi dan seeder
php artisan migrate --seed

# 9. Buat symlink storage (untuk foto)
php artisan storage:link

# 10. Build assets
npm run build
```

### Menjalankan Development Server

Buka **dua terminal** secara bersamaan:

```bash
# Terminal 1
php artisan serve

# Terminal 2
npm run dev
```

Akses di browser: **http://127.0.0.1:8000**

---

## 🔑 Akun Default (Seeder)

| Role | Email | Password |
|------|-------|----------|
| Admin | admin@anirent.com | password |
| Anggota | budi@anirent.com | password |

---

## ⚖️ Aturan Bisnis

| Aturan | Ketentuan |
|--------|-----------|
| Batas pinjaman | Maksimal **2 item** aktif per anggota |
| Durasi pinjam | Maksimal **5 hari** |
| Denda | Dikenakan jika melewati 5 hari, dihitung per hari |
| Pengembalian | Hanya **Admin** yang bisa memproses |
| Kode unit | Harus **unik**, tidak boleh duplikat |
| Profil | Setiap user hanya punya **1 profil** |
| Bundle | Dihitung sebagai **1 item** pinjaman |

---

## 📌 Perintah Berguna

```bash
# Reset database dan seed ulang
php artisan migrate:fresh --seed

# Buat file baru
php artisan make:model NamaModel
php artisan make:controller NamaController
php artisan make:migration create_nama_table
php artisan make:seeder NamaSeeder

# Cache & optimasi
php artisan optimize:clear

# Lihat semua route
php artisan route:list

# Push ke GitHub
git add .
git commit -m "deskripsi perubahan"
git push
```

---

<div align="center">
  <p>Dibuat dengan ❤️ oleh Kelompok [Nomor] — [Tahun]</p>
  <p><strong>AniRent</strong> — Sewa peralatan anime & kreator favoritmu</p>
</div>