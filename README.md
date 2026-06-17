# Lostlink
Project Website untuk Memenuhi Tugas Akhir PBD dan PPW

# 🔗 LostLink — Platform Laporan Kehilangan & Temuan Barang Kampus

> **LostLink** adalah aplikasi web untuk melaporkan dan menemukan kembali barang hilang di lingkungan kampus. Tersedia untuk mahasiswa, staf, dan masyarakat umum.

---

## Fitur

### Pengguna Umum
-  **Laporan Kehilangan** — Buat laporan barang hilang dengan foto, kategori, lokasi & deskripsi
-  **Laporan Temuan** — Laporkan barang temuan agar mudah diklaim pemiliknya
-  **Klaim Barang** — Ajukan klaim kepemilikan barang temuan dengan bukti pendukung
-  **Dashboard Pribadi** — Pantau semua laporan dan klaim milik sendiri
-  **Autentikasi Aman** — Registrasi & login dengan verifikasi identitas (NIM/NIK)

### 🛡️ Admin
-  **Dashboard Ringkasan** — Statistik total laporan, klaim, dan pengguna secara real-time
-  **Verifikasi Laporan** — Setujui atau tolak laporan yang masuk
-  **Manajemen Pengguna** — Aktifkan, nonaktifkan, atau hapus akun pengguna
-  **Manajemen Kategori** — Tambah, ubah, dan hapus kategori barang
-  **Log Aktivitas** — Pantau seluruh aktivitas dalam sistem

---

##  Teknologi

| Komponen | Teknologi |
|---|---|
| Backend | PHP 8.x (Native) |
| Database | MySQL / MariaDB |
| Frontend | HTML5, Tailwind CSS |
| Ikon | Font Awesome 6 |
| Database Access | PDO (PHP Data Objects) |
| Session | PHP Native Session |

---

## 📁 Struktur Proyek

```
lostlink/
├── index.php                  # Halaman utama / feed laporan
├── includes/
│   ├── config.php             # Konfigurasi database & helper functions
│   ├── functions.php          # Seluruh fungsi bisnis aplikasi
│   ├── header.php             # Template header & navigasi
│   └── footer.php             # Template footer
├── pages/
│   ├── dashboard.php          # Dashboard pengguna
│   ├── admin.php              # Panel admin
│   ├── detail.php             # Detail laporan
│   ├── form-laporan.php       # Form buat laporan (hilang/temuan)
│   └── form-klaim.php         # Form pengajuan klaim
├── api/
│   ├── login.php              # Endpoint login
│   ├── register.php           # Endpoint registrasi
│   ├── logout.php             # Endpoint logout
│   ├── admin-action.php       # Endpoint aksi admin
│   ├── delete-laporan.php     # Endpoint hapus laporan
│   └── cancel-klaim.php       # Endpoint batalkan klaim
└── assets/
    └── uploads/               # Direktori unggahan foto
        └── bukti/             # Direktori unggahan foto bukti klaim
```

##  Instalasi & Konfigurasi

### 1. Clone Repositori

```bash
git clone https://github.com/username/lostlink.git
cd lostlink
```

### 2. Pindahkan ke Root Web Server

```bash
# Untuk XAMPP (Windows)
xcopy lostlink C:\xampp\htdocs\lostlink /E /I

# Untuk XAMPP (Linux/macOS)
cp -r lostlink /opt/lampp/htdocs/lostlink
```

### 3. Import Database

Buat database baru di phpMyAdmin atau via terminal:

```sql
CREATE DATABASE lostlink_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Kemudian import file SQL:

```bash
mysql -u root -p lostlink_db < database/lostlink_db.sql
```

### 4. Konfigurasi Database

Edit file `includes/config.php` sesuai environment kamu:

```php
define('BASE_URL', 'http://localhost/lostlink/');

define('DB_HOST', 'localhost');
define('DB_USER', 'root');        // Sesuaikan username DB
define('DB_PASS', '');            // Sesuaikan password DB
define('DB_NAME', 'lostlink_db');
```

### 5. Beri Izin Folder Upload

```bash
chmod -R 755 assets/uploads/
```

### 6. Akses Aplikasi

Buka browser dan akses:

```
http://localhost/lostlink/
```

---

## Penggunaan

### Mendaftar Akun
1. Klik tombol **Daftar** di halaman utama
2. Pilih tipe akun: **Mahasiswa** (gunakan NIM) atau **Umum** (gunakan NIK)
3. Isi formulir pendaftaran dan submit

### Membuat Laporan
1. Login ke akun kamu
2. Klik **Laporkan Kehilangan** atau **Laporkan Temuan**
3. Isi detail barang: nama, kategori, lokasi, tanggal, deskripsi & foto
4. Submit laporan

### Mengklaim Barang Temuan
1. Temukan laporan barang di feed utama
2. Buka halaman detail laporan
3. Klik **Ajukan Klaim** dan unggah foto bukti kepemilikan
4. Tunggu verifikasi dari admin

---

##  Peran Pengguna

| Peran | Deskripsi | Akses |
|---|---|---|
| **Guest** | Pengunjung tanpa akun | Hanya bisa melihat feed laporan |
| **User (Mahasiswa/Umum)** | Pengguna terdaftar | Buat laporan, klaim barang, kelola akun |
| **Admin** | Administrator sistem | Kelola semua data, verifikasi, manajemen pengguna |

> Akun admin dapat dibuat langsung melalui database dengan mengatur kolom `role = 'admin'` pada tabel `users`.
