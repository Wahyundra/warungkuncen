<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
// Ambil ID produk dari URL
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id_produk > 0) {
    // Cek apakah produk ini milik toko user yang login
    $sql_check_owner = "SELECT p.id_toko FROM produk p JOIN toko t ON p.id_toko = t.id_toko WHERE p.id_produk = ? AND t.id_user = ?";
    $stmt_check_owner = $koneksi->prepare($sql_check_owner);
    $stmt_check_owner->bind_param("ii", $id_produk, $user_id);
    $stmt_check_owner->execute();
    $result_check_owner = $stmt_check_owner->get_result();

    if ($result_check_owner->num_rows === 0) {
        echo "Anda tidak memiliki akses untuk menghapus produk ini.";
        exit;
    }
    $id_toko = $result_check_owner->fetch_assoc()['id_toko'];
    $stmt_check_owner->close();

    // Hapus produk dari database
    $sql = "DELETE FROM produk WHERE id_produk = ?";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("i", $id_produk);
    
    if ($stmt->execute()) {
        // Redirect kembali ke halaman edit menu toko dengan status sukses
        header("Location: user_edit_menu_toko.php?id_toko=" . $id_toko . "&status=sukses_hapus");
        exit;
    } else {
        // Redirect dengan status gagal
        header("Location: user_edit_menu_toko.php?id_toko=" . $id_toko . "&status=gagal_hapus");
        exit;
    }
    
    $stmt->close();
} else {
    // Jika ID tidak valid, redirect ke halaman profile
    header("Location: profile.php");
    exit;
}

$koneksi->close();
?>