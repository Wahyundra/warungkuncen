<?php
require_once 'config/koneksi.php';

// Jika user sudah login, redirect ke halaman beranda
if (isset($_SESSION['user_id'])) {
    header("Location: beranda.php");
    exit;
}

$error = '';

// Proses login ketika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = md5($_POST['password']); // Enkripsi password dengan MD5

    // Query untuk mencari user
    $sql = "SELECT id_user, nama_user FROM user WHERE email = ? AND password = ? AND level = 'user'";
    $stmt = $koneksi->prepare($sql);
    $stmt->bind_param("ss", $email, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // Login berhasil
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id_user'];
        $_SESSION['user_nama'] = $user['nama_user'];
        // Redirect ke halaman yang diminta jika ada parameter 'redirect'
        if (isset($_GET['redirect'])) {
            header("Location: " . htmlspecialchars($_GET['redirect']));
            exit;
        } else {
            header("Location: beranda.php");
            exit;
        }
    } else {
        // Login gagal
        $error = "Email atau Password salah!";
    }

    $stmt->close();
    $koneksi->close();
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login User - Warung Kuncen</title>
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
        .login-card {
            width: 100%;
            max-width: 400px;
            padding: 2rem;
        }
    </style>
</head>
<body>

    <div class="card login-card shadow-sm">
        <div class="card-body">
            <h3 class="card-title text-center mb-4">Login Pengguna</h3>
            
            <?php if(isset($_GET['status']) && $_GET['status'] == 'email_updated'): ?>
                <div class="alert alert-success" role="alert">
                    Profil berhasil diperbarui. Silakan login kembali dengan email baru Anda.
                </div>
            <?php endif; ?>
            <?php if($error): ?>
                <div class="alert alert-danger" role="alert">
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <form action="login.php" method="POST">
                <div class="mb-3">
                    <label for="email" class="form-label">Alamat Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label">Password</label>
                    <input type="password" class="form-control" id="password" name="password" required>
                </div>
                <div class="d-grid">
                    <button type="submit" class="btn btn-primary">Login</button>
                </div>
            </form>
            <div class="text-center mt-3">
                Belum punya akun? <a href="register.php">Daftar Sekarang</a>
            </div>
            <div class="text-center mt-3">
                <a href="beranda.php">Kembali ke Halaman Utama</a>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>