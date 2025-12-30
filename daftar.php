<?php
require_once 'classes/classes.php';
require 'header.php';

$db = (new Database())->getConnection();
$msg = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $mhs = new Mahasiswa($db);
    // Registrasi
    if ($mhs->register($_POST['nama'], $_POST['nim'], $_POST['prodi'], $_POST['password'])) {
        $msg = "<div class='alert alert-success'>Pendaftaran Berhasil! <a href='index.php' class='fw-bold'>Login disini</a></div>";
    } else {
        $msg = "<div class='alert alert-danger'>Gagal mendaftar. NIM mungkin sudah terdaftar.</div>";
    }
}
?>

<div class="row justify-content-center mt-5">
    <div class="col-md-5">
        <div class="card shadow p-4 border-0">
            <h3 class="mb-4 text-center fw-bold text-primary">Daftar Anggota Baru</h3>
            <?= $msg ?>
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label text-muted small">Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">NIM</label>
                    <input type="text" name="nim" class="form-control" required>
                </div>
                <div class="mb-3">
                    <label class="form-label text-muted small">Program Studi</label>
                    <input type="text" name="prodi" class="form-control" required>
                </div>
                <div class="mb-4">
                    <label class="form-label text-muted small">Password</label>
                    <input type="password" name="password" class="form-control" required>
                </div>
                <button class="btn btn-primary w-100 py-2 fw-bold">Daftar Sekarang</button>
            </form>
            <div class="text-center mt-3">
                <small>Sudah punya akun? <a href="index.php">Login</a></small>
            </div>
        </div>
    </div>
</div>
</body>
</html>