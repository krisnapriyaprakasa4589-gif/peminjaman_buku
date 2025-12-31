<?php
require_once 'classes/classes.php';

$db = (new Database())->getConnection();
$mhs = new Mahasiswa($db);
$pesan = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nim = $_POST['nim'];
    $nama = $_POST['nama'];
    $pass = $_POST['password'];

    // Cek NIM
    $stmt = $db->prepare("SELECT * FROM mahasiswa WHERE nim = ?");
    $stmt->execute([$nim]);
    
    if ($stmt->rowCount() > 0) {
        $pesan = "<div class='alert alert-danger alert-custom d-flex align-items-center'><i class='bi bi-exclamation-triangle-fill me-2'></i> NIM sudah terdaftar!</div>";
    } else {
        if ($mhs->register($nim, $nama, $pass)) {
            // REDIRECT KEMBALI KE index.php
            echo "<script>alert('Pendaftaran Berhasil! Silakan Login.'); window.location='index.php';</script>";
        } else {
            $pesan = "<div class='alert alert-danger alert-custom'>Gagal mendaftar.</div>";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar Mahasiswa</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            /* Gradient yang sama dengan Login agar konsisten */
            background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .register-card {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 20px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 450px;
            overflow: hidden;
            transition: transform 0.3s;
        }

        .register-card:hover {
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
            /* Warna Ungu kehijauan untuk membedakan dengan Login */
            background: linear-gradient(135deg, #23d5ab 0%, #23a6d5 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
            box-shadow: 0 5px 15px rgba(35, 166, 213, 0.4);
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
            border-color: #23a6d5;
        }

        .btn-register {
            background: linear-gradient(135deg, #23d5ab 0%, #23a6d5 100%);
            border: none;
            border-radius: 10px;
            padding: 12px;
            font-weight: 600;
            letter-spacing: 1px;
            transition: all 0.3s;
            color: white;
        }

        .btn-register:hover {
            background: linear-gradient(135deg, #23a6d5 0%, #23d5ab 100%);
            transform: scale(1.02);
            box-shadow: 0 5px 15px rgba(35, 213, 171, 0.4);
            color: white;
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

    <div class="register-card">
        <div class="card-header-custom">
            <div class="icon-circle">
                <i class="bi bi-person-plus-fill"></i>
            </div>
            <h3 class="fw-bold text-dark">Join Us!</h3>
            <p class="text-muted small">Daftar sebagai Mahasiswa Baru</p>
        </div>

        <div class="p-4 pt-2">
            <?= $pesan ?>

            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">NIM</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-card-heading text-secondary"></i></span>
                        <input type="text" name="nim" class="form-control" required placeholder="Contoh: 10120234">
                    </div>
                </div>

                <div class="mb-3">
                    <label class="form-label text-muted small fw-bold">NAMA LENGKAP</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-person-badge text-secondary"></i></span>
                        <input type="text" name="nama" class="form-control" required placeholder="Nama Anda">
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label text-muted small fw-bold">PASSWORD</label>
                    <div class="input-group">
                        <span class="input-group-text"><i class="bi bi-lock-fill text-secondary"></i></span>
                        <input type="password" name="password" class="form-control" required placeholder="Buat password aman">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 btn-register mb-3">DAFTAR SEKARANG</button>
                
                <div class="text-center">
                    <span class="text-muted small">Sudah punya akun?</span>
                    <a href="index.php" class="text-decoration-none fw-bold" style="color: #23a6d5;">Login di sini</a>
                </div>
            </form>
        </div>
    </div>

</body>
</html>