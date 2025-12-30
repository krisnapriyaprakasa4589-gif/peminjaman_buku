<?php
session_start();
// Cek sesi login petugas
if (!isset($_SESSION['role']) || $_SESSION['role'] != 'petugas') { 
    header("Location: index.php"); 
    exit; 
}

require_once 'classes/classes.php'; // Load semua class
require 'header.php';

$db = (new Database())->getConnection();
$bukuObj = new Buku($db);
$pinjamObj = new Peminjaman($db);

// --- LOGIKA PHP (Server Side) ---

// 1. Verifikasi Peminjaman (Terima/Tolak Request)
if (isset($_GET['aksi']) && isset($_GET['id'])) {
    if($pinjamObj->verifikasiPeminjaman($_GET['id'], $_GET['aksi'])) { 
        echo "<script>window.location='dashboard_petugas.php';</script>"; 
    }
}

// 2. Konfirmasi Pengembalian (Terima Buku Fisik)
if (isset($_GET['terima_kembali'])) {
    $pesan = $pinjamObj->prosesPengembalian($_GET['terima_kembali']);
    echo "<script>alert('$pesan'); window.location='dashboard_petugas.php';</script>";
}
?>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h2 class="fw-bold">Selamat Datang, <?= $_SESSION['nama'] ?>! 👔</h2>
        <p class="text-muted">Panel kontrol manajemen perpustakaan.</p>
    </div>
</div>

<div class="row mb-5">
    <div class="col-md-4">
        
        <div class="card shadow mb-4">
            <div class="card-header bg-primary text-white">➕ Tambah Buku Baru</div>
            <div class="card-body">
                <form id="formTambahBuku" enctype="multipart/form-data">
                    <div class="mb-2"><label>Judul</label><input type="text" name="judul" class="form-control" required></div>
                    <div class="mb-2"><label>Kategori</label><input type="text" name="kategori" class="form-control" required></div>
                    <div class="mb-2"><label>Penerbit</label><input type="text" name="penerbit" class="form-control" placeholder="Nama Penerbit" required></div>
                    <div class="mb-2"><label>Stok</label><input type="number" name="stok" class="form-control" required></div>
                    <div class="mb-3"><label>Cover</label><input type="file" name="foto" class="form-control"></div>
                    
                    <button class="btn btn-success w-100" type="submit" id="btnSimpan">Simpan Buku</button>
                    <button class="btn btn-success w-100 d-none" type="button" id="btnLoading" disabled>
                        <span class="spinner-border spinner-border-sm"></span> Menyimpan...
                    </button>
                </form>
            </div>
        </div>
        
        <div class="card shadow border-warning mb-4">
            <div class="card-header bg-warning text-dark fw-bold">⏳ Request Peminjaman</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php 
                    $requests = $pinjamObj->getPermintaanMenunggu();
                    if(empty($requests)) echo "<div class='p-3 text-muted text-center'>Tidak ada request pinjam.</div>";
                    foreach($requests as $req): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= $req['nama_mahasiswa'] ?></h6>
                            <small class="text-muted"><?= $req['judul_buku'] ?></small>
                        </div>
                        <div>
                            <a href="?aksi=terima&id=<?= $req['id_peminjaman'] ?>" class="btn btn-sm btn-success">✔</a>
                            <a href="?aksi=tolak&id=<?= $req['id_peminjaman'] ?>" class="btn btn-sm btn-danger">✖</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="card shadow border-info">
            <div class="card-header bg-info text-white fw-bold">↩ Konfirmasi Pengembalian</div>
            <div class="card-body p-0">
                <div class="list-group list-group-flush">
                    <?php 
                    $kembali = $pinjamObj->getPermintaanKembali();
                    if(empty($kembali)) echo "<div class='p-3 text-muted text-center'>Tidak ada request kembali.</div>";
                    foreach($kembali as $k): ?>
                    <div class="list-group-item d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-0 fw-bold"><?= $k['nama_mahasiswa'] ?></h6>
                            <small class="text-muted">Return: <b><?= $k['judul_buku'] ?></b></small>
                        </div>
                        <div>
                            <a href="?terima_kembali=<?= $k['id_peminjaman'] ?>" class="btn btn-sm btn-primary" onclick="return confirm('Pastikan fisik buku sudah diterima. Lanjutkan?')">Terima</a>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-8">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h3>📚 Manajemen Buku</h3>
            <a href="pengembalian.php" class="btn btn-outline-primary fw-bold">Pengembalian Manual</a>
        </div>
        <div class="card shadow">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr><th>Cover</th><th>Judul</th><th>Penerbit</th><th>Stok</th><th>Aksi</th></tr>
                        </thead>
                        <tbody id="tabelBukuBody">
                            <?php foreach ($bukuObj->getAllBooks() as $buku): ?>
                            <tr id="row-buku-<?= $buku['id_buku'] ?>">
                                <td><img src="uploads/<?= $buku['gambar'] ?>" width="40" class="rounded"></td>
                                <td><?= $buku['judul_buku'] ?><br><small class="text-muted"><?= $buku['kategori_buku'] ?></small></td>
                                <td><?= $buku['penerbit'] ?></td>
                                <td><span class="badge bg-<?= $buku['stok_buku']>0?'success':'danger' ?>"><?= $buku['stok_buku'] ?></span></td>
                                <td>
                                    <a href="edit_buku.php?id=<?= $buku['id_buku'] ?>" class="btn btn-sm btn-info text-white">Edit</a>
                                    <button onclick="hapusBuku(<?= $buku['id_buku'] ?>)" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="row mb-5">
    <div class="col-12">
        <div class="card shadow border-0">
            <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                <h5 class="mb-0">📋 Monitoring Buku Sedang Dipinjam</h5>
                <span class="badge bg-light text-dark">Realtime Data</span>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 align-middle">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Nama Peminjam</th>
                                <th>Judul Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Jatuh Tempo</th>
                                <th>Status Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            // Mengambil data peminjaman yang statusnya 'dipinjam'
                            $listAktif = $pinjamObj->getPeminjamanAktif();
                            
                            if(empty($listAktif)): ?>
                                <tr><td colspan="5" class="text-center py-4 text-muted">Tidak ada buku yang sedang dipinjam saat ini.</td></tr>
                            <?php else: 
                                foreach($listAktif as $row):
                                    $jatuhTempo = new DateTime($row['tanggal_jatuh_tempo']);
                                    $hariIni = new DateTime();
                                    $selisih = $hariIni->diff($jatuhTempo);
                                    $lewat = ($hariIni > $jatuhTempo);
                            ?>
                            <tr>
                                <td class="ps-4 fw-bold text-primary"><?= $row['nama_mahasiswa'] ?></td>
                                <td><?= $row['judul_buku'] ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                                <td><?= date('d M Y', strtotime($row['tanggal_jatuh_tempo'])) ?></td>
                                <td>
                                    <?php if($lewat): ?>
                                        <span class="badge bg-danger">Telat <?= $selisih->days ?> Hari</span>
                                    <?php else: ?>
                                        <span class="badge bg-success">Sisa <?= $selisih->days ?> Hari</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // AJAX TAMBAH BUKU
    document.getElementById('formTambahBuku').addEventListener('submit', function(e) {
        e.preventDefault(); 
        let btnSimpan = document.getElementById('btnSimpan');
        let btnLoading = document.getElementById('btnLoading');
        btnSimpan.classList.add('d-none');
        btnLoading.classList.remove('d-none');

        let formData = new FormData(this);

        fetch('process/proses_buku.php', { method: 'POST', body: formData })
        .then(response => response.json())
        .then(data => {
            if (data.status === 'success') {
                Swal.fire('Berhasil!', data.message, 'success');
                let newRow = `
                    <tr id="row-buku-${data.data.id}" class="animate__animated animate__fadeIn table-success">
                        <td><img src="uploads/${data.data.gambar}" width="40" class="rounded"></td>
                        <td>${data.data.judul}<br><small class="text-muted">${data.data.kategori}</small></td>
                        <td>${data.data.penerbit}</td>
                        <td><span class="badge bg-success">${data.data.stok}</span></td>
                        <td>
                            <a href="edit_buku.php?id=${data.data.id}" class="btn btn-sm btn-info text-white">Edit</a>
                            <button onclick="hapusBuku(${data.data.id})" class="btn btn-sm btn-danger"><i class="bi bi-trash"></i></button>
                        </td>
                    </tr>
                `;
                document.getElementById('tabelBukuBody').insertAdjacentHTML('afterbegin', newRow);
                document.getElementById('formTambahBuku').reset();
            } else {
                Swal.fire('Gagal!', data.message, 'error');
            }
        })
        .finally(() => {
            btnSimpan.classList.remove('d-none');
            btnLoading.classList.add('d-none');
        });
    });

    // AJAX HAPUS BUKU
    function hapusBuku(idBuku) {
        Swal.fire({
            title: 'Hapus Buku?',
            text: "Data akan dihapus permanen.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Ya, Hapus!'
        }).then((result) => {
            if (result.isConfirmed) {
                let formData = new FormData();
                formData.append('aksi', 'hapus');
                formData.append('id', idBuku);

                fetch('process/proses_buku.php', { method: 'POST', body: formData })
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success') {
                        Swal.fire('Terhapus!', data.message, 'success');
                        document.getElementById('row-buku-' + idBuku).remove();
                    } else {
                        Swal.fire('Gagal!', data.message, 'error');
                    }
                });
            }
        })
    }
</script>
</body>
</html>