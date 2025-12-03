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

// Ambil data toko yang akan diedit
$sql = "SELECT * FROM toko WHERE id_toko = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_toko);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Toko tidak ditemukan.";
    exit;
}
$toko = $result->fetch_assoc();
$stmt->close();

$error = '';

// Proses form jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_toko = $_POST['nama_toko'];
    $deskripsi_toko = $_POST['deskripsi_toko'];
    $lokasi_toko = $_POST['lokasi_toko'];
    $nama_pemilik = $_POST['nama_pemilik'];
    $telepon_pemilik = $_POST['telepon_pemilik'];
    $alamat_pemilik = $_POST['alamat_pemilik'];
    $gambar_lama = $_POST['gambar_lama'];

    $gambar_toko = $gambar_lama;
    // Cek jika ada gambar baru yang diupload
    if (isset($_FILES['gambar_toko']) && $_FILES['gambar_toko']['error'] == 0) {
        $target_dir = "../assets/img/";
        $gambar_toko = basename($_FILES["gambar_toko"]["name"]);
        $target_file = $target_dir . $gambar_toko;
        
        if (!move_uploaded_file($_FILES["gambar_toko"]["tmp_name"], $target_file)) {
            $error = "Maaf, terjadi kesalahan saat mengupload file baru.";
        }
    }

    if (empty($error)) {
        $sql_update = "UPDATE toko SET nama_toko = ?, deskripsi_toko = ?, lokasi_toko = ?, gambar_toko = ?, nama_pemilik = ?, telepon_pemilik = ?, alamat_pemilik = ? WHERE id_toko = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("sssssssi", $nama_toko, $deskripsi_toko, $lokasi_toko, $gambar_toko, $nama_pemilik, $telepon_pemilik, $alamat_pemilik, $id_toko);
        
        if ($stmt_update->execute()) {
            header("Location: kelola_toko.php?status=sukses_edit");
            exit;
        } else {
            $error = "Gagal memperbarui toko.";
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
    <title>Edit Toko - Admin</title>
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
        <h1 class="h2 mb-4">Edit Toko: <?php echo htmlspecialchars($toko['nama_toko']); ?></h1>
        <div class="card">
            <div class="card-body">
                <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                <form action="edit_toko.php?id_toko=<?php echo $id_toko; ?>" method="POST" enctype="multipart/form-data">
                    <input type="hidden" name="gambar_lama" value="<?php echo htmlspecialchars($toko['gambar_toko']); ?>">
                    <div class="mb-3">
                        <label for="nama_toko" class="form-label">Nama Toko</label>
                        <input type="text" class="form-control" id="nama_toko" name="nama_toko" value="<?php echo htmlspecialchars($toko['nama_toko']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="deskripsi_toko" class="form-label">Deskripsi Toko</label>
                        <textarea class="form-control" id="deskripsi_toko" name="deskripsi_toko" rows="3" required><?php echo htmlspecialchars($toko['deskripsi_toko']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label for="lokasi_toko" class="form-label">Lokasi Toko</label>
                        <input type="text" class="form-control" id="lokasi_toko" name="lokasi_toko" value="<?php echo htmlspecialchars($toko['lokasi_toko']); ?>" required>
                    </div>

                    <h4 class="mb-3 mt-4">Informasi Pemilik</h4>
                    <div class="mb-3">
                        <label for="nama_pemilik" class="form-label">Nama Pemilik</label>
                        <input type="text" class="form-control" id="nama_pemilik" name="nama_pemilik" value="<?php echo htmlspecialchars($toko['nama_pemilik']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="telepon_pemilik" class="form-label">Nomor Telepon</label>
                        <input type="text" class="form-control" id="telepon_pemilik" name="telepon_pemilik" value="<?php echo htmlspecialchars($toko['telepon_pemilik']); ?>" required>
                    </div>
                    <div class="mb-3">
                        <label for="alamat_pemilik" class="form-label">Alamat Pemilik</label>
                        <textarea class="form-control" id="alamat_pemilik" name="alamat_pemilik" rows="3" required><?php echo htmlspecialchars($toko['alamat_pemilik']); ?></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Gambar Toko Saat Ini</label>
                        <div>
                            <img src="../assets/img/<?php echo htmlspecialchars($toko['gambar_toko']); ?>" alt="" style="width: 150px; height: auto;">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="gambar_toko" class="form-label">Upload Gambar Baru (Opsional)</label>
                        <input class="form-control" type="file" id="gambar_toko" name="gambar_toko">
                    </div>
                    <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
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