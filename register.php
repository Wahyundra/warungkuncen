<?php
require_once 'config/koneksi.php';

// Jika user sudah login, redirect ke halaman beranda
if (isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit;
}

$error = '';
$success = '';

// Proses registrasi ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_user = htmlspecialchars($_POST['nama_user']);
    $email = htmlspecialchars($_POST['email']);
    $password = md5($_POST['password']); // Enkripsi password dengan MD5
    $alamat = htmlspecialchars($_POST['alamat']);

    // Cek apakah email sudah terdaftar
    $sql_check = "SELECT id_user FROM user WHERE email = ?";
    $stmt_check = $koneksi->prepare($sql_check);
    $stmt_check->bind_param("s", $email);
    $stmt_check->execute();
    $result_check = $stmt_check->get_result();

    if ($result_check->num_rows > 0) {
        $error = "Email sudah terdaftar. Silakan gunakan email lain atau login.";
    } else {
        // Insert user baru
        $sql_insert = "INSERT INTO user (nama_user, email, password, alamat, level) VALUES (?, ?, ?, ?, 'user')";
        $stmt_insert = $koneksi->prepare($sql_insert);
        $stmt_insert->bind_param("ssss", $nama_user, $email, $password, $alamat);
        
        if ($stmt_insert->execute()) {
            $success = "Registrasi berhasil! Silakan login.";
            // Redirect ke halaman login setelah registrasi berhasil
            header("Location: login.php?status=registered");
            exit;
        } else {
            $error = "Registrasi gagal. Silakan coba lagi.";
        }
        $stmt_insert->close();
    }
    $stmt_check->close();
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi User - Warung Kuncen</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="assets/img/warungkuncen.png">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            height: 100vh;
            background-color: #f8f9fa;
        }
        .register-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
    </style>
</head>
<body>

    <div class="card register-card shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Registrasi Pengguna</h3>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'registered'): ?>
                <div class="alert alert-success" role="alert">
                    Registrasi berhasil! Silakan login.
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>
            <?php if($success): ?>
                <div class="alert alert-success" role="alert">
                    <?php echo $success; ?>
                </div>
            <?php endif; ?>

            <form action="register.php" method="POST">
                <div class="mb-3">
                    <label for="nama_user" class="form-label">Nama Lengkap</label>
                    <input type="text" class="form-control" id="nama_user" name="nama_user" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="mb-3">
                    <label for="alamat" class="form-label">Alamat</label>
                    <textarea class="form-control" id="alamat" name="alamat" rows="3" required></textarea>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Daftar</button>
                </div>
            </form>
            <div class="text-center mt-3">
                Sudah punya akun? <a href="login.php">Login Sekarang</a>
            </div>
            <div class="text-center mt-3">
                <a href="beranda.php">Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>