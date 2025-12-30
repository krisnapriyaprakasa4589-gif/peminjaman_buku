<?php
session_start();
require_once 'classes/classes.php';
require 'header.php';

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'petugas') {
    header("Location: index.php");
    exit;
}

$db = (new Database())->getConnection();
$buku = new Buku($db);

$idBuku = $_GET['id'] ?? null;
if ($idBuku === null) {
    header("Location: dashboard_petugas.php");
    exit;
}

$dataBuku = $buku->getBukuById((int)$idBuku);
if (!$dataBuku) {
    header("Location: dashboard_petugas.php");
    exit;
}

$pesanError = null;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $success = $buku->updateBuku(
        (int)$idBuku,
        $_POST['judul'],
        $_POST['kategori'],
        $_POST['penerbit'],
        $_POST['deskripsi'],
        (int)$_POST['stok'],
        $_FILES['foto']
    );

    if ($success) {
        echo "<script>alert('Data buku berhasil diperbarui'); window.location='dashboard_petugas.php';</script>";
        exit;
    }

    $pesanError = "Gagal mengupdate data buku";
}
?>

<div class="row justify-content-center">
    <div class="col-md-6">
        <div class="card shadow border-0">
            <div class="card-header bg-info text-white">
                Edit Data Buku
            </div>
            <div class="card-body">

                <?php if ($pesanError): ?>
                    <div class="alert alert-danger"><?= $pesanError ?></div>
                <?php endif; ?>

                <form method="post" enctype="multipart/form-data">
                    <div class="mb-3">
                        <label class="form-label">Judul Buku</label>
                        <input
                            type="text"
                            name="judul"
                            class="form-control"
                            value="<?= htmlspecialchars($dataBuku['judul_buku']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Kategori</label>
                        <input
                            type="text"
                            name="kategori"
                            class="form-control"
                            value="<?= htmlspecialchars($dataBuku['kategori_buku']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Penerbit</label>
                        <input
                            type="text"
                            name="penerbit"
                            class="form-control"
                            value="<?= htmlspecialchars($dataBuku['penerbit']) ?>"
                            required
                        >
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Deskripsi</label>
                        <textarea
                            name="deskripsi"
                            class="form-control"
                            rows="4"
                        ><?= htmlspecialchars($dataBuku['deskripsi']) ?></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Stok</label>
                        <input
                            type="number"
                            name="stok"
                            class="form-control"
                            value="<?= (int)$dataBuku['stok_buku'] ?>"
                            required
                        >
                    </div>

                    <div class="mb-4">
                        <label class="form-label">Ganti Cover</label>
                        <div class="d-flex align-items-center gap-3">
                            <img
                                src="uploads/<?= htmlspecialchars($dataBuku['gambar']) ?>"
                                width="60"
                                class="rounded border"
                            >
                            <input type="file" name="foto" class="form-control">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between">
                        <a href="dashboard_petugas.php" class="btn btn-secondary">Batal</a>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>

            </div>
        </div>
    </div>
</div>

</body>
</html>
