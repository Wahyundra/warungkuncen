<?php
// Konfigurasi Database
define('DB_HOST', 'localhost');
define('DB_USER', 'root'); // Ganti dengan username database Anda
define('DB_PASS', ''); // Ganti dengan password database Anda
define('DB_NAME', 'dbwarkun');

// Membuat Koneksi
$koneksi = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// Cek Koneksi
if ($koneksi->connect_error) {
    die("Koneksi ke database gagal: " . $koneksi->connect_error);
}

// Mengatur base URL
// Pastikan untuk menyesuaikan ini jika aplikasi Anda berada di dalam subfolder
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443) ? "https://" : "http://";
$host = $_SERVER['HTTP_HOST'];
$script_name = str_replace(basename($_SERVER['SCRIPT_NAME']), '', $_SERVER['SCRIPT_NAME']);

// Menghapus subdirektori 'admin' jika ada, agar base URL konsisten
$base_path = preg_replace('/\/admin\/?$/', '/', $script_name);

define('BASE_URL', $protocol . $host . $base_path);

// Memulai session
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
?>
