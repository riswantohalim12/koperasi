# Sistem Informasi Koperasi Simpan Pinjam (SI-KSP)

Aplikasi berbasis web sederhana untuk manajemen Koperasi Simpan Pinjam. Aplikasi ini dibangun menggunakan PHP Native (tanpa framework), database MySQL, dan Bootstrap 5 untuk antarmuka pengguna.

## 📋 Fitur Utama

Aplikasi ini memiliki 3 level akses (Role): **Admin**, **Petugas**, dan **Anggota**.

### Fitur Umum
- **Login Multi-User:** Sistem autentikasi aman dengan *password hashing*.
- **Dashboard:** Statistik ringkas untuk Admin/Petugas dan info saldo untuk Anggota.
- **Cetak Struk:** Fitur cetak bukti transaksi simpanan, pencairan pinjaman, dan angsuran.

### 👮 Administrator & Petugas
- **Manajemen User:** Mengelola akun Admin dan Petugas (Khusus Admin).
- **Manajemen Anggota:** Tambah, Edit, Hapus data anggota (Otomatis membuat akun login untuk anggota).
- **Transaksi Simpanan:** Input Setoran dan Penarikan simpanan anggota.
- **Pengajuan Pinjaman:** Input pengajuan pinjaman baru.
- **Persetujuan Pinjaman:** Validasi (Setuju/Tolak) pengajuan pinjaman (Khusus Admin).
- **Pembayaran Angsuran:** Input pembayaran cicilan pinjaman.
- **Laporan/Riwayat:** Melihat riwayat transaksi simpanan dan pinjaman.

### 👤 Anggota
- **Profil:** Melihat data diri dan statistik keuangan pribadi.
- **Info Saldo:** Melihat total saldo simpanan saat ini.
- **Riwayat Transaksi:** Memantau riwayat setoran, penarikan, dan pembayaran angsuran pinjaman.

---

## 🛠️ Teknologi yang Digunakan

- **Bahasa Pemrograman:** PHP (Native / Vanilla)
- **Database:** MySQL / MariaDB
- **Frontend Framework:** Bootstrap 5
- **Icons:** Bootstrap Icons
- **Plugins Tambahan:**
  - DataTables (Tabel interaktif dengan pencarian & pagination)
  - Chart.js (Grafik statistik)
  - jQuery

---

## ⚙️ Persyaratan Sistem

- Web Server (Apache/Nginx)
- PHP versi 7.4 atau lebih baru (Disarankan PHP 8.x)
- MySQL Database

---

## 🚀 Cara Instalasi

Ikuti langkah-langkah berikut untuk menjalankan aplikasi di komputer lokal (Localhost):

1. **Download/Clone Project**
   Simpan folder project ini ke dalam direktori web server Anda (misalnya: `htdocs` untuk XAMPP atau `www` untuk Laragon). Beri nama folder, misalnya: `koperasi`.

2. **Buat Database**
   - Buka phpMyAdmin (http://localhost/phpmyadmin).
   - Buat database baru dengan nama: `koperasi_simpan_pinjam`.

3. **Import Database**
   - Pilih database yang baru dibuat.
   - Klik menu **Import**.
   - Pilih file `database.sql` yang ada di dalam folder project (`koprasi/database.sql`).
   - Klik **Go/Kirim**.

4. **Konfigurasi Koneksi**
   - Buka file `config/database.php` dengan text editor (VS Code, Notepad++, dll).
   - Sesuaikan konfigurasi berikut dengan settingan database lokal Anda:
     ```php
     $host = 'localhost';
     $dbname = 'koperasi_simpan_pinjam';
     $username = 'root'; 
     $password = ''; // Kosongkan jika menggunakan XAMPP default, atau sesuaikan
     ```
   *(Catatan: Di file asli tertulis password '12345678', harap diganti sesuai password database lokal Anda)*.

5. **Jalankan Aplikasi**
   - Buka browser dan akses: `http://localhost/koperasi` (sesuaikan dengan nama folder Anda).

---

## 🔑 Akun Login Default

Berikut adalah akun dummy yang sudah tersedia di database untuk pengujian:

| Role | Username | Password | Keterangan |
| :--- | :--- | :--- | :--- |
| **Admin** | `admin` | `12345678` | Akses Penuh |
| **Petugas** | `petugas` | `12345678` | Admin operasional (tanpa hapus user/persetujuan) |
| **Anggota** | `anggota1` | `12345678` | Hanya bisa lihat data sendiri |

> **Catatan:** Password untuk anggota baru yang dibuat melalui menu "Tambah Anggota" secara default adalah **NIK** anggota tersebut.

---

## 📂 Struktur Folder
