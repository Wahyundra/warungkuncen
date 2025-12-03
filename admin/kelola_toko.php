<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Handle Approve/Reject actions
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    $id_toko = (int)$_POST['id_toko'];
    $action = $_POST['action']; // 'approve', 'reject', or 'delete'

    if ($action == 'approve') {
        $sql_update = "UPDATE toko SET status_toko = 'approved' WHERE id_toko = ?";
    } elseif ($action == 'reject') {
        $sql_update = "UPDATE toko SET status_toko = 'rejected' WHERE id_toko = ?";
    } elseif ($action == 'delete') {
        // Delete associated products first (optional, depending on FK constraint)
        // For now, rely on ON DELETE SET NULL on produk.id_toko
        $sql_update = "DELETE FROM toko WHERE id_toko = ?";
    }

    if (isset($sql_update)) {
        $stmt = $koneksi->prepare($sql_update);
        $stmt->bind_param("i", $id_toko);
        $stmt->execute();
        $stmt->close();
        header("Location: kelola_toko.php?status=updated");
        exit;
    }
}

// Query to fetch all shops (pending, approved, rejected)
$sql = "SELECT * FROM toko ORDER BY status_toko ASC, id_toko DESC";
$result = $koneksi->query($sql);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Toko - Admin</title>
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
            <h1 class="h2 mb-4">Kelola Toko</h1>
            <div class="card">
                <div class="card-body">
                    <?php if (isset($_GET['status']) && $_GET['status'] == 'updated'): ?>
                        <div class="alert alert-success">Status toko berhasil diperbarui.</div>
                    <?php endif; ?>
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID Toko</th>
                                    <th>Nama Toko</th>
                                    <th>Deskripsi</th>
                                    <th>Lokasi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result && $result->num_rows > 0): ?>
                                    <?php while($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo $row['id_toko']; ?></td>
                                            <td><?php echo htmlspecialchars($row['nama_toko']); ?></td>
                                            <td><?php echo htmlspecialchars(substr($row['deskripsi_toko'], 0, 50)) . '...'; ?></td>
                                            <td><?php echo htmlspecialchars($row['lokasi_toko']); ?></td>
                                            <td><span class="badge bg-<?php
                                                if ($row['status_toko'] == 'pending') echo 'warning';
                                                elseif ($row['status_toko'] == 'approved') echo 'success';
                                                else echo 'danger';
                                            ?>"><?php echo ucfirst($row['status_toko']); ?></span></td>
                                            <td>
                                                <?php if ($row['status_toko'] == 'pending'): ?>
                                                    <form method="POST" action="kelola_toko.php" class="d-inline">
                                                        <input type="hidden" name="id_toko" value="<?php echo $row['id_toko']; ?>">
                                                        <button type="submit" name="action" value="approve" class="btn btn-sm btn-success me-1">Setujui</button>
                                                    </form>
                                                    <form method="POST" action="kelola_toko.php" class="d-inline">
                                                        <input type="hidden" name="id_toko" value="<?php echo $row['id_toko']; ?>">
                                                        <button type="submit" name="action" value="reject" class="btn btn-sm btn-warning me-1">Tolak</button>
                                                    </form>
                                                <?php endif; ?>
                                                <form method="POST" action="kelola_toko.php" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus toko ini? Semua produk terkait akan kehilangan tautan toko.');">
                                                    <input type="hidden" name="id_toko" value="<?php echo $row['id_toko']; ?>">
                                                    <button type="submit" name="action" value="delete" class="btn btn-sm btn-danger">Hapus</button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center">Tidak ada toko yang terdaftar.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
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