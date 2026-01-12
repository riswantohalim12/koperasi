<?php
// config/database.php

$host = 'localhost';
$dbname = 'koperasi_simpan_pinjam';
$username = 'root';
$password = '12345678'; // Sesuaikan dengan konfigurasi server

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    
    // Set mode error PDO ke exception
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    // Set default fetch mode ke Associative Array
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    // Jika koneksi gagal
    die("Koneksi Database Gagal: " . $e->getMessage());
}
?>
