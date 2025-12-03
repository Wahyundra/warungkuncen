<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id_pesanan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pesanan <= 0) {
    header("Location: kelola_pesanan.php");
    exit;
}

// Ambil data pesanan utama dan data user
$sql_pesanan = "SELECT p.*, u.nama_user, u.email, u.alamat 
                FROM pesanan p 
                JOIN user u ON p.id_user = u.id_user 
                WHERE p.id_pesanan = ?";
$stmt_pesanan = $koneksi->prepare($sql_pesanan);
$stmt_pesanan->bind_param("i", $id_pesanan);
$stmt_pesanan->execute();
$result_pesanan = $stmt_pesanan->get_result();
if ($result_pesanan->num_rows === 0) {
    echo "Pesanan tidak ditemukan.";
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

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Pesanan #<?php echo $id_pesanan; ?> - Admin</title>
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
        .product-img-sm {
            width: 60px;
            height: 60px;
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
            <li class="nav-item"><a href="index.php" class="nav-link"><i class="bi bi-speedometer2"></i> Dashboard</a></li>
            <!-- <li><a href="kelola_produk.php" class="nav-link"><i class="bi bi-box-seam"></i> Kelola Produk</a></li> -->
            <li><a href="kelola_pesanan.php" class="nav-link active"><i class="bi bi-receipt"></i> Kelola Pesanan</a></li>
            <li><a href="kelola_pesan.php" class="nav-link"><i class="bi bi-envelope"></i> Kelola Pesan</a></li>
            <li><a href="kelola_toko.php" class="nav-link"><i class="bi bi-shop"></i> Kelola Toko</a></li>
        </ul>
        <hr>
        <div class="dropdown">
            <a href="#" class="d-flex align-items-center text-white text-decoration-none dropdown-toggle" data-bs-toggle="dropdown">
                <i class="bi bi-person-circle me-2"></i><strong><?php echo htmlspecialchars($_SESSION['admin_nama']); ?></strong>
            </a>
            <ul class="dropdown-menu dropdown-menu-dark text-small shadow"><li><a class="dropdown-item" href="logout.php">Sign out</a></li></ul>
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
            <h1 class="h2">Detail Pesanan #<?php echo $id_pesanan; ?></h1>
            <a href="kelola_pesanan.php" class="btn btn-secondary">Kembali ke Daftar Pesanan</a>
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
                <?php if (!empty($items_by_shop)):
                    foreach ($items_by_shop as $nama_toko => $items):
                        $subtotal_toko = 0;
                ?>
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
                                    <?php foreach ($items as $item) {
                                        $subtotal_toko += $item['subtotal'];
                                    ?>
                                    <tr>
                                        <td><img src="../assets/img/<?php echo htmlspecialchars($item['gambar']); ?>" class="product-img-sm"></td>
                                        <td><?php echo htmlspecialchars($item['nama_produk']); ?></td>
                                        <td><?php echo $item['jumlah']; ?></td>
                                        <td>Rp <?php echo number_format($item['subtotal'], 0, ',', '.'); ?></td>
                                    </tr>
                                    <?php } ?>
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
$stmt_detail->close();
$koneksi->close();
?>
