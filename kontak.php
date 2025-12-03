<?php
require_once 'config/koneksi.php';

$pesan_sukses = '';
$pesan_error = '';

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama = trim($_POST['nama']);
    $email = trim($_POST['email']);
    $subjek = trim($_POST['subjek']);
    $pesan = trim($_POST['pesan']);

    if (!empty($nama) && !empty($email) && !empty($subjek) && !empty($pesan)) {
        // Simpan data ke database
        $sql = "INSERT INTO pesan_kontak (nama_pengirim, email_pengirim, subjek_pesan, isi_pesan) VALUES (?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("ssss", $nama, $email, $subjek, $pesan);
        
        if ($stmt->execute()) {
            $pesan_sukses = "Terima kasih! Pesan Anda telah berhasil dikirim.";
        } else {
            $pesan_error = "Maaf, terjadi kesalahan saat mengirim pesan Anda. Silakan coba lagi.";
        }
        $stmt->close();
    } else {
        $pesan_error = "Harap isi semua kolom yang wajib diisi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kontak Kami - Warung Kuncen</title>
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
        .map-container {
            position: relative;
            overflow: hidden;
            padding-top: 56.25%; /* 16:9 Aspect Ratio */
        }
        .map-container iframe {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            border: 0;
        }
        /* Custom Navbar Styles */
        .navbar {
            transition: all 0.3s ease-in-out;
        }
        .navbar .navbar-brand {
            font-size: 1.4rem;
            padding-top: 0.5rem;
            padding-bottom: 0.5rem;
        }
        .navbar .nav-link {
            padding-top: 0.8rem;
            padding-bottom: 0.8rem;
            position: relative;
            transition: color 0.3s ease-in-out;
        }
        .navbar.navbar-scrolled {
            box-shadow: 0 4px 6px rgba(0,0,0,.07);
        }
        /* Navbar Active Link Underline */
        .navbar .nav-link::after {
            content: '';
            position: absolute;
            bottom: 0.5rem;
            left: 50%;
            transform: translateX(-50%);
            width: 0;
            height: 2px;
            background-color: #0d6efd;
            transition: width 0.3s ease-in-out;
        }
        .navbar .nav-link:hover::after,
        .navbar .nav-link.active::after {
            width: 70%;
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
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item"><a class="nav-link" href="beranda.php">Beranda</a></li>
                    <li class="nav-item"><a class="nav-link" href="tentang.php">Tentang</a></li>
                    <li class="nav-item"><a class="nav-link" href="index.php">Toko</a></li>
                    <?php if (!isset($_SESSION['user_id'])): ?>
                        <li class="nav-item"><a class="nav-link" href="transaksi.php">Lacak Pesanan</a></li>
                    <?php endif; ?>
                    <li class="nav-item"><a class="nav-link active" href="kontak.php">Kontak</a></li>
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
    <div class="container my-5">
        <div class="row">
            <div class="col-12 text-center mb-5">
                <h1>Hubungi Kami</h1>
                <p class="lead text-muted">Kami senang mendengar dari Anda. Silakan gunakan informasi di bawah ini atau kirim pesan melalui formulir.</p>
            </div>
        </div>
        <div class="row g-5">
            <!-- Contact Info -->
            <div class="col-lg-4">
                <h3 class="mb-4">Informasi Kontak</h3>
                <div class="d-flex mb-3">
                    <i class="bi bi-geo-alt-fill fs-4 text-primary me-3"></i>
                    <div>
                        <strong>Alamat:</strong><br>
                        Jl. 24 Purwasaba, Mandiraja, Banjarnegara, Jawa Tengah
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <i class="bi bi-envelope-fill fs-4 text-primary me-3"></i>
                    <div>
                        <strong>Email:</strong><br>
                        warungkuncen@gmail.com
                    </div>
                </div>
                <div class="d-flex mb-3">
                    <i class="bi bi-telephone-fill fs-4 text-primary me-3"></i>
                    <div>
                        <strong>Telepon:</strong><br>
                        +62 878-5361-1837
                    </div>
                </div>
                 <div class="d-flex">
                    <i class="bi bi-clock-fill fs-4 text-primary me-3"></i>
                    <div>
                        <strong>Jam Buka:</strong><br>
                        Setiap Hari, 08:00 - 20:00 WIB
                    </div>
                </div>
            </div>

            <!-- Contact Form -->
            <div class="col-lg-8">
                <h3 class="mb-4">Kirim Pesan</h3>
                <?php if ($pesan_sukses): ?>
                    <div class="alert alert-success"><?php echo $pesan_sukses; ?></div>
                <?php endif; ?>
                <?php if ($pesan_error): ?>
                    <div class="alert alert-danger"><?php echo $pesan_error; ?></div>
                <?php endif; ?>
                <form action="kontak.php" method="POST" class="card p-4 border-0 shadow-sm">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nama" class="form-label">Nama Anda</label>
                            <input type="text" class="form-control" id="nama" name="nama" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email Anda</label>
                            <input type="email" class="form-control" id="email" name="email" required>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="subjek" class="form-label">Subjek</label>
                        <input type="text" class="form-control" id="subjek" name="subjek" required>
                    </div>
                    <div class="mb-3">
                        <label for="pesan" class="form-label">Pesan</label>
                        <textarea class="form-control" id="pesan" name="pesan" rows="5" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary">Kirim Pesan</button>
                </form>
            </div>
        </div>

        <!-- Google Maps -->
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="text-center mb-4">Lokasi Kami</h3>
                <div class="card shadow-sm">
                    <div class="map-container">
                        <iframe src="https://www.google.com/maps/embed?pb=!1m14!1m12!1m3!1d247.2498015609427!2d109.47337680301753!3d-7.465600425074479!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!5e0!3m2!1sid!2sid!4v1756268251247!5m2!1sid!2sid" allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Navbar scroll effect
        document.addEventListener('DOMContentLoaded', function() {
            const navbar = document.querySelector('.navbar');
            if (navbar) {
                const handleScroll = () => {
                    if (window.scrollY > 20) {
                        navbar.classList.add('navbar-scrolled');
                    } else {
                        navbar.classList.remove('navbar-scrolled');
                    }
                };
                window.addEventListener('scroll', handleScroll);
                handleScroll();
            }
        });
    </script>
</body>
</html>
