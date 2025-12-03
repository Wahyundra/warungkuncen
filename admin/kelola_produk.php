<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Query untuk mengambil semua data produk
$sql = "SELECT * FROM produk ORDER BY id_produk ASC";
$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Produk - Admin Warung Kuncen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <link rel="icon" href="assets/img/warungkuncen.png">
    <style>
        body {
            background-color: #f8f9fa;
            overflow-x: hidden;
        }
        .sidebar {
            width: 250px;
            background-color: #000080; /* Biru Navy */
            color: white;
            position: fixed;
            top: 0;
            left: -250px;
            height: 100%;
            z-index: 1050;
            transition: left 0.3s ease-in-out;
        }
        .sidebar.active {
            left: 0;
        }
        .sidebar .nav-link {
            color: #adb5bd;
        }
        .sidebar .nav-link.active {
            color: #fff;
            background-color: #004C99;
        }
        .sidebar .nav-link:hover {
            color: #fff;
            background-color: #004C99;
        }
        .content {
            flex-grow: 1;
            padding: 2rem;
            margin-left: 0;
            transition: margin-left 0.3s ease-in-out;
        }
        .content-wrapper {
            width: 100%;
        }
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0, 0, 0, 0.5);
            z-index: 1040;
        }
        .sidebar-overlay.active {
            display: block;
        }
        .sidebar-header {
            display: flex;
            justify-content: flex-end;
            padding: 0.5rem 1rem;
        }
        .hamburger-btn {
            background: none;
            border: none;
            color: #212529;
            font-size: 1.5rem;
        }
        .product-img {
            width: 80px;
            height: 80px;
            object-fit: cover;
        }
        @media (min-width: 992px) {
            .sidebar {
                left: 0;
            }
            .content {
                margin-left: 250px;
            }
            .hamburger-btn, .sidebar-header {
                display: none;
            }
        }
    </style>
</head>
<body>
    <div class="sidebar d-flex flex-column p-3" id="sidebar">
        <div class="sidebar-header">
            <button type="button" class="btn-close btn-close-white" id="sidebar-close-btn"></button>
        </div>
        <h4>Admin Panel</h4>
        <hr>
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="index.php" class="nav-link">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <!-- <li>
                <a href="kelola_produk.php" class="nav-link active">
                    <i class="bi bi-box-seam"></i> Kelola Produk
                </a>
            </li> -->
            <li>
                <a href="kelola_pesanan.php" class="nav-link">
                    <i class="bi bi-receipt"></i> Kelola Pesanan
                </a>
            </li>
            <li><a href="kelola_pesan.php" class="nav-link"><i class="bi bi-envelope"></i> Kelola Pesan</a></li>
            <li><a href="kelola_toko.php" class="nav-link"><i class="bi bi-shop"></i> Kelola Toko</a></li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                <i class="bi bi-person-circle me-2"></i>
                <strong><?php echo htmlspecialchars($_SESSION['admin_nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow">
                <li><a class="dropdown-item" href="logout.php">Sign out</a></li>
            </ul>
        </div>
    </div>

    <div class="sidebar-overlay" id="sidebar-overlay"></div>

    <div class="content-wrapper">
        <nav class="navbar navbar-light bg-light d-lg-none">
            <div class="container-fluid">
                <button class="hamburger-btn" id="sidebar-toggle-btn"><i class="bi bi-list"></i></button>
                <span class="navbar-brand mb-0 h1">Admin Panel</span>
            </div>
        </nav>
        <div class="content">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h1 class="h2">Kelola Produk</h1>
            <a href="tambah_produk.php" class="btn btn-primary">Tambah Produk Baru</a>
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
                            <?php if ($result && $result->num_rows > 0): ?>
                                <?php while($row = $result->fetch_assoc()): ?>
                                    <tr>
                                        <th scope="row"><?php echo $row['id_produk']; ?></th>
                                        <td><img src="../assets/img/<?php echo htmlspecialchars($row['gambar']); ?>" alt="" class="product-img"></td>
                                        <td><?php echo htmlspecialchars($row['nama_produk']); ?></td>
                                        <td>Rp <?php echo number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td><?php echo $row['stok']; ?></td>
                                        <td>
                                            <a href="edit_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-warning">Edit</a>
                                            <a href="hapus_produk.php?id=<?php echo $row['id_produk']; ?>" class="btn btn-sm btn-danger" onclick="return confirm('Apakah Anda yakin ingin menghapus produk ini?');">Hapus</a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="text-center">Tidak ada produk.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const sidebar = document.getElementById('sidebar');
            const toggleBtn = document.getElementById('sidebar-toggle-btn');
            const closeBtn = document.getElementById('sidebar-close-btn');
            const overlay = document.getElementById('sidebar-overlay');

            function showSidebar() {
                sidebar.classList.add('active');
                overlay.classList.add('active');
            }

            function hideSidebar() {
                sidebar.classList.remove('active');
                overlay.classList.remove('active');
            }

            if (toggleBtn) {
                toggleBtn.addEventListener('click', showSidebar);
            }

            if (closeBtn) {
                closeBtn.addEventListener('click', hideSidebar);
            }

            if (overlay) {
                overlay.addEventListener('click', hideSidebar);
            }
        });
    </script>
</body>
</html>
<?php
$koneksi->close();
?>
