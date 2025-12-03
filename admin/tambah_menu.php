<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id_toko = isset($_GET['id_toko']) ? (int)$_GET['id_toko'] : 0;
if ($id_toko <= 0) {
    header("Location: kelola_toko.php"); // Redirect if no valid shop ID
    exit;
}

// Fetch shop details to display
$sql_toko = "SELECT nama_toko FROM toko WHERE id_toko = ?";
$stmt_toko = $koneksi->prepare($sql_toko);
$stmt_toko->bind_param("i", $id_toko);
$stmt_toko->execute();
$result_toko = $stmt_toko->get_result();
$toko = $result_toko->fetch_assoc();
$stmt_toko->close();

if (!$toko) {
    echo "Toko tidak ditemukan.";
    exit;
}

$error = '';
$success = '';

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_produk = $_POST['nama_produk'];
    $deskripsi = $_POST['deskripsi'];
    $harga = $_POST['harga'];
    $stok = $_POST['stok'];
    
    // Proses upload gambar
    $gambar = 'default.jpg'; // Default image if none uploaded or error
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $target_dir = "../assets/img/";
        $gambar = basename($_FILES["gambar"]["name"]);
        $target_file = $target_dir . $gambar;
        
        // Pindahkan file yang diupload ke direktori tujuan
        if (!move_uploaded_file($_FILES["gambar"]["tmp_name"], $target_file)) {
            $error = "Maaf, terjadi kesalahan saat mengupload file.";
        }
    }

    if (empty($error)) {
        // Simpan data ke database
        $sql = "INSERT INTO produk (nama_produk, deskripsi, harga, stok, gambar, id_toko) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $koneksi->prepare($sql);
        $stmt->bind_param("ssdisi", $nama_produk, $deskripsi, $harga, $stok, $gambar, $id_toko);
        
        if ($stmt->execute()) {
            header("Location: kelola_toko.php?status=sukses_tambah_menu");
            exit;
        } else {
            $error = "Gagal menyimpan produk ke database.";
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
    <title>Tambah Menu untuk <?php echo htmlspecialchars($toko['nama_toko']); ?> - Admin</title>
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
            <li><a href="kelola_pesanan.php" class="nav-link"><i class="bi bi-receipt"></i> Kelola Pesanan</a></li>
            <li><a href="kelola_pesan.php" class="nav-link"><i class="bi bi-envelope"></i> Kelola Pesan</a></li>
            <li class="nav-item"><a href="kelola_toko.php" class="nav-link active"><i class="bi bi-shop"></i> Kelola Toko</a></li>
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
        <h1 class="h2 mb-4">Tambah Menu untuk <?php echo htmlspecialchars($toko['nama_toko']); ?></h1>
        <div class="card">
            <div class="card-body">
                <?php if($error): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <form action="tambah_menu.php?id_toko=<?php echo $id_toko; ?>" method="POST" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label for="nama_produk" class="form-label">Nama Menu</label>
                        <input type="text" class="form-control" id="nama_produk" name="nama_produk" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi" class="form-label">Deskripsi Menu</label>
                        <textarea class="form-control" id="deskripsi" name="deskripsi" rows="3" required></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="harga" class="form-label">Harga</label>
                        <input type="number" class="form-control" id="harga" name="harga" step="500" required>
                    </div>
                    <div class="mb-3">
                        <label for="stok" class="form-label">Stok</label>
                        <input type="number" class="form-control" id="stok" name="stok" required>
                    </div>
                    <div class="mb-3">
                        <label for="gambar" class="form-label">Gambar Menu (Opsional)</label>
                        <input class="form-control" type="file" id="gambar" name="gambar">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Menu</button>
                    <a href="kelola_toko.php" class="btn btn-secondary">Batal</a>
                </form>
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