<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Ambil ID produk dari URL
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk > 0) {
    // Hapus produk dari database
    // Sebaiknya tambahkan juga logika untuk menghapus file gambar dari server jika diperlukan
    
    $sql = "DELETE FROM produk WHERE id_produk = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_produk);
    
    if ($stmt->execute()) {
        // Redirect kembali ke halaman kelola produk dengan status sukses
        header("Location: kelola_produk.php?status=sukses_hapus");
        exit;
    } else {
        // Redirect dengan status gagal
        header("Location: kelola_produk.php?status=gagal_hapus");
        exit;
    }
    
    $stmt->close();
} else {
    // Jika ID tidak valid, redirect ke halaman kelola produk
    header("Location: kelola_produk.php");
    exit;
}

$koneksi->close();
?>
