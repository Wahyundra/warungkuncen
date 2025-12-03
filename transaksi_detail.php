<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pesanan <= 0) {
    header("Location: profile.php"); // Redirect if no valid order ID
    exit;
}

// Ambil data pesanan utama dan pastikan milik user yang login
$sql_pesanan = "SELECT p.*, u.nama_user, u.email, u.alamat 
                FROM pesanan p 
                JOIN user u ON p.id_user = u.id_user 
                WHERE p.id_pesanan = ? AND p.id_user = ?";
$stmt_pesanan = $koneksi->prepare($sql_pesanan);
$stmt_pesanan->bind_param("ii", $id_pesanan, $user_id);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
if ($result_pesanan->num_rows === 0) {
    echo "Pesanan tidak ditemukan atau Anda tidak memiliki akses ke pesanan ini.";
    exit;
}
$pesanan = $result_pesanan->fetch_assoc();
$stmt_pesanan->close();

// Ambil detail item pesanan, termasuk informasi toko
$sql_detail = "SELECT dp.jumlah, dp.subtotal, pr.nama_produk, pr.gambar, t.nama_toko 
               FROM detail_pesanan dp 
               JOIN produk pr ON dp.id_produk = pr.id_produk 
               LEFT JOIN toko t ON pr.id_toko = t.id_toko
               WHERE dp.id_pesanan = ? 
               ORDER BY t.nama_toko ASC";
$stmt_detail = $koneksi->prepare($sql_detail);
$stmt_detail->bind_param("i", $id_pesanan);
$stmt_detail->execute();
$result_detail = $stmt_detail->get_result();

// Kelompokkan item berdasarkan toko
$items_by_shop = [];
if ($result_detail->num_rows > 0) {
    while ($item = $result_detail->fetch_assoc()) {
        $shop_name = $item['nama_toko'] ?: 'Toko Umum/Telah Dihapus';
        $items_by_shop[$shop_name][] = $item;
    }
}
$stmt_detail->close();

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $id_pesanan; ?> - Warung Kuncen</title>
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
        .product-img-sm {
            width: 60px;
            height: 60px;
            object-fit: cover;
        }
    </style>
</head>
<body>
    <!-- Navbar (Copy from beranda.php, but make Profile active) -->
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
            <h1 class="h2">Detail Pesanan #<?php echo $id_pesanan; ?></h1>
            <a href="profile.php" class="btn btn-secondary">Kembali ke Profil</a>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Informasi Pembeli</div>
                    <div class="card-body">
                        <p><strong>Nama:</strong> <?php echo htmlspecialchars($pesanan['nama_user']); ?></p>
                        <p><strong>Email:</strong> <?php echo htmlspecialchars($pesanan['email']); ?></p>
                        <p><strong>Alamat:</strong> <?php echo nl2br(htmlspecialchars($pesanan['alamat'])); ?></p>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card mb-4">
                    <div class="card-header">Informasi Pesanan</div>
                    <div class="card-body">
                        <p><strong>Tanggal Pesan:</strong> <?php echo date('d M Y, H:i', strtotime($pesanan['tanggal_pesan'])); ?></p>
                        <p><strong>Total Harga:</strong> <span class="fw-bold text-danger">Rp <?php echo number_format($pesanan['total_harga'], 0, ',', '.'); ?></span></p>
                        <p><strong>Status:</strong> <span class="badge bg-primary"><?php echo ucfirst($pesanan['status_pesanan']); ?></span></p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">Item yang Dipesan</div>
            <div class="card-body">
                <?php if (!empty($items_by_shop)): ?>
                    <?php foreach ($items_by_shop as $nama_toko => $items): ?>
                        <h5 class="mt-3">Toko: <strong><?php echo htmlspecialchars($nama_toko); ?></strong></h5>
                        <div class="table-responsive">
                            <table class="table table-sm table-bordered">
                                <thead>
                                    <tr>
                                        <th>Gambar</th>
                                        <th>Nama Produk</th>
                                        <th>Jumlah</th>
                                        <th>Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php 
                                    $subtotal_toko = 0;
                                    foreach ($items as $item): 
                                        $subtotal_toko += $item['subtotal'];
                                    ?>
                                        <tr>
                                            <td><img src="assets/img/<?php echo htmlspecialchars($item['gambar']); ?>" class="product-img-sm"></td>
                                            <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                            <td><?php echo $item['jumlah']; ?></td>
                                            <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                                <tfoot>
                                    <tr class="table-light">
                                        <td colspan="3" class="text-end"><strong>Subtotal Toko:</strong></td>
                                        <td class="fw-bold">Rp <?php echo number_format($subtotal_toko, 0, ',', '.'); ?></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <p class="text-center">Tidak ada item dalam pesanan ini.</p>
                <?php endif; ?>
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
                    <p class="text-white-50 small mt-3">Ingin mendaftarkan toko anda di sini? <a href="daftar_toko.php" class="text-white fw-bold">klik disini</a></p>
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