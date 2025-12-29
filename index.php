<?php
session_start();

// [PERBAIKAN ADA DISINI]
// Mengarahkan ke folder 'classes'
require_once 'classes/classes.php'; 

$db = (new Database())->getConnection();
$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $role = $_POST['role'];
    $user = $_POST['username'];
    $pass = $_POST['password'];

    if ($role == 'mahasiswa') {
        $mhs = new Mahasiswa($db);
        $data = $mhs->login($user, $pass);
        if ($data) {
            $_SESSION['user_id'] = $data['id_mahasiswa'];
            $_SESSION['role'] = 'mahasiswa';
            $_SESSION['nama'] = $data['nama_mahasiswa'];
            header("Location: dashboard_mhs.php"); exit;
        } else { $error = "NIM atau Password salah!"; }
    } elseif ($role == 'petugas') {
        $petugas = new Petugas($db);
        $data = $petugas->login($user, $pass);
        if ($data) {
            $_SESSION['user_id'] = $data['id_petugas'];
            $_SESSION['role'] = 'petugas';
            $_SESSION['nama'] = $data['nama_petugas'];
            header("Location: dashboard_petugas.php"); exit;
        } else { $error = "Login Petugas Gagal!"; }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;600&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: url('https://images.unsplash.com/photo-1507842217121-9e93c8aaf27c?q=80&w=1920&auto=format&fit=crop') no-repeat center center fixed;
            background-size: cover;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .overlay { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.6); z-index: 1; }
        .login-card { z-index: 2; width: 100%; max-width: 400px; border-radius: 15px; background: rgba(255, 255, 255, 0.95); overflow: hidden; }
        .card-header { background: transparent; border-bottom: none; padding-top: 30px; text-align: center; }
        .logo-icon { font-size: 3rem; color: #0d6efd; background: #e7f1ff; width: 80px; height: 80px; line-height: 80px; border-radius: 50%; display: inline-block; margin-bottom: 10px; }
        .form-control { height: 45px; padding-left: 15px; border-radius: 8px; }
        .input-group-text { background: #f8f9fa; border-top-left-radius: 8px; border-bottom-left-radius: 8px; }
        .btn-login { height: 45px; border-radius: 8px; font-weight: 600; letter-spacing: 0.5px; transition: all 0.3s; }
        .btn-login:hover { transform: translateY(-2px); box-shadow: 0 5px 15px rgba(13, 110, 253, 0.3); }
    </style>
</head>
<body>

    <div class="overlay"></div>

    <div class="card login-card shadow">
        <div class="card-header">
            <div class="logo-icon">
                <i class="bi bi-book-half"></i>
            </div>
            <h4 class="fw-bold text-dark">E-Perpustakaan</h4>
            <p class="text-muted small">Silakan login untuk melanjutkan</p>
        </div>
        
        <div class="card-body p-4">
            <?php if($error): ?>
                <div class="alert alert-danger d-flex align-items-center" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>
                    <div><?= $error ?></div>
                </div>
            <?php endif; ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">LOGIN SEBAGAI</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge"></i></span>
                        <select name="role" class="form-select">
                            <option value="mahasiswa">Mahasiswa</option>
                            <option value="petugas">Petugas Perpustakaan</option>
                        </select>
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-secondary">USERNAME / NIM</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person"></i></span>
                        <input type="text" name="username" class="form-control" placeholder="Masukkan NIM atau Username" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-secondary">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock"></i></span>
                        <input type="password" name="password" class="form-control" placeholder="Masukkan Password" required>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-login">
                        MASUK SEKARANG <i class="bi bi-box-arrow-in-right ms-2"></i>
                    </button>
                </div>
            </form>
        </div>

        <div class="card-footer text-center bg-light py-3 border-0">
            <span class="text-muted small">Mahasiswa baru?</span> 
            <a href="daftar.php" class="text-decoration-none fw-bold">Daftar Akun</a>
        </div>
    </div>

</body>
</html>