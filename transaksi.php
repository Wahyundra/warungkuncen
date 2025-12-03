<?php
require_once 'config/koneksi.php';

$pesanan = [];
$email_dicari = '';

// Logika untuk mencari pesanan berdasarkan email
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['lacak'])) {
    $email_dicari = $_POST['email'];
    if (!empty($email_dicari)) {
        $sql = "SELECT p.id_pesanan, p.tanggal_pesan, p.total_harga, p.status_pesanan
                FROM pesanan p
                JOIN user u ON p.id_user = u.id_user
                WHERE u.email = ?
                ORDER BY p.tanggal_pesan DESC";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param('s', $email_dicari);
        $stmt->execute();
        $result = $stmt->get_result();
        while ($row = $result->fetch_assoc()) {
            $pesanan[] = $row;
        }
        $stmt->close();
    }
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lacak Pesanan - Warung Kuncen</title>
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
                    <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Toko</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link active" href="transaksi.php">Lacak Pesanan</a></li>
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
        
        <?php if (isset($_GET['status']) && $_GET['status'] == 'sukses'): ?>
        <div class="alert alert-success text-center">
            <h4>Terima Kasih!</h4>
            <p>Pesanan Anda dengan ID <strong>#<?php echo htmlspecialchars($_GET['id_pesanan']); ?></strong> telah berhasil kami terima dan akan segera diproses.</p>
        </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <h3 class="card-title text-center mb-4">Lacak Status Pesanan Anda</h3>
                <p class="text-center text-muted">Masukkan alamat email yang Anda gunakan saat memesan untuk melihat riwayat dan status pesanan.</p>
                <div class="row justify-content-center">
                    <div class="col-md-6">
                        <form method="POST" action="transaksi.php">
                            <div class="input-group mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Masukkan email Anda..." value="<?php echo htmlspecialchars($email_dicari); ?>" required>
                                <button class="btn btn-primary" type="submit" name="lacak">Lacak</button>
                            </div>
                        </form>
                    </div>
                </div>

                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST'): ?>
                    <hr>
                    <h4 class="mt-4">Riwayat Pesanan untuk: <?php echo htmlspecialchars($email_dicari); ?></h4>
                    <?php if (!empty($pesanan)): ?>
                        <table class="table table-striped table-bordered mt-3">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Tanggal</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($pesanan as $p): ?>
                                    <tr>
                                        <td>#<?php echo $p['id_pesanan']; ?></td>
                                        <td><?php echo date('d M Y, H:i', strtotime($p['tanggal_pesan'])); ?></td>
                                        <td>Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></td>
                                        <td><span class="badge bg-info"><?php echo ucfirst($p['status_pesanan']); ?></span></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p class="text-center mt-3">Tidak ada riwayat pesanan yang ditemukan untuk email ini.</p>
                    <?php endif; ?>
                <?php endif; ?>
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
