<?php
require_once 'config/koneksi.php';

// Logic for session-based loader
$show_loader = !isset($_SESSION['has_visited']);
if ($show_loader) {
    $_SESSION['has_visited'] = true;
}

// Query untuk mengambil produk unggulan (misalnya 4 produk terbaru)
$sql_unggulan = "SELECT *, (CASE WHEN total_rating_count > 0 THEN total_rating_sum / total_rating_count ELSE 0 END) AS average_rating FROM produk ORDER BY average_rating DESC, id_produk DESC LIMIT 4";
$result_unggulan = $koneksi->query($sql_unggulan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Warung Kuncen</title>
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
        .footer {
            margin-top: auto;
        }
        .hero-section {
            background: url('assets/img/pasarjajan.webp') no-repeat center center;
            background-size: cover;
            position: relative;
            padding: 8rem 0;
            color: white;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .hero-section .container {
            position: relative;
        }
        .product-card {
            transition: transform .2s;
        }
        .product-card:hover {
            transform: scale(1.05);
        }
        .about-section {
            background-color: #ffffff;
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
        #loader-wrapper {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: #fff;
            z-index: 9999;
            display: flex;
            justify-content: center;
            align-items: center;
            transition: opacity 1s ease;
        }
        .spinner {
            border: 8px solid #f3f3f3; /* Light grey */
            border-top: 8px solid #0d6efd; /* Blue from button */
            border-radius: 50%;
            width: 60px;
            height: 60px;
            animation: spin 1s linear infinite;
        }
        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
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
    </style>
</head>
<body>

    <?php if ($show_loader): ?>
    <!-- Loader -->
    <div id="loader-wrapper">
        <div class="spinner"></div>
    </div>
    <?php endif; ?>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg bg-white shadow-sm sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center" href="beranda.php">
                <img src="assets/img/warungkuncen.png" alt="Warung Kuncen Logo" class="navbar-logo me-2">
                <strong>Warung <span style="color: #0b5ed7;">Kuncen</span></strong>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link active" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Toko</a></li>
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

    <!-- Hero Section -->
    <div class="hero-section text-center">
        <div class="container">
            <h1 class="display-4 fw-bold">Cita Rasa Otentik, Hadir Untuk Anda</h1>
            <p class="lead my-3">Nikmati kelezatan masakan tradisional Indonesia dari Warung Kuncen, kini lebih mudah dipesan secara online.</p>
            <a href="index.php" class="btn btn-primary btn-lg">Lihat Menu Lengkap</a>
        </div>
    </div>

    <!-- Featured Products Section -->
    <div class="container mt-5">
        <div class="row text-center mb-4">
            <div class="col">
                <h2>Menu Andalan</h2>
                <p class="text-muted">Beberapa pilihan favorit dari pelanggan kami.</p>
            </div>
        </div>
        <div class="row">
            <?php if ($result_unggulan && $result_unggulan->num_rows > 0): ?>
                <?php while($produk = $result_unggulan->fetch_assoc()): ?>
                    <div class="col-lg-3 col-md-6 mb-4">
                        <div class="card product-card h-100 shadow-sm border-0">
                            <img src="assets/img/<?php echo htmlspecialchars($produk['gambar']); ?>" class="card-img-top" style="height: 200px; object-fit: cover;" alt="<?php echo htmlspecialchars($produk['nama_produk']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($produk['nama_produk']); ?></h5>
                                <p class="card-text text-muted small flex-grow-1"><?php echo htmlspecialchars($produk['deskripsi']); ?></p>
                                <p class="card-text fw-bold">Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></p>
                                <a href="pesan.php?id=<?php echo $produk['id_produk']; ?>" class="btn btn-outline-primary mt-auto">Pesan Sekarang</a>
                            </div>
                        </div>
                    </div>
                <?php endwhile; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- About Us Snippet -->
    <section class="py-5 about-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-6">
                    <h2 class="fw-bold">Tentang Kami</h2>
                    <p class="text-muted">Warung Kuncen berawal dari sebuah kecintaan terhadap masakan tradisional Indonesia. Kami berkomitmen untuk menyajikan hidangan otentik yang dibuat dari bahan-bahan segar dan resep turun-temurun.</p>
                    <a href="tentang.php" class="btn btn-secondary">Baca Selengkapnya</a>
                </div>
                <div class="col-md-6 text-center">
                    <img src="assets/img/soto1.jpg" class="img-fluid rounded shadow-lg" alt="Tentang Warung Kuncen">
                </div>
            </div>
        </div>
    </section>

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

    <!-- Scroll to Top Button -->
    <a href="#" id="scrollTopBtn" class="scroll-top-btn" title="Kembali ke atas"><i class="bi bi-arrow-up"></i></a>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
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
    <?php if ($show_loader): ?>
    <script>
        // Loading screen logic
        window.addEventListener('load', function() {
            const loader = document.getElementById('loader-wrapper');
            // Fade out the loader
            loader.style.opacity = '0';
            setTimeout(() => {
                loader.style.display = 'none';
            }, 1000); // 1000ms matches the CSS transition duration
        });
    </script>
    <?php endif; ?>
</body>
</html>