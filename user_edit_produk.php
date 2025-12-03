<?php
require_once 'config/koneksi.php';

// Cek jika user belum login, redirect ke halaman login
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$id_produk = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_produk <= 0) {
    header("Location: profile.php"); // Redirect if no valid product ID
    exit;
}

// Ambil data produk yang akan diedit dan pastikan milik toko user yang login
$sql = "SELECT p.*, t.id_user FROM produk p JOIN toko t ON p.id_toko = t.id_toko WHERE p.id_produk = ? AND t.id_user = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("ii", $id_produk, $user_id);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Produk tidak ditemukan atau Anda tidak memiliki akses untuk mengedit produk ini.";
    exit;
}
$produk = $result->fetch_assoc();
$stmt->close();

$error = '';

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    $gambar_lama = $_POST['gambar_lama'];

    $gambar = $gambar_lama;
    // Cek jika ada gambar baru yang diupload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "assets/img/";
        $gambar = basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar;
        
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $error = "Maaf, terjadi kesalahan saat mengupload file baru.";
        }
    }

    if (empty($error)) {
        $sql_update = "UPDATE produk SET nama_produk = ?, deskripsi = ?, harga = ?, stok = ?, gambar = ? WHERE id_produk = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("ssdisi", $nama_produk, $deskripsi, $harga, $stok, $gambar, $id_produk);
        
        if ($stmt_update->execute()) {
            header("Location: user_edit_menu_toko.php?id_toko=" . $produk['id_toko'] . "&status=sukses_edit");
            exit;
        } else {
            $error = "Gagal memperbarui produk.";
        }
        $stmt_update->close();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Produk: <?php echo htmlspecialchars($produk['nama_produk']); ?> - Warung Kuncen</title>
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
        <h1 class="h2 mb-4">Edit Produk</h1>
        <div class="card">
            <div class="card-body">
                <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <form action="user_edit_produk.php?id=<?php echo $id_produk; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($produk['gambar']); ?>">
                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">Nama Produk</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" value="<?php echo htmlspecialchars($produk['nama_produk']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required><?php echo htmlspecialchars($produk['deskripsi']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" step="500" value="<?php echo htmlspecialchars($produk['harga']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" value="<?php echo htmlspecialchars($produk['stok']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Saat Ini</label>
                        <div>
                            <img src="assets/img/<?php echo htmlspecialchars($produk['gambar']); ?>" alt="" style="width: 150px; height: auto;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Upload Gambar Baru (Opsional)</label>
                        <input class="form-control" type="file" id="gambar" name="gambar">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    <a href="user_edit_menu_toko.php?id_toko=<?php echo $produk['id_toko']; ?>" class="btn btn-secondary">Batal</a>
                </form>
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