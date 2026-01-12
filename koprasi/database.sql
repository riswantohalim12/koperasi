-- Database: koperasi_simpan_pinjam

-- 1. Tabel Users (Untuk Login 3 Role: Admin, Petugas, Anggota)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    nama_lengkap VARCHAR(100) NOT NULL,
    role ENUM('admin', 'petugas', 'anggota') NOT NULL DEFAULT 'anggota',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabel Anggota (Data Detail Anggota)
CREATE TABLE IF NOT EXISTS anggota (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT, -- Relasi ke tabel users (jika user tersebut adalah anggota)
    no_anggota VARCHAR(20) NOT NULL UNIQUE,
    nik VARCHAR(16) NOT NULL UNIQUE,
    nama VARCHAR(100) NOT NULL,
    alamat TEXT,
    no_hp VARCHAR(15),
    pekerjaan VARCHAR(50),
    tanggal_gabung DATE NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
);

-- 3. Tabel Jenis Simpanan (Master Data)
CREATE TABLE IF NOT EXISTS jenis_simpanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nama_simpanan VARCHAR(50) NOT NULL, -- Pokok, Wajib, Sukarela
    nominal_minimal DECIMAL(15, 2) DEFAULT 0,
    keterangan TEXT
);

-- 4. Tabel Transaksi Simpanan
CREATE TABLE IF NOT EXISTS transaksi_simpanan (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    jenis_simpanan_id INT NOT NULL,
    petugas_id INT NOT NULL, -- User ID petugas yang input
    kode_transaksi VARCHAR(20) NOT NULL UNIQUE,
    jenis_transaksi ENUM('setor', 'tarik') NOT NULL,
    jumlah DECIMAL(15, 2) NOT NULL,
    tanggal_transaksi DATETIME DEFAULT CURRENT_TIMESTAMP,
    keterangan TEXT,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id),
    FOREIGN KEY (jenis_simpanan_id) REFERENCES jenis_simpanan(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- 5. Tabel Pinjaman
CREATE TABLE IF NOT EXISTS pinjaman (
    id INT AUTO_INCREMENT PRIMARY KEY,
    anggota_id INT NOT NULL,
    petugas_id INT NOT NULL,
    kode_pinjaman VARCHAR(20) NOT NULL UNIQUE,
    jumlah_pinjaman DECIMAL(15, 2) NOT NULL,
    bunga_persen DECIMAL(5, 2) NOT NULL, -- Bunga per tahun/bulan
    jumlah_bunga DECIMAL(15, 2) NOT NULL,
    total_angsuran DECIMAL(15, 2) NOT NULL, -- Total yang harus dibayar (Pokok + Bunga)
    lama_angsuran INT NOT NULL, -- Dalam bulan
    tanggal_pengajuan DATE NOT NULL,
    status ENUM('diajukan', 'disetujui', 'lunas', 'ditolak') DEFAULT 'diajukan',
    keterangan TEXT,
    FOREIGN KEY (anggota_id) REFERENCES anggota(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- 6. Tabel Angsuran
CREATE TABLE IF NOT EXISTS angsuran (
    id INT AUTO_INCREMENT PRIMARY KEY,
    pinjaman_id INT NOT NULL,
    petugas_id INT NOT NULL,
    angsuran_ke INT NOT NULL,
    jumlah_bayar DECIMAL(15, 2) NOT NULL,
    denda DECIMAL(15, 2) DEFAULT 0,
    tanggal_bayar DATETIME DEFAULT CURRENT_TIMESTAMP,
    keterangan TEXT,
    FOREIGN KEY (pinjaman_id) REFERENCES pinjaman(id),
    FOREIGN KEY (petugas_id) REFERENCES users(id)
);

-- SEEDING DATA AWAL (Dummy Data)

-- Password default: '123456' (akan di-hash di aplikasi, tapi untuk SQL ini kita asumsi plain dulu atau diupdate via app nanti)
-- Note: Dalam implementasi PHP nanti, pastikan menggunakan password_hash(). 
-- Di sini saya masukkan hash bcrypt untuk '123456' agar langsung bisa dipakai login.
-- Hash '123456' = $2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm

-- 1. Admin
INSERT INTO users (username, password, nama_lengkap, role) VALUES 
('admin', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Administrator Sistem', 'admin');

-- 2. Petugas
INSERT INTO users (username, password, nama_lengkap, role) VALUES 
('petugas', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Budi Santoso', 'petugas');

-- 3. Anggota (User Login)
INSERT INTO users (username, password, nama_lengkap, role) VALUES 
('anggota1', '$2y$10$TKh8H1.PfQx37YgCzwiKb.KjNyWgaHb9cbcoQgdIVFlYg7B77UdFm', 'Siti Aminah', 'anggota');

-- 4. Data Anggota (Link ke User Anggota1)
INSERT INTO anggota (user_id, no_anggota, nik, nama, alamat, no_hp, pekerjaan, tanggal_gabung) VALUES 
((SELECT id FROM users WHERE username='anggota1'), 'A-001', '3201123456789001', 'Siti Aminah', 'Jl. Merdeka No. 45, Jakarta', '081234567890', 'Wiraswasta', CURDATE());

-- 5. Data Jenis Simpanan
INSERT INTO jenis_simpanan (nama_simpanan, nominal_minimal, keterangan) VALUES 
('Simpanan Pokok', 50000, 'Dibayar sekali saat mendaftar'),
('Simpanan Wajib', 10000, 'Dibayar setiap bulan'),
('Simpanan Sukarela', 0, 'Tidak ada batasan minimal');
