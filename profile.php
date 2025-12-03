<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$error = '';
$success = '';

// Ambil data user
$sql_user = "SELECT nama_user, email, alamat FROM user WHERE id_user = ?";
$stmt_user = $koneksi->prepare($sql_user);
$stmt_user->bind_param("i", $user_id);
$stmt_user->execute();
$result_user = $stmt_user->get_result();
$user = $result_user->fetch_assoc();
$stmt_user->close();

// Ambil data toko user jika ada
$user_shop = null;
$sql_get_shop = "SELECT id_toko, nama_toko, status_toko FROM toko WHERE id_user = ?";
$stmt_get_shop = $koneksi->prepare($sql_get_shop);
$stmt_get_shop->bind_param("i", $user_id);
$stmt_get_shop->execute();
$result_get_shop = $stmt_get_shop->get_result();
if ($result_get_shop->num_rows > 0) {
    $user_shop = $result_get_shop->fetch_assoc();
}
$stmt_get_shop->close();

// Ambil pesanan untuk toko user jika toko sudah disetujui
$shop_orders = [];
if ($user_shop && $user_shop['status_toko'] == 'approved') {
    // Query untuk mengambil pesanan yang berisi produk dari toko user
    // Ini akan menjadi query yang kompleks karena harus join beberapa tabel
    // pesanan -> detail_pesanan -> produk -> toko
    $sql_shop_orders = "SELECT DISTINCT p.id_pesanan, u.nama_user, p.tanggal_pesan, p.total_harga, p.status_pesanan 
                        FROM pesanan p
                        JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
                        JOIN produk pr ON dp.id_produk = pr.id_produk
                        JOIN user u ON p.id_user = u.id_user
                        WHERE pr.id_toko = ?
                        ORDER BY p.tanggal_pesan DESC";
    $stmt_shop_orders = $koneksi->prepare($sql_shop_orders);
    $stmt_shop_orders->bind_param("i", $user_shop['id_toko']);
    $stmt_shop_orders->execute();
    $result_shop_orders = $stmt_shop_orders->get_result();
    while ($row = $result_shop_orders->fetch_assoc()) {
        $shop_orders[] = $row;
    }
    $stmt_shop_orders->close();
}

// Logika untuk update status pesanan dari toko user
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_shop_order_status'])) {
    $id_pesanan_update = $_POST['id_pesanan'];
    $status_pesanan_update = $_POST['status_pesanan'];

    // Pastikan pesanan ini memang terkait dengan toko user yang login
    $sql_check_order_ownership = "SELECT p.id_pesanan 
                                  FROM pesanan p
                                  JOIN detail_pesanan dp ON p.id_pesanan = dp.id_pesanan
                                  JOIN produk pr ON dp.id_produk = pr.id_produk
                                  WHERE p.id_pesanan = ? AND pr.id_toko = ? LIMIT 1";
    $stmt_check_order_ownership = $koneksi->prepare($sql_check_order_ownership);
    $stmt_check_order_ownership->bind_param('ii', $id_pesanan_update, $user_shop['id_toko']);
    $stmt_check_order_ownership->execute();
    $result_check_order_ownership = $stmt_check_order_ownership->get_result();

    if ($result_check_order_ownership->num_rows > 0) {
        $update_sql = "UPDATE pesanan SET status_pesanan = ? WHERE id_pesanan = ?";
        $stmt_update = $koneksi->prepare($update_sql);
        $stmt_update->bind_param('si', $status_pesanan_update, $id_pesanan_update);
        $stmt_update->execute();
        $stmt_update->close();
        header("Location: profile.php?status=order_updated");
        exit;
    } else {
        $error = "Anda tidak memiliki izin untuk mengubah status pesanan ini.";
    }
    $stmt_check_order_ownership->close();
}

// Ambil riwayat pesanan user
$pesanan_user = [];
$sql_pesanan = "SELECT id_pesanan, tanggal_pesan, total_harga, status_pesanan 
                FROM pesanan 
                WHERE id_user = ? 
                ORDER BY tanggal_pesan DESC";
$stmt_pesanan = $koneksi->prepare($sql_pesanan);
$stmt_pesanan->bind_param("i", $user_id);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
while ($row = $result_pesanan->fetch_assoc()) {
    $pesanan_user[] = $row;
}
$stmt_pesanan->close();

// Proses update profile jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_user = htmlspecialchars($_POST['nama_user']);
    $email = htmlspecialchars($_POST['email']);
    $alamat = htmlspecialchars($_POST['alamat']);
    $password = $_POST['password']; // Password baru (opsional)

    // Cek apakah email sudah digunakan oleh user lain
    $sql_check_email = "SELECT id_user FROM user WHERE email = ? AND id_user != ?";
    $stmt_check_email = $koneksi->prepare($sql_check_email);
    $stmt_check_email->bind_param("si", $email, $user_id);
    $stmt_check_email->execute();
    $result_check_email = $stmt_check_email->get_result();

    if ($result_check_email->num_rows > 0) {
        $error = "Email sudah digunakan oleh pengguna lain.";
    } else {
        $sql_update = "UPDATE user SET nama_user = ?, email = ?, alamat = ? WHERE id_user = ?";
        $params = "sssi";
        $values = [&$nama_user, &$email, &$alamat, &$user_id];

        if (!empty($password)) {
            $password_md5 = md5($password);
            $sql_update = "UPDATE user SET nama_user = ?, email = ?, alamat = ?, password = ? WHERE id_user = ?";
            $params = "ssssi";
            $values = [&$nama_user, &$email, &$alamat, &$password_md5, &$user_id];
        }

        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param($params, ...$values);
        
        if ($stmt_update->execute()) {
            $success = "Profil berhasil diperbarui. Silakan login kembali dengan email baru Anda.";
            // Force logout after email change to ensure re-authentication with new email
            session_destroy();
            header("Location: login.php?status=email_updated");
            exit;
        } else {
            $error = "Gagal memperbarui profil: " . $koneksi->error;
        }
        $stmt_update->close();
    }
    $stmt_check_email->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Pengguna - Warung Kuncen</title>
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
        <h1 class="text-center mb-4">Profil Pengguna</h1>
        <div class="row">
            <div class="col-lg-6">
                <div class="card p-4 shadow-sm">
                    <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <?php if($success): ?><div class="alert alert-success"><?php echo $success; ?></div><?php endif; ?>

                    <form action="profile.php" method="POST">
                        <div class="mb-3">
                            <label for="nama_user" class="form-label">Nama Lengkap</label>
                            <input type="text" class="form-control" id="nama_user" name="nama_user" value="<?php echo htmlspecialchars($user['nama_user']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="email" class="form-label">Alamat Email</label>
                            <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                        </div>
                        <div class="mb-3">
                            <label for="alamat" class="form-label">Alamat</label>
                            <textarea class="form-control" id="alamat" name="alamat" rows="3" required><?php echo htmlspecialchars($user['alamat']); ?></textarea>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Password Baru (kosongkan jika tidak ingin mengubah)</label>
                            <input type="password" class="form-control" id="password" name="password">
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        </div>
                    </form>
                </div>
            </div>
            <div class="col-lg-6">
                <h2 class="text-center mb-4">Riwayat Pesanan Saya</h2>
                <div class="card p-4 shadow-sm">
                    <?php if (!empty($pesanan_user)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-bordered">
                                <thead>
                                    <tr>
                                        <th>ID Pesanan</th>
                                        <th>Tanggal</th>
                                        <th>Total Harga</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pesanan_user as $p): ?>
                                        <tr>
                                            <td>#<?php echo $p['id_pesanan']; ?></td>
                                            <td><?php echo date('d M Y, H:i', strtotime($p['tanggal_pesan'])); ?></td>
                                            <td>Rp <?php echo number_format($p['total_harga'], 0, ',', '.'); ?></td>
                                            <td><span class="badge bg-info"><?php echo ucfirst($p['status_pesanan']); ?></span></td>
                                            <td><a href="transaksi_detail.php?id=<?php echo $p['id_pesanan']; ?>" class="btn btn-sm btn-info">Lihat Detail</a></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-center">Anda belum memiliki riwayat pesanan.</p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php if (!$user_shop): ?>
        <div class="row mt-4">
            <div class="col-lg-12 text-center">
                <p class="lead">Belum punya toko? Mau buat toko? <a href="daftar_toko.php" class="text-primary fw-bold">klik disini</a></p>
            </div>
        </div>
        <?php else: ?>
        <div class="row mt-4">
            <div class="col-lg-12">
                <h2 class="text-center mb-4">Kelola Toko Anda</h2>
                <div class="card p-4 shadow-sm">
                    <?php if ($user_shop['status_toko'] == 'pending'): ?>
                        <div class="alert alert-info text-center">
                            Toko Anda (<strong><?php echo htmlspecialchars($user_shop['nama_toko']); ?></strong>) sedang menunggu konfirmasi dari admin.
                        </div>
                    <?php elseif ($user_shop['status_toko'] == 'approved'): ?>
                        <div class="alert alert-success text-center">
                            Toko Anda (<strong><?php echo htmlspecialchars($user_shop['nama_toko']); ?></strong>) telah dikonfirmasi!
                        </div>
                        <div class="d-grid gap-2">
                            <a href="user_edit_toko.php?id_toko=<?php echo $user_shop['id_toko']; ?>" class="btn btn-primary">Edit Informasi Toko</a>
                            <a href="user_tambah_menu.php?id_toko=<?php echo $user_shop['id_toko']; ?>" class="btn btn-success">Tambah Menu Baru</a>
                            <a href="user_edit_menu_toko.php?id_toko=<?php echo $user_shop['id_toko']; ?>" class="btn btn-info">Edit/Hapus Menu</a>
                        </div>
                    <?php elseif ($user_shop['status_toko'] == 'rejected'): ?>
                        <div class="alert alert-danger text-center">
                            Pendaftaran toko Anda (<strong><?php echo htmlspecialchars($user_shop['nama_toko']); ?></strong>) ditolak oleh admin. Silakan hubungi admin untuk informasi lebih lanjut.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <?php if ($user_shop && $user_shop['status_toko'] == 'approved'): ?>
        <div class="row mt-5">
            <div class="col-lg-12">
                <h2 class="text-center mb-4">Kelola Pesanan Toko Anda</h2>
                <div class="card p-4 shadow-sm">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'order_updated'): ?>
                        <div class="alert alert-success">Status pesanan berhasil diperbarui.</div>
                    <?php endif; ?>
                    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Pesanan</th>
                                    <th>Nama Pembeli</th>
                                    <th>Tanggal</th>
                                    <th>Total Harga</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($shop_orders)): ?>
                                    <?php 
                                    $status_options = ['menunggu', 'diproses', 'selesai', 'dibatalkan'];
                                    foreach ($shop_orders as $order): ?>
                                        <tr>
                                            <td>#<?php echo $order['id_pesanan']; ?></td>
                                            <td><?php echo htmlspecialchars($order['nama_user']); ?></td>
                                            <td><?php echo date('d M Y, H:i', strtotime($order['tanggal_pesan'])); ?></td>
                                            <td>Rp <?php echo number_format($order['total_harga'], 0, ',', '.'); ?></td>
                                            <td>
                                                <form method="POST" action="profile.php" class="d-flex">
                                                    <input type="hidden" name="id_pesanan" value="<?php echo $order['id_pesanan']; ?>">
                                                    <select name="status_pesanan" class="form-select form-select-sm me-2">
                                                        <?php foreach ($status_options as $status): ?>
                                                            <option value="<?php echo $status; ?>" <?php echo ($order['status_pesanan'] == $status) ? 'selected' : ''; ?>><?php echo ucfirst($status); ?></option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                    <button type="submit" name="update_shop_order_status" class="btn btn-sm btn-outline-primary">Update</button>
                                                </form>
                                            </td>
                                            <td>
                                                <a href="transaksi_detail.php?id=<?php echo $order['id_pesanan']; ?>" class="btn btn-sm btn-info">Lihat Detail</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada pesanan untuk toko Anda.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>
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