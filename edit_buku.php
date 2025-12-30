<?php
session_start();
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') { header("Location: index.php"); exit; }

require_once 'classes/classes.php';
require 'header.php';

$db = (new Database())->getConnection();
$bukuObj = new Buku($db);

$id_buku = $_GET['id'] ?? null;
if(!$id_buku) { header("Location: dashboard_petugas.php"); exit; }

$dataBuku = $bukuObj->getBukuById($id_buku);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // [UPDATE] Sertakan deskripsi saat update
    if($bukuObj->updateBuku($id_buku, $_POST['judul'], $_POST['kategori'], $_POST['penerbit'], $_POST['deskripsi'], $_POST['stok'], $_FILES['foto'])) {
        echo "<script>alert('Data buku berhasil diperbarui!'); window.location='dashboard_petugas.php';</script>";
    } else {
        echo "<div class='alert alert-danger'>Gagal mengupdate data.</div>";
    }
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-info text-white fw-bold">✏ Edit Data Buku</div>
            <div class="card-body">
                <form method="POST" enctype="multipart/form-data">
                    <div class="mb-3"><label class="form-label">Judul Buku</label><input type="text" name="judul" class="form-control" value="<?= $dataBuku['judul_buku'] ?>" required></div>
                    <div class="mb-3"><label class="form-label">Kategori</label><input type="text" name="kategori" class="form-control" value="<?= $dataBuku['kategori_buku'] ?>" required></div>
                    <div class="mb-3"><label class="form-label">Penerbit</label><input type="text" name="penerbit" class="form-control" value="<?= $dataBuku['penerbit'] ?>" required></div>
                    
                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="4"><?= $dataBuku['deskripsi'] ?></textarea>
                    </div>

                    <div class="mb-3"><label class="form-label">Stok</label><input type="number" name="stok" class="form-control" value="<?= $dataBuku['stok_buku'] ?>" required></div>
                    <div class="mb-4">
                        <label class="form-label">Ganti Cover</label>
                        <div class="d-flex align-items-center gap-3">
                            <img src="uploads/<?= $dataBuku['gambar'] ?>" width="60" class="rounded border">
                            <input type="file" name="foto" class="form-control">
                        </div>
                    </div>
                    <div class="d-flex justify-content-between">
                        <a href="dashboard_petugas.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body></html>