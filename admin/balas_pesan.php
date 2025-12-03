<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

$id_pesan = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id_pesan <= 0) {
    header("Location: kelola_pesan.php");
    exit;
}

// Update status pesan menjadi 'dibaca' jika statusnya 'baru'
$koneksi->query("UPDATE pesan_kontak SET status = 'dibaca' WHERE id_pesan = $id_pesan AND status = 'baru'");

// Ambil data pesan
$sql = "SELECT * FROM pesan_kontak WHERE id_pesan = ?";
$stmt = $koneksi->prepare($sql);
$stmt->bind_param("i", $id_pesan);
$stmt->execute();
$result = $stmt->get_result();
if ($result->num_rows === 0) {
    echo "Pesan tidak ditemukan.";
    exit;
}
$pesan = $result->fetch_assoc();
$stmt->close();

// Proses form balasan jika disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['kirim_balasan'])) {
    $balasan = $_POST['balasan'];
    $email_tujuan = $pesan['email_pengirim'];
    $subjek_balasan = "Re: " . $pesan['subjek_pesan'];

    // Kirim email (dinonaktifkan untuk lingkungan lokal)
    // Untuk mengaktifkan, konfigurasikan SMTP di php.ini
    $headers = "From: admin@warkun.com"; // Ganti dengan email admin Anda
    // if (mail($email_tujuan, $subjek_balasan, $balasan, $headers)) {
    
    // Simulasi pengiriman email berhasil
    $email_sent_successfully = true; 

    if ($email_sent_successfully) {
        // Update database
        $sql_update = "UPDATE pesan_kontak SET status = 'dibalas', tanggal_balas = NOW(), balasan = ? WHERE id_pesan = ?";
        $stmt_update = $koneksi->prepare($sql_update);
        $stmt_update->bind_param("si", $balasan, $id_pesan);
        $stmt_update->execute();
        $stmt_update->close();
        header("Location: kelola_pesan.php?status=sukses_balas");
        exit;
    } else {
        $error = "Gagal mengirim email balasan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Balas Pesan - Admin</title>
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
            <li><a href="kelola_pesan.php" class="nav-link active"><i class="bi bi-envelope"></i> Kelola Pesan</a></li>
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
            <h1 class="h2 mb-4">Balas Pesan</h1>
            <div class="card">
                <div class="card-header">
                    <strong>Dari:</strong> <?php echo htmlspecialchars($pesan['nama_pengirim']); ?> (<?php echo htmlspecialchars($pesan['email_pengirim']); ?>)<br>
                    <strong>Subjek:</strong> <?php echo htmlspecialchars($pesan['subjek_pesan']); ?>
                </div>
                <div class="card-body">
                    <p><strong>Isi Pesan:</strong></p>
                    <p><?php echo nl2br(htmlspecialchars($pesan['isi_pesan'])); ?></p>
                </div>
            </div>

            <div class="card mt-4">
                <div class="card-header">
                    <strong>Tulis Balasan</strong>
                </div>
                <div class="card-body">
                    <?php if(!empty($error)): ?><div class="alert alert-danger"><?php echo $error; ?></div><?php endif; ?>
                    <form action="balas_pesan.php?id=<?php echo $id_pesan; ?>" method="POST">
                        <div class="mb-3">
                            <label for="balasan" class="form-label">Balasan Anda:</label>
                            <textarea class="form-control" id="balasan" name="balasan" rows="5" required><?php echo htmlspecialchars($pesan['balasan'] ?? ''); ?></textarea>
                        </div>
                        <button type="submit" name="kirim_balasan" class="btn btn-primary">Kirim Balasan</button>
                        <a href="kelola_pesan.php" class="btn btn-secondary">Kembali</a>
                    </form>
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
