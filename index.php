<?php
session_start();
require_once 'classes/classes.php';

// Jika sudah login, langsung lempar ke dashboard sesuai role
if (isset($_SESSION['role'])) {
    if ($_SESSION['role'] == 'petugas') {
        header("Location: dashboard_petugas.php");
    } else {
        header("Location: dashboard_mhs.php");
    }
    exit;
}

$db = (new Database())->getConnection();
$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // 1. Cek Login Petugas
    $petugas = new Petugas($db);
    $user = $petugas->login($username, $password);

    if ($user) {
        $_SESSION['user_id'] = $user['id_petugas'];
        $_SESSION['nama'] = $user['nama_petugas'];
        $_SESSION['role'] = 'petugas';
        header("Location: dashboard_petugas.php");
        exit;
    }

    // 2. Cek Login Mahasiswa
    $mhs = new Mahasiswa($db);
    $userMhs = $mhs->login($username, $password);

    if ($userMhs) {
        $_SESSION['user_id'] = $userMhs['id_mahasiswa'];
        $_SESSION['nama'] = $userMhs['nama_mahasiswa'];
        $_SESSION['role'] = 'mahasiswa';
        header("Location: dashboard_mhs.php");
        exit;
    }

    $error = "Username atau Password salah!";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .login-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 400px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .login-card:hover {
            transform: translateY(-5px);
        }

        .card-header-custom {
            background: transparent;
            text-align: center;
            padding: 30px 20px 10px;
        }

        .icon-circle {
            width: 80px;
            height: 80px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(118, 75, 162, 0.4);
        }

        .icon-circle i {
            font-size: 40px;
            color: white;
        }

        .form-control {
            border-radius: 10px;
            padding: 12px;
            border: 1px solid #ddd;
        }

        .form-control:focus {
            box-shadow: none;
            border-color: #764ba2;
        }

        .btn-login {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
        }

        .btn-login:hover {
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }

        .input-group-text {
            border-top-left-radius: 10px;
            border-bottom-left-radius: 10px;
            background-color: #f8f9fa;
            border: 1px solid #ddd;
        }
        
        .alert-custom {
            border-radius: 10px;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

    <div class="container px-4">
        <div class="login-card mx-auto">
            <div class="card-header-custom">
                <div class="icon-circle">
                    <i class="bi bi-book-half"></i>
                </div>
                <h3 class="fw-bold text-dark">Welcome Back!</h3>
                <p class="text-muted small">Perpustakaan Digital Modern</p>
            </div>

            <div class="p-4 pt-2">
                <?php if($error): ?>
                    <div class="alert alert-danger alert-custom d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-circle-fill me-2"></i>
                        <div><?= $error ?></div>
                    </div>
                <?php endif; ?>

                <form method="POST">
                    <div class="mb-3">
                        <label class="form-label text-muted small fw-bold">USERNAME / NIM</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-person-fill text-secondary"></i></span>
                            <input type="text" name="username" class="form-control" required placeholder="Masukkan username">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-muted small fw-bold">PASSWORD</label>
                        <div class="input-group">
                            <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                            <input type="password" name="password" class="form-control" required placeholder="Masukkan password">
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary w-100 btn-login mb-3">MASUK SEKARANG</button>
                    
                    <div class="text-center">
                        <span class="text-muted small">Belum punya akun?</span>
                        <a href="register.php" class="text-decoration-none fw-bold" style="color: #764ba2;">Daftar di sini</a>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>