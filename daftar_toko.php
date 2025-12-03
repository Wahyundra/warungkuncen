<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama_toko = htmlspecialchars($_POST['nama_toko']);
    $deskripsi_toko = htmlspecialchars($_POST['deskripsi_toko']);
    $lokasi_toko = htmlspecialchars($_POST['lokasi_toko']);

    // Handle shop image upload
    $gambar_toko = 'default_shop.jpg'; // Default image for shop
    if (isset($_FILES['gambar_toko']) && $_FILES['gambar_toko']['error'] == 0) {
        $target_dir = "assets/img/"; // Relative to daftar_toko.php
        $gambar_toko = basename($_FILES["gambar_toko"]["name"]);
        $target_file = $target_dir . $gambar_toko;
        
        if (!move_uploaded_file($_FILES["gambar_toko"]["tmp_name"], $target_file)) {
            $message = "<div class='alert alert-danger'>Maaf, terjadi kesalahan saat mengupload gambar toko.</div>";
            // Optionally, exit or handle error more gracefully
        }
    }

    // Handle menu image upload
    if (isset($_FILES['gambar_menu']) && $_FILES['gambar_menu']['error'] == 0) {
        $target_dir = "assets/img/"; // Relative to daftar_toko.php
        $gambar_menu = basename($_FILES["gambar_menu"]["name"]);
        $target_file = $target_dir . $gambar_menu;
        
        if (!move_uploaded_file($_FILES["gambar_menu"]["tmp_name"], $target_file)) {
            $message = "<div class='alert alert-danger'>Maaf, terjadi kesalahan saat mengupload gambar menu.</div>";
            // Optionally, exit or handle error more gracefully
        }
    }

    $koneksi->begin_transaction();

    try {
        // Insert into toko table
        $sql_toko = "INSERT INTO toko (id_user, nama_toko, deskripsi_toko, lokasi_toko, gambar_toko, status_toko) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt_toko = $koneksi->prepare($sql_toko);
        $status_toko_pending = 'pending';
        $stmt_toko->bind_param("isssss", $_SESSION['user_id'], $nama_toko, $deskripsi_toko, $lokasi_toko, $gambar_toko, $status_toko_pending);
        $stmt_toko->execute();
        $id_toko = $koneksi->insert_id;
        $stmt_toko->close();

        $koneksi->commit();
        $message = "<div class='alert alert-success'>Terima kasih! Toko Anda (<strong>{$nama_toko}</strong>) telah berhasil didaftarkan. Admin akan segera meninjau pendaftaran Anda.</div>";

    } catch (Exception $e) {
        $koneksi->rollback();
        $message = "<div class='alert alert-danger'>Terjadi kesalahan saat mendaftarkan toko Anda: " . $e->getMessage() . "</div>";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Toko Baru - Warung Kuncen</title>
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
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
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
    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8 mx-auto">
                <h1 class="text-center mb-4">Daftarkan Toko Anda</h1>
                <p class="lead text-center text-muted">Isi formulir di bawah ini untuk mendaftarkan toko dan menu Anda di Warung Kuncen.</p>

                <?php echo $message; ?>

                <div class="card p-4 shadow-sm">
                    <form action="daftar_toko.php" method="POST" enctype="multipart/form-data">
                        <h4 class="mb-3">Informasi Toko</h4>
                        <div class="mb-3">
                            <label for="nama_toko" class="form-label">Nama Toko</label>
                            <input type="text" class="form-control" id="nama_toko" name="nama_toko" required>
                        </div>
                        <div class="mb-3">
                            <label for="deskripsi_toko" class="form-label">Deskripsi Toko</label>
                            <textarea class="form-control" id="deskripsi_toko" name="deskripsi_toko" rows="3" required></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="lokasi_toko" class="form-label">Lokasi Toko</label>
                            <input type="text" class="form-control" id="lokasi_toko" name="lokasi_toko" required>
                        </div>
                        <div class="mb-3">
                            <label for="gambar_toko" class="form-label">Gambar Toko (Opsional)</label>
                            <input class="form-control" type="file" id="gambar_toko" name="gambar_toko">
                        </div>

                        <button type="submit" class="btn btn-primary w-100 mt-4">Daftarkan Toko & Menu</button>
                    </form>
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
</body>
</html>