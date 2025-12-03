<?php
require_once 'config/koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating'])) {
    $id_produk_rated = isset($_POST['id_produk_rated']) ? (int)$_POST['id_produk_rated'] : 0;
    $id_toko = isset($_POST['id_toko']) ? (int)$_POST['id_toko'] : 0;
    $user_id = $_SESSION['user_id'];
    $rating_value = isset($_POST['rating']) ? (int)$_POST['rating'] : 0;

    if ($id_produk_rated <= 0 || $id_toko <= 0 || $rating_value < 1 || $rating_value > 5) {
        $_SESSION['error_message'] = "Data rating tidak valid.";
        header("Location: shop_detail.php?id_toko=" . $id_toko);
        exit;
    }

    // Mulai transaksi
    $koneksi->begin_transaction();
    try {
        // 1. Simpan rating baru ke tabel product_ratings
        $sql_insert_rating = "INSERT INTO product_ratings (id_produk, id_user, rating_value) VALUES (?, ?, ?)";
        $stmt_insert_rating = $koneksi->prepare($sql_insert_rating);
        $stmt_insert_rating->bind_param("iii", $id_produk_rated, $user_id, $rating_value);
        $stmt_insert_rating->execute();
        $stmt_insert_rating->close();

        // 2. Update total_rating_sum dan total_rating_count di tabel produk
        $sql_update_product_rating = "UPDATE produk SET total_rating_sum = total_rating_sum + ?, total_rating_count = total_rating_count + 1 WHERE id_produk = ?";
        $stmt_update_product_rating = $koneksi->prepare($sql_update_product_rating);
        $stmt_update_product_rating->bind_param("ii", $rating_value, $id_produk_rated);
        $stmt_update_product_rating->execute();
        $stmt_update_product_rating->close();

        // 3. Recalculate average rating for the shop
        // Get all product ratings for this shop
        $sql_shop_avg_rating = "SELECT SUM(p.total_rating_sum) as shop_total_sum, SUM(p.total_rating_count) as shop_total_count 
                                FROM produk p 
                                WHERE p.id_toko = ?";
        $stmt_shop_avg_rating = $koneksi->prepare($sql_shop_avg_rating);
        $stmt_shop_avg_rating->bind_param("i", $id_toko);
        $stmt_shop_avg_rating->execute();
        $result_shop_avg_rating = $stmt_shop_avg_rating->get_result();
        $shop_rating_data = $result_shop_avg_rating->fetch_assoc();
        $stmt_shop_avg_rating->close();

        $new_shop_rating = 0;
        if ($shop_rating_data['shop_total_count'] > 0) {
            $new_shop_rating = $shop_rating_data['shop_total_sum'] / $shop_rating_data['shop_total_count'];
        }

        $sql_update_shop_rating = "UPDATE toko SET rating_toko = ? WHERE id_toko = ?";
        $stmt_update_shop_rating = $koneksi->prepare($sql_update_shop_rating);
        $stmt_update_shop_rating->bind_param("di", $new_shop_rating, $id_toko);
        $stmt_update_shop_rating->execute();
        $stmt_update_shop_rating->close();

        $koneksi->commit();
        $_SESSION['success_message'] = "Terima kasih atas rating Anda!";
        header("Location: shop_detail.php?id_toko=" . $id_toko . "&_t=" . time());
        exit;

    } catch (Exception $e) {
        $koneksi->rollback();
        $_SESSION['error_message'] = "Gagal menyimpan rating: " . $e->getMessage();
        header("Location: shop_detail.php?id_toko=" . $id_toko);
        exit;
    }
} else {
    header("Location: index.php"); // Redirect if not a POST request or submit_rating not set
    exit;
}
?>