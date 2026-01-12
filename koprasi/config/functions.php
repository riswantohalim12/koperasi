<?php
// config/functions.php

// Mulai sesi jika belum dimulai
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

/**
 * Mendapatkan URL dasar aplikasi
 * @param string $path Path tambahan (opsional)
 * @return string URL lengkap
 */
function base_url($path = '') {
    // Sesuaikan URL dasar sesuai folder project di htdocs/www
    // Contoh: http://localhost/koperasi_simpan_pinjam/
    // Karena kita tidak tahu folder pastinya, kita gunakan relative path untuk sementara
    // atau deteksi otomatis
    
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    
    // Deteksi folder root script
    $scriptDir = dirname($_SERVER['SCRIPT_NAME']);
    // Normalisasi slash
    $scriptDir = str_replace('\\', '/', $scriptDir);
    
    // Jika path diawali slash, hapus
    $path = ltrim($path, '/');
    
    // Karena kita menggunakan struktur flat/simple, kita asumsikan root adalah directory tempat index.php
    // Namun fungsi ini dipanggil dari berbagai kedalaman.
    // Cara paling aman untuk PHP Native tanpa routing framework adalah define BASE_URL di index.php atau config.
    
    // Kita hardcode sementara untuk development, idealnya didefine di config utama
    // return "http://localhost/koperasi/" . $path;
    
    // Opsi dinamis:
    return $path; 
}

/**
 * Format angka ke Rupiah
 * @param int $angka
 * @return string
 */
function format_rupiah($angka) {
    return "Rp " . number_format($angka, 0, ',', '.');
}

/**
 * Membersihkan input dari karakter berbahaya (XSS prevention)
 * @param string $data
 * @return string
 */
function clean_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

/**
 * Cek apakah user sudah login
 * @return void Redirect jika belum login
 */
function cek_login() {
    if (!isset($_SESSION['user_id'])) {
        header("Location: login.php");
        exit();
    }
}

/**
 * Cek akses role
 * @param array $allowed_roles Array role yang diizinkan, contoh: ['admin', 'petugas']
 * @return void Redirect jika tidak punya akses
 */
function cek_akses($allowed_roles) {
    if (!isset($_SESSION['role']) || !in_array($_SESSION['role'], $allowed_roles)) {
        // Redirect ke halaman unauthorized atau dashboard masing-masing
        echo "<script>alert('Anda tidak memiliki akses ke halaman ini!'); window.location.href='index.php';</script>";
        exit();
    }
}

/**
 * Flash message untuk notifikasi
 * @param string $key
 * @param string $message
 * @param string $type (success, danger, warning, info)
 */
function set_flash_message($key, $message, $type = 'success') {
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

function get_flash_message($key) {
    if (isset($_SESSION['flash'][$key])) {
        $msg = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]);
        return "<div class='alert alert-{$msg['type']} alert-dismissible fade show' role='alert'>
                    {$msg['message']}
                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                </div>";
    }
    return "";
}
?>
