<?php
require_once 'config/koneksi.php';

$id_toko = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : 0;

if ($id_toko <= 0) {
    header("Location: index.php"); // Redirect if no valid shop ID
    exit;
}

// Fetch shop details
$sql_toko = "SELECT * FROM toko WHERE id_toko = ?";
$stmt_toko = $koneksi->prepare($sql_toko);
$stmt_toko->bind_param("i", $id_toko);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
$shop = $result_toko->fetch_assoc();
$stmt_toko->close();

if (!$shop) {
    echo "Toko tidak ditemukan.";
    exit;
}

// Fetch products for this shop
$sql_produk = "SELECT * FROM produk WHERE id_toko = ? ORDER BY nama_produk ASC";
$stmt_produk = $koneksi->prepare($sql_produk);
$stmt_produk->bind_param("i", $id_toko);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();
$stmt_produk->close();

// // Handle rating submission
// if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['submit_rating']) && isset($_SESSION['user_id'])) {
//     $id_produk_rated = (int)$_POST['id_produk_rated'];
//     $user_id = $_SESSION['user_id'];

//     if (!isset($_POST['rating'])) {
//         $error_rating = "Harap pilih rating bintang.";
//     } else {
//         $rating_value = (int)$_POST['rating'];
//         // Validasi rating
//         if ($rating_value < 1 || $rating_value > 5) {
//             $error_rating = "Nilai rating tidak valid.";
//         } else {
//         // Cek apakah user sudah pernah memberi rating untuk produk ini
//         $sql_check_rating = "SELECT id FROM product_ratings WHERE id_produk = ? AND id_user = ?";
//         $stmt_check_rating = $koneksi->prepare($sql_check_rating);
//         $stmt_check_rating->bind_param("ii", $id_produk_rated, $user_id);
//         $stmt_check_rating->execute();
//         $result_check_rating = $stmt_check_rating->get_result();

//         if ($result_check_rating->num_rows > 0) {
//             $error_rating = "Anda sudah pernah memberi rating untuk produk ini.";
//         } else {
//             // Mulai transaksi
//             $koneksi->begin_transaction();
//             try {
//                 // 1. Simpan rating baru ke tabel product_ratings
//                 $sql_insert_rating = "INSERT INTO product_ratings (id_produk, id_user, rating_value) VALUES (?, ?, ?)";
//                 $stmt_insert_rating = $koneksi->prepare($sql_insert_rating);
//                 $stmt_insert_rating->bind_param("iii", $id_produk_rated, $user_id, $rating_value);
//                 $stmt_insert_rating->execute();
//                 $stmt_insert_rating->close();

//                 // 2. Update total_rating_sum dan total_rating_count di tabel produk
//                 $sql_update_product_rating = "UPDATE produk SET total_rating_sum = total_rating_sum + ?, total_rating_count = total_rating_count + 1 WHERE id_produk = ?";
//                 $stmt_update_product_rating = $koneksi->prepare($sql_update_product_rating);
//                 $stmt_update_product_rating->bind_param("ii", $rating_value, $id_produk_rated);
//                 $stmt_update_product_rating->execute();
//                 $stmt_update_product_rating->close();

//                 // 3. Recalculate average rating for the product and update it (optional, can be done on display)
//                 // For simplicity, we'll just update sum/count and calculate on display.

//                 // 4. Recalculate average rating for the shop
//                 // Get all product ratings for this shop
//                 $sql_shop_avg_rating = "SELECT SUM(p.total_rating_sum) as shop_total_sum, SUM(p.total_rating_count) as shop_total_count 
//                                         FROM produk p 
//                                         WHERE p.id_toko = ?";
//                 $stmt_shop_avg_rating = $koneksi->prepare($sql_shop_avg_rating);
//                 $stmt_shop_avg_rating->bind_param("i", $id_toko);
//                 $stmt_shop_avg_rating->execute();
//                 $result_shop_avg_rating = $stmt_shop_avg_rating->get_result();
//                 $shop_rating_data = $result_shop_avg_rating->fetch_assoc();
//                 $stmt_shop_avg_rating->close();

//                 $new_shop_rating = 0;
//                 if ($shop_rating_data['shop_total_count'] > 0) {
//                     $new_shop_rating = $shop_rating_data['shop_total_sum'] / $shop_rating_data['shop_total_count'];
//                 }

//                 $sql_update_shop_rating = "UPDATE toko SET rating_toko = ? WHERE id_toko = ?";
//                 $stmt_update_shop_rating = $koneksi->prepare($sql_update_shop_rating);
//                 $stmt_update_shop_rating->bind_param("di", $new_shop_rating, $id_toko);
//                 $stmt_update_shop_rating->execute();
//                 $stmt_update_shop_rating->close();

//                 $koneksi->commit();
//                 $success_rating = "Terima kasih atas rating Anda!";
//                 // Refresh shop data to show updated rating
//                 $shop['rating_toko'] = $new_shop_rating;

//             } catch (Exception $e) {
//                 $koneksi->rollback();
//                 $error_rating = "Gagal menyimpan rating: " . $e->getMessage();
//             }
//             $stmt_check_rating->close();
//         }
//     } // This curly brace closes the 'else' block for !isset($_POST['rating'])
// }
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($shop['nama_toko']); ?> - Warung Kuncen</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="assets/img/warungkuncen.png">
    <style>
        .bg-dark-blue {
            background-color: #000080 !important; /* Biru Dongker (Navy Blue) */
        }
        html {
            overflow-y: scroll;
        }
        body {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            background-color: #f8f9fa;
        }
        .navbar {
            background-color: #ffffff;
            box-shadow: 0 2px 4px rgba(0,0,0,.1);
        }
        .product-card {
            transition: transform .2s;
            margin-bottom: 2rem;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .card-img-top {
            height: 200px;
            object-fit: cover;
        }
        .footer {
            background-color: #343a40;
            color: white;
            padding: 2rem 0;
            margin-top: auto; /* Mendorong footer ke bawah */
        }
        .scroll-top-btn {
            position: fixed;
            bottom: 20px;
            right: 20px;
            display: none; /* Hidden by default */
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background-color: #0d6efd;
            color: white;
            text-align: center;
            line-height: 1.9; /* Adjusted for vertical centering */
            font-size: 20px;
            z-index: 1000;
            transition: opacity 0.3s, visibility 0.3s;
        }
        .scroll-top-btn:hover {
            background-color: #0b5ed7;
        }

        /* Custom Navbar Styles */
        .navbar {
            /* Smooth transition for background and shadow */
            transition: all 0.3s ease-in-out;
        }
        .navbar .navbar-brand {
            font-size: 1.4rem; /* Slightly larger brand */
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .navbar .nav-link {
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
        }
        /* Style for when the page is scrolled */
        .navbar.navbar-scrolled {
            box-shadow: 0 4px 6px rgba(0,0,0,.07);
        }

        /* Navbar Active Link Underline */
        .navbar .nav-link {
            position: relative;
            transition: color 0.3s ease-in-out;
        }
        .navbar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0.5rem; /* Adjust position to not be too close to the text */
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: #0d6efd;
            transition: width 0.3s ease-in-out;
        }
        .navbar .nav-link:hover::after,
        .navbar .nav-link.active::after {
            width: 70%; /* A bit smaller than full width for a nicer look */
        }
        .navbar .nav-link.active {
            color: #0d6efd !important;
        }
        .navbar-logo {
            width: 40px; /* Adjust size as needed */
            height: 40px; /* Ensure height matches width for perfect circle */
            border-radius: 50%; /* Makes it circular */
            object-fit: cover; /* Ensures image covers the circular area */
        }
        .rating-input label {
            font-size: 1.5rem;
            color: #ccc;
            cursor: pointer;
            padding: 0 2px;
        }
        .rating-input label:hover,
        .rating-input label:hover ~ label,
        .rating-input input:checked ~ label {
            color: #ffc107; /* Bootstrap's warning yellow */
        }
        .rating-input {
            direction: rtl; /* Right-to-left for star selection */
        }
        .rating-input label i {
            display: block;
        }
        
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="beranda.php">
                <img src="assets/img/warungkuncen.png" alt="Warung Kuncen Logo" class="navbar-logo me-2">
                <strong>Warung <span style="color: #0b5ed7;">Kuncen</span></strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link active" href="index.php">Menu</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="transaksi.php">Lacak Pesanan</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                </ul>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="btn btn-primary ms-lg-3">Profile</a>
                    <a href="logout_user.php" class="btn btn-outline-secondary ms-lg-3">Logout</a>
                <?php else: ?>
                    <a href="login.php" class="btn btn-primary ms-lg-3">Login</a>
                <?php endif; ?>
                <a href="pesan.php" class="btn btn-outline-primary ms-lg-3">
                    <i class="bi bi-cart"></i> Keranjang
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="mb-3">
            <a href="index.php" class="btn btn-outline-secondary"><i class="bi bi-arrow-left"></i> Kembali ke Halaman Toko</a>
        </div>
        <?php if (isset($_SESSION['success_message'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['success_message']; unset($_SESSION['success_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (isset($_SESSION['error_message'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $_SESSION['error_message']; unset($_SESSION['error_message']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <div class="row text-center mb-4">
            <div class="col">
                <h2>Menu dari <?php echo htmlspecialchars($shop['nama_toko']); ?></h2>
                <p class="lead text-muted"><?php echo htmlspecialchars($shop['deskripsi_toko']); ?></p>
                <p class="small text-muted mb-1"><i class="bi bi-geo-alt-fill"></i> Lokasi: <?php echo htmlspecialchars($shop['lokasi_toko']); ?></p>
                <p class="small text-warning mb-2">
                    <?php
                    $rating = $shop['rating_toko'];
                    for ($i = 1; $i <= 5; $i++) {
                        if ($rating >= $i) {
                            echo '<i class="bi bi-star-fill"></i>';
                        } elseif ($rating > ($i - 1)) {
                            echo '<i class="bi bi-star-half"></i>';
                        } else {
                            echo '<i class="bi bi-star"></i>';
                        }
                    }
                    ?> (<?php echo number_format($rating, 1); ?>/5)
                </p>
            </div>
        </div>

        <div class="row">


            <?php if ($result_produk && $result_produk->num_rows > 0): ?>
                <?php while($row = $result_produk->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6">
                        <div class="card product-card shadow-sm">
                            <img src="assets/img/<?php echo htmlspecialchars($row['gambar']); ?>" class="card-img-top" alt="<?php echo htmlspecialchars($row['nama_produk']); ?>">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['nama_produk']); ?></h5>
                                <p class="card-text"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <p class="card-text"><strong>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></strong></p>
                                <?php
                                $product_rating = 0;
                                if ($row['total_rating_count'] > 0) {
                                    $product_rating = $row['total_rating_sum'] / $row['total_rating_count'];
                                }
                                ?>
                                <p class="small text-warning mb-2">
                                    <?php
                                    for ($i = 1; $i <= 5; $i++) {
                                        if ($product_rating >= $i) {
                                            echo '<i class="bi bi-star-fill"></i>';
                                        } elseif ($product_rating > ($i - 1)) {
                                            echo '<i class="bi bi-star-half"></i>';
                                        } else {
                                            echo '<i class="bi bi-star"></i>';
                                        }
                                    }
                                    ?> (<?php echo number_format($product_rating, 1); ?>/5)
                                </p>

                                <?php if (isset($_SESSION['user_id'])): ?>
                                    <form action="submit_rating.php" method="POST" class="mb-3">
                                        <input type="hidden" name="id_produk_rated" value="<?php echo $row['id_produk']; ?>">
                                        <input type="hidden" name="id_toko" value="<?php echo $id_toko; ?>">
                                        <div class="rating-input d-flex justify-content-center mb-2">
                                            <?php for ($i = 5; $i >= 1; $i--): ?>
                                                <input type="radio" id="star<?php echo $row['id_produk'] . '-' . $i; ?>" name="rating" value="<?php echo $i; ?>" class="d-none" />
                                                <label for="star<?php echo $row['id_produk'] . '-' . $i; ?>" title="<?php echo $i; ?> stars">
                                                    <i class="bi bi-star"></i>
                                                </label>
                                            <?php endfor; ?>
                                        </div>
                                        <button type="submit" name="submit_rating" class="btn btn-sm btn-outline-primary w-100">Beri Rating</button>
                                    </form>
                                <?php else: ?>
                                    <p class="text-center small text-muted">Login untuk memberi rating.</p>
                                <?php endif; ?>
                                
                                <a href="pesan.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-primary w-100">Tambah ke Keranjang</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col">
                    <p class="text-center">Belum ada menu yang tersedia untuk toko ini.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer bg-dark-blue text-white py-4">
        <div class="container">
            <div class="row">
                <div class="col-md-6 mb-4 mb-md-0">
                    <h5>Tentang Warung Kuncen</h5>
                    <p class="text-white-50 small">Menyajikan Cita Rasa Otentik Sejak 2023. Dibuat dari bahan-bahan segar dan resep turun-temurun untuk menjaga kualitas rasa asli kuliner nusantara.</p>
                </div>
                <div class="col-md-6">
                    <h5>Hubungi Kami</h5>
                    <ul class="list-unstyled text-white-50 small">
                        <li><strong>Alamat:</strong> Jl. 24 Purwasaba, Mandiraja</li>
                        <li><strong>Jam Buka:</strong> Setiap Hari, 08:00 - 20:00 WIB</li>
                        <li><strong>Email:</strong> warungkuncen@gmail.com</li>
                    </ul>
                </div>
            </div>
            <div class="text-center border-top border-white-50 pt-3 mt-3">
                <p class="small text-white-50">&copy; <?php echo date('Y'); ?> Warung Kuncen. Semua Hak Dilindungi. | <a href="admin/login.php" class="text-white">Admin Login</a></p>
            </div>
        </div>
    </footer>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Scroll to Top Button -->
    <a href="#" id="scrollTopBtn" class="scroll-top-btn" title="Kembali ke atas"><i class="bi bi-arrow-up"></i></a>

    <script>
        // Get the button
        let scrollTopBtn = document.getElementById("scrollTopBtn");

        // When the user scrolls down 100px from the top of the document, show the button
        window.onscroll = function() {scrollFunction()};

        function scrollFunction() {
          if (document.body.scrollTop > 100 || document.documentElement.scrollTop > 100) {
            scrollTopBtn.style.display = "block";
          } else {
            scrollTopBtn.style.display = "none";
          }
        }

        // When the user clicks on the button, scroll to the top of the document
        scrollTopBtn.addEventListener("click", function(e) {
          e.preventDefault();
          window.scrollTo({top: 0, behavior: 'smooth'});
        });
    </script>

    <script>
        // Navbar scroll effect
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                // Function to add/remove class based on scroll position
                const handleScroll = () => {
                    if (window.scrollY > 20) {
                        navbar.classList.add('navbar-scrolled');
                    } else {
                        navbar.classList.remove('navbar-scrolled');
                    }
                };
                // Listen for scroll events
                window.addEventListener('scroll', handleScroll);
                // Initial check in case the page is already scrolled
                handleScroll();
            }
        });
    </script>
</body>
</html>