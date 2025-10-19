<?php
require_once 'config/koneksi.php';

// --- LOGIKA KERANJANG BELANJA ---

// Inisialisasi keranjang jika belum ada
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// 1. Menambah produk ke keranjang
if (isset($_GET['id'])) {
    $id_produk = (int)$_GET['id'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        // Jika produk sudah ada, tambah jumlahnya
        $_SESSION['keranjang'][$id_produk]++;
    } else {
        // Jika belum ada, tambahkan ke keranjang dengan jumlah 1
        $_SESSION['keranjang'][$id_produk] = 1;
    }
    // Redirect ke halaman keranjang untuk menghindari duplikasi saat refresh
    header('Location: pesan.php');
    exit;
}

// 2. Menghapus produk dari keranjang
if (isset($_GET['hapus'])) {
    $id_produk = (int)$_GET['hapus'];
    if (isset($_SESSION['keranjang'][$id_produk])) {
        unset($_SESSION['keranjang'][$id_produk]);
    }
    header('Location: pesan.php');
    exit;
}

// --- LOGIKA PROSES CHECKOUT ---
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['checkout'])) {
    $nama_user = $_POST['nama_user'];
    $email = $_POST['email'];
    $alamat = $_POST['alamat'];
    $password = md5(rand()); // Buat password acak untuk user guest

    // Mulai transaksi database
    $koneksi->begin_transaction();

    try {
        // Step 1: Buat user baru untuk guest checkout
        $sql_user = "INSERT INTO user (nama_user, email, password, alamat, level) VALUES (?, ?, ?, ?, 'user')";
        $stmt_user = $koneksi->prepare($sql_user);
        $stmt_user->bind_param('ssss', $nama_user, $email, $password, $alamat);
        $stmt_user->execute();
        $id_user = $koneksi->insert_id;
        $stmt_user->close();

        // Step 2: Buat record di tabel pesanan
        $total_harga = $_POST['total_harga'];
        $sql_pesanan = "INSERT INTO pesanan (id_user, total_harga, status_pesanan) VALUES (?, ?, 'menunggu')";
        $stmt_pesanan = $koneksi->prepare($sql_pesanan);
        $stmt_pesanan->bind_param('id', $id_user, $total_harga);
        $stmt_pesanan->execute();
        $id_pesanan = $koneksi->insert_id;
        $stmt_pesanan->close();

        // Step 3: Pindahkan item dari keranjang ke detail_pesanan
        $sql_detail = "INSERT INTO detail_pesanan (id_pesanan, id_produk, jumlah, subtotal) VALUES (?, ?, ?, ?)";
        $stmt_detail = $koneksi->prepare($sql_detail);
        foreach ($_SESSION['keranjang'] as $id_produk => $jumlah) {
            $harga_produk = $_POST['harga_produk'][$id_produk];
            $subtotal = $harga_produk * $jumlah;
            $stmt_detail->bind_param('iiid', $id_pesanan, $id_produk, $jumlah, $subtotal);
            $stmt_detail->execute();
        }
        $stmt_detail->close();

        // Jika semua query berhasil, commit transaksi
        $koneksi->commit();

        // Kosongkan keranjang dan redirect
        unset($_SESSION['keranjang']);
        header('Location: transaksi.php?status=sukses&id_pesanan=' . $id_pesanan);
        exit;

    } catch (Exception $e) {
        // Jika ada error, rollback transaksi
        $koneksi->rollback();
        $error_checkout = "Terjadi kesalahan saat memproses pesanan Anda. Silakan coba lagi. Error: " . $e->getMessage();
    }
}

// Ambil data produk berdasarkan keranjang
$keranjang_produk = [];
$total_harga = 0;
if (!empty($_SESSION['keranjang'])) {
    $ids = implode(',', array_keys($_SESSION['keranjang']));
    $sql = "SELECT * FROM produk WHERE id_produk IN ($ids)";
    $result = $koneksi->query($sql);
    while ($row = $result->fetch_assoc()) {
        $keranjang_produk[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Keranjang Belanja - Warung Kuncen</title>
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
                    <li class="nav-item"><a class="nav-link" href="index.php">Menu</a></li>
                    <li class="nav-item"><a class="nav-link" href="transaksi.php">Lacak Pesanan</a></li>
                    <li class="nav-item"><a class="nav-link" href="kontak.php">Kontak</a></li>
                </ul>
                <a href="pesan.php" class="btn btn-primary ms-lg-3">
                    <i class="bi bi-cart"></i> Keranjang
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-5">
        <h2 class="mb-4">Keranjang Belanja Anda</h2>
        <?php if (!empty($keranjang_produk)): ?>
            <div class="row">
                <!-- Daftar Belanja -->
                <div class="col-md-8">
                    <div class="card">
                        <div class="card-body">
                            <table class="table">
                                <thead>
                                    <tr><th>Produk</th><th>Harga</th><th>Jumlah</th><th>Subtotal</th><th>Aksi</th></tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($keranjang_produk as $produk):
                                        $jumlah = $_SESSION['keranjang'][$produk['id_produk']];
                                        $subtotal = $produk['harga'] * $jumlah;
                                        $total_harga += $subtotal;
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($produk['nama_produk']); ?></td>
                                        <td>Rp <?php echo number_format($produk['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $jumlah; ?></td>
                                        <td>Rp <?php echo number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td><a href="pesan.php?hapus=<?php echo $produk['id_produk']; ?>" class="btn btn-sm btn-danger">Hapus</a></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                            <hr>
                            <h4 class="text-end">Total: Rp <?php echo number_format($total_harga, 0, ',', '.'); ?></h4>
                        </div>
                    </div>
                </div>
                <!-- Form Checkout -->
                <div class="col-md-4">
                    <div class="card">
                        <div class="card-header"><strong>Isi Data Pemesan</strong></div>
                        <div class="card-body">
                            <?php if (isset($error_checkout)): ?><div class="alert alert-danger"><?php echo $error_checkout; ?></div><?php endif; ?>
                            <form method="POST" action="pesan.php">
                                <input type="hidden" name="total_harga" value="<?php echo $total_harga; ?>">
                                <?php foreach ($keranjang_produk as $produk): ?>
                                    <input type="hidden" name="harga_produk[<?php echo $produk['id_produk']; ?>]" value="<?php echo $produk['harga']; ?>">
                                <?php endforeach; ?>

                                <div class="mb-3">
                                    <label for="nama_user" class="form-label">Nama Lengkap</label>
                                    <input type="text" class="form-control" name="nama_user" required>
                                </div>
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email</label>
                                    <input type="email" class="form-control" name="email" required>
                                </div>
                                <div class="mb-3">
                                    <label for="alamat" class="form-label">Alamat Pengiriman</label>
                                    <textarea class="form-control" name="alamat" rows="3" required></textarea>
                                </div>
                                <div class="d-grid">
                                    <button type="submit" name="checkout" class="btn btn-primary">Konfirmasi Pesanan</button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php else: ?>
            <div class="alert alert-info text-center">
                Keranjang belanja Anda masih kosong. Silakan <a href="index.php">pilih menu</a> terlebih dahulu.
            </div>
        <?php endif; ?>
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
