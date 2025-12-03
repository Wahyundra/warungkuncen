<?php
require_once 'config/koneksi.php';
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tentang Kami - Warung Kuncen</title>
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
        }
        .footer {
            margin-top: auto; /* Mendorong footer ke bawah */
        }
        .hero-section {
            background: url('assets/img/soto1.jpg') no-repeat center center;
            background-size: cover;
            color: white;
            padding: 100px 0;
            text-align: center;
            position: relative;
        }
        .hero-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.5);
        }
        .hero-section .container {
            position: relative;
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
                    <li class="nav-item"><a class="nav-link active" href="tentang.php">Tentang</a></li>
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
    <div class="hero-section">
        <div class="container">
            <h1 class="display-4">Tentang Warung Kuncen</h1>
            <p class="lead">Menyajikan Cita Rasa Otentik Sejak 2010</p>
        </div>
    </div>

    <!-- Main Content -->
    <div class="container mt-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h2 class="text-center mb-4">Tentang Kami</h2>
                <p class="text-muted">Warung Kuncen berawal dari sebuah kecintaan terhadap masakan tradisional Indonesia. Didirikan pada tahun 2023, kami berkomitmen untuk menyajikan hidangan otentik yang dibuat dari bahan-bahan segar dan resep turun-temurun. Nama 'Kuncen' dipilih karena kami bercita-cita menjadi penjaga (kuncen) dari cita rasa asli kuliner nusantara.</p>
                <p class="text-muted">Mulai dari soto ayam yang hangat, ketoprak dengan bumbu kacang yang khas, hingga jajanan seperti donat dan roti tape, setiap hidangan di Warung Kuncen dibuat dengan penuh cinta dan perhatian. Kini, kami hadir secara online untuk memudahkan Anda menikmati hidangan favorit tanpa harus keluar rumah.</p>
                <p class="text-muted">Warung Kuncen ini berlokasi di desa Purwasaba kecamatan Mandiraja. Datang dan nikmati suasana hangat di warung kami atau pesan secara online untuk menikmati kelezatan Warung Kuncen di rumah Anda.</p>
                </div>
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
</body>
</html>
