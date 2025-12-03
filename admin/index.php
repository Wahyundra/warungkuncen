<?php
require_once '../config/koneksi.php';

// Cek jika admin tidak login, redirect ke halaman login
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.php");
    exit;
}

// Di sini Anda bisa menambahkan query untuk mengambil data ringkasan
// Contoh: Menghitung jumlah produk
$total_produk_result = $koneksi->query("SELECT COUNT(*) as total FROM produk");
$total_produk = $total_produk_result->fetch_assoc()['total'];

// Contoh: Menghitung jumlah pesanan baru (status 'menunggu')
$pesanan_baru_result = $koneksi->query("SELECT COUNT(*) as total FROM pesanan WHERE status_pesanan = 'menunggu'");
$pesanan_baru = $pesanan_baru_result->fetch_assoc()['total'];

// Menghitung total pendapatan dari pesanan yang sudah selesai
$pendapatan_result = $koneksi->query("SELECT SUM(total_harga) as total FROM pesanan WHERE status_pesanan = 'selesai'");
    $total_pendapatan = $pendapatan_result->fetch_assoc()['total'] ?? 0;

// Query untuk mengambil distribusi status pesanan
$sql_status_dist = "SELECT status_pesanan, COUNT(*) as count FROM pesanan GROUP BY status_pesanan";
$result_status_dist = $koneksi->query($sql_status_dist);

$order_status_labels = [];
$order_status_data = [];
while ($row = $result_status_dist->fetch_assoc()) {
    $order_status_labels[] = ucfirst($row['status_pesanan']);
    $order_status_data[] = $row['count'];
}

?><!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Warung Kuncen</title>
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
            <li class="nav-item">
                <a href="index.php" class="nav-link active">
                    <i class="bi bi-speedometer2"></i> Dashboard
                </a>
            </li>
            <!-- <li>
                <a href="kelola_produk.php" class="nav-link">
                    <i class="bi bi-box-seam"></i> Kelola Produk
                </a>
            </li> -->
            <li>
                <a href="kelola_pesanan.php" class="nav-link"><i class="bi bi-receipt"></i> Kelola Pesanan</a>
            </li>
            <li>
                <a href="kelola_pesan.php" class="nav-link"><i class="bi bi-envelope"></i> Kelola Pesan</a>
            </li>
            <li>
                <a href="kelola_toko.php" class="nav-link"><i class="bi bi-shop"></i> Kelola Toko</a>
            </li>
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
        <h1 class="mb-4">Dashboard</h1>
        <div class="row">
            <!-- Card Pesanan Baru -->
            <div class="col-md-4">
                <div class="card text-white bg-primary mb-3">
                    <div class="card-header">Pesanan Baru</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $pesanan_baru; ?></h5>
                        <p class="card-text">Pesanan yang perlu diproses.</p>
                    </div>
                </div>
            </div>
            <!-- Card Total Produk -->
            <div class="col-md-4">
                <div class="card text-white bg-success mb-3">
                    <div class="card-header">Total Produk</div>
                    <div class="card-body">
                        <h5 class="card-title"><?php echo $total_produk; ?></h5>
                        <p class="card-text">Jumlah jenis produk yang dijual.</p>
                    </div>
                </div>
            </div>
            <!-- Card Total Pendapatan -->
            <div class="col-md-4">
                <div class="card text-white bg-info mb-3">
                    <div class="card-header">Total Pendapatan</div>
                    <div class="card-body">
                        <h5 class="card-title">Rp <?php echo number_format($total_pendapatan, 0, ',', '.'); ?></h5>
                        <p class="card-text">Pendapatan dari pesanan selesai.</p>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="mt-5">
            <h2>Selamat Datang, <?php echo htmlspecialchars($_SESSION['admin_nama']); ?>!</h2>
            <p>Gunakan menu di samping untuk mengelola konten website Anda.</p>

            <div class="card mt-4">
                <div class="card-header">Distribusi Status Pesanan</div>
                <div class="card-body">
                    <canvas id="orderStatusChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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

            // Chart.js for Order Status Distribution
            const ctx = document.getElementById('orderStatusChart').getContext('2d');
            const orderStatusChart = new Chart(ctx, {
                type: 'bar',
                data: {
                    labels: <?php echo json_encode($order_status_labels); ?>,
                    datasets: [{
                        label: 'Jumlah Pesanan',
                        data: <?php echo json_encode($order_status_data); ?>,
                        backgroundColor: [
                            'rgba(255, 99, 132, 0.8)', // Merah untuk menunggu
                            'rgba(54, 162, 235, 0.8)', // Biru untuk diproses
                            'rgba(75, 192, 192, 0.8)', // Hijau untuk selesai
                            'rgba(255, 206, 86, 0.8)'  // Kuning untuk dibatalkan
                        ],
                        borderColor: [
                            'rgba(255, 99, 132, 1)',
                            'rgba(54, 162, 235, 1)',
                            'rgba(75, 192, 192, 1)',
                            'rgba(255, 206, 86, 1)'
                        ],
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: {
                            display: false // Hide legend for bar chart if labels are on x-axis
                        },
                        title: {
                            display: true,
                            text: 'Distribusi Status Pesanan'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            title: {
                                display: true,
                                text: 'Jumlah Pesanan'
                            }
                        },
                        x: {
                            title: {
                                display: true,
                                text: 'Status Pesanan'
                            }
                        }
                    }
                },
            });
        });
    </script>
</body>
</html>
