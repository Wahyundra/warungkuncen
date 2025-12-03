<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id_toko = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : 0;
if ($id_toko <= 0) {
    header("Location: profile.php");
    exit;
}

// Ambil data toko dan pastikan milik user yang login
$sql_toko = "SELECT nama_toko FROM toko WHERE id_toko = ? AND id_user = ?";
$stmt_toko = $koneksi->prepare($sql_toko);
$stmt_toko->bind_param("ii", $id_toko, $user_id);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
if ($result_toko->num_rows === 0) {
    echo "Toko tidak ditemukan atau Anda tidak memiliki akses ke toko ini.";
    exit;
}
$toko = $result_toko->fetch_assoc();
$stmt_toko->close();

// Ambil semua produk untuk toko ini
$sql_produk = "SELECT * FROM produk WHERE id_toko = ? ORDER BY nama_produk ASC";
$stmt_produk = $koneksi->prepare($sql_produk);
$stmt_produk->bind_param("i", $id_toko);
$stmt_produk->execute();
$result_produk = $stmt_produk->get_result();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Toko: <?php echo htmlspecialchars($toko['nama_toko']); ?> - Warung Kuncen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="assets/img/warungkuncen.png">
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .navbar-logo {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            object-fit: cover;
        }
        .footer {
            margin-top: auto;
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar (Copy from beranda.php) -->
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

    <div class="container my-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Edit Menu: <?php echo htmlspecialchars($toko['nama_toko']); ?></h1>
            <a href="profile.php" class="btn btn-secondary">Kembali ke Profil</a>
        </div>

        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Gambar</th>
                                <th scope="col">Nama Produk</th>
                                <th scope="col">Harga</th>
                                <th scope="col">Stok</th>
                                <th scope="col">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if ($result_produk && $result_produk->num_rows > 0): ?>
                                <?php while($row = $result_produk->fetch_assoc()): ?>
                                    <tr>
                                        <th scope="row"><?php echo $row['id_produk']; ?></th>
                                        <td><img src="assets/img/<?php echo htmlspecialchars($row['gambar']); ?>" alt="" class="product-img"></td>
                                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $row['stok']; ?></td>
                                        <td>
                                            <a href="user_edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="user_hapus_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada menu untuk toko ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer (Copy from beranda.php) -->
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