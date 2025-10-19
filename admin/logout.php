<?php
// Memulai atau melanjutkan sesi yang sudah ada
session_start();

// Menghapus semua variabel sesi
$_SESSION = array();

// Menghancurkan sesi
session_destroy();

// Mengarahkan pengguna kembali ke halaman login
header("Location: login.php");
exit;
?>
