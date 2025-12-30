<?php
session_start();
if ($_SESSION['role'] != 'mahasiswa') { header("Location: index.php"); exit; }

require_once 'classes/classes.php';
require 'header.php';

$db = (new Database())->getConnection();
$bukuObj = new Buku($db);
$pinjamObj = new Peminjaman($db);

// LOGIKA GET
if (isset($_GET['kembalikan_id'])) {
    if($pinjamObj->ajukanPengembalian($_GET['kembalikan_id'])) {
        echo "<script>alert('Pengajuan pengembalian berhasil!'); window.location='dashboard_mhs.php';</script>";
    }
}
?>

<div class="p-4 mb-4 bg-primary text-white rounded-3 shadow-sm d-flex justify-content-between align-items-center">
    <div><h1 class="display-6 fw-bold">Halo, <?= $_SESSION['nama'] ?>! 👋</h1><p class="mb-0">Selamat datang di Perpustakaan Digital.</p></div>
    <i class="bi bi-book-half display-3 opacity-50"></i>
</div>

<div class="row mb-5">
    <div class="col-12">
        <h4 class="mb-3 text-secondary"><i class="bi bi-clock-history"></i> Status Peminjaman Saya</h4>
        <div class="card shadow-sm border-0"><div class="card-body p-0"><div class="list-group list-group-flush" id="containerListPinjaman">
            <?php 
            $myBooks = $pinjamObj->getPeminjamanByMahasiswa($_SESSION['user_id']);
            if(empty($myBooks)): ?><div class="p-4 text-center text-muted" id="emptyState"><p class="mt-2">Tidak ada aktivitas peminjaman.</p></div>
            <?php else: foreach($myBooks as $book): ?>
            <div class="list-group-item d-flex align-items-center py-3 flex-wrap">
                <div class="me-3"><img src="uploads/<?= $book['gambar'] ?>" class="rounded shadow-sm" style="width: 50px; height: 70px; object-fit: cover;"></div>
                <div class="flex-grow-1"><h6 class="mb-0 fw-bold"><?= $book['judul_buku'] ?></h6><small class="text-muted">Penerbit: <?= $book['penerbit'] ?></small><br><small>Jatuh Tempo: <b><?= date('d M Y', strtotime($book['tanggal_jatuh_tempo'])) ?></b></small></div>
                <div class="text-end ms-3">
                    <?php if($book['status'] == 'menunggu'): ?><span class="badge bg-warning text-dark mb-2">Menunggu Persetujuan</span>
                    <?php elseif($book['status'] == 'menunggu_kembali'): ?><span class="badge bg-info text-white mb-2">Menunggu Konfirmasi</span>
                    <?php else: ?><span class="badge bg-success mb-2">Dipinjam</span><br><a href="?kembalikan_id=<?= $book['id_peminjaman'] ?>" class="btn btn-sm btn-outline-danger fw-bold" onclick="return confirm('Kembalikan?')">Kembalikan</a><?php endif; ?>
                </div>
            </div><?php endforeach; endif; ?>
        </div></div></div>
    </div>
</div>

<div class="d-flex justify-content-between align-items-center mb-3 flex-wrap gap-2">
    <h4 class="text-secondary mb-0"><i class="bi bi-grid-fill"></i> Koleksi Buku</h4>
    <div class="input-group" style="max-width: 300px;">
        <span class="input-group-text bg-white border-end-0"><i class="bi bi-search text-muted"></i></span>
        <input type="text" id="inputCari" class="form-control border-start-0" placeholder="Cari Judul...">
    </div>
</div>

<div class="row row-cols-1 row-cols-md-3 row-cols-lg-4 g-4" id="containerBuku">
    <?php foreach ($bukuObj->getAllBooks() as $buku): ?>
    <div class="col">
        <div class="card h-100 shadow-sm border-0 card-hover">
            <div style="height: 250px; overflow: hidden; cursor: pointer;" onclick="bukaModalDetail(<?= htmlspecialchars(json_encode($buku)) ?>)">
                <img src="uploads/<?= $buku['gambar'] ?>" class="card-img-top w-100 h-100" style="object-fit: cover;" onerror="this.src='https://via.placeholder.com/300x400'">
                <?php if($buku['stok_buku'] == 0): ?><div class="position-absolute top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 d-flex align-items-center justify-content-center"><span class="badge bg-danger fs-5">HABIS</span></div><?php endif; ?>
                <div class="position-absolute bottom-0 w-100 p-2 text-center bg-dark bg-opacity-50 text-white" style="font-size: 0.8rem;">Klik untuk Detail</div>
            </div>
            <div class="card-body d-flex flex-column">
                <h6 class="card-subtitle mb-2 text-muted small text-uppercase"><?= $buku['kategori_buku'] ?></h6>
                <h5 class="card-title fw-bold text-dark mb-0"><?= $buku['judul_buku'] ?></h5>
                <small class="text-muted mb-2">By: <?= $buku['penerbit'] ?></small>
                
                <div class="mt-auto">
                    <span class="badge bg-light text-dark border mb-2">Stok: <span id="stok-<?= $buku['id_buku'] ?>"><?= $buku['stok_buku'] ?></span></span>
                    <?php if ($buku['stok_buku'] > 0): ?>
                    <form class="formPinjam">
                        <input type="hidden" name="id_buku" value="<?= $buku['id_buku'] ?>">
                        <div class="input-group input-group-sm">
                            <input type="date" name="tgl_kembali" class="form-control" min="<?= date('Y-m-d') ?>" required>
                            <button class="btn btn-primary btn-submit" type="submit">Pinjam</button>
                            <button class="btn btn-primary d-none btn-loading" type="button" disabled>...</button>
                        </div>
                    </form>
                    <?php else: ?><button class="btn btn-secondary btn-sm w-100 disabled">Tidak Tersedia</button><?php endif; ?>
                </div>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<div class="modal fade" id="modalDetailBuku" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="modalJudul">Judul Buku</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="row">
                    <div class="col-md-5 mb-3">
                        <img id="modalGambar" src="" class="img-fluid rounded shadow-sm w-100" style="object-fit: cover;">
                    </div>
                    <div class="col-md-7">
                        <h6 class="text-muted text-uppercase small" id="modalKategori">Kategori</h6>
                        <p class="text-muted mb-3 small">Penerbit: <span id="modalPenerbit" class="fw-bold text-dark"></span></p>
                        <hr>
                        <p id="modalDeskripsi" class="text-secondary small" style="text-align: justify; line-height: 1.6;">...</p>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 bg-light">
                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Tutup</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
    // 1. FUNGSI BUKA MODAL DETAIL
    function bukaModalDetail(dataBuku) {
        document.getElementById('modalJudul').innerText = dataBuku.judul_buku;
        document.getElementById('modalKategori').innerText = dataBuku.kategori_buku;
        document.getElementById('modalPenerbit').innerText = dataBuku.penerbit;
        document.getElementById('modalGambar').src = 'uploads/' + dataBuku.gambar;
        
        // Cek deskripsi kosong atau tidak
        let deskripsi = dataBuku.deskripsi ? dataBuku.deskripsi : 'Belum ada deskripsi untuk buku ini.';
        document.getElementById('modalDeskripsi').innerText = deskripsi;

        // Tampilkan Modal
        var myModal = new bootstrap.Modal(document.getElementById('modalDetailBuku'));
        myModal.show();
    }

    // 2. FUNGSI PINJAM (AJAX)
    function attachPinjamEvent() {
        document.querySelectorAll('.formPinjam').forEach(form => {
            form.onsubmit = function(e) {
                e.preventDefault(); 
                let btnSubmit = this.querySelector('.btn-submit');
                let btnLoading = this.querySelector('.btn-loading');
                btnSubmit.classList.add('d-none');
                btnLoading.classList.remove('d-none');

                fetch('process/proses_pinjam.php', { method: 'POST', body: new FormData(this) })
                .then(r => r.json()).then(d => {
                    if (d.status === 'success') {
                        Swal.fire('Berhasil!', d.message, 'success');
                        let stokElem = document.getElementById('stok-' + d.data.id_buku);
                        if(stokElem) stokElem.innerText = parseInt(stokElem.innerText) - 1;
                        setTimeout(()=>location.reload(), 1000);
                    } else Swal.fire('Gagal!', d.message, 'error');
                })
                .finally(() => { btnSubmit.classList.remove('d-none'); btnLoading.classList.add('d-none'); });
            }
        });
    }
    attachPinjamEvent();

    // 3. PENCARIAN LIVE
    document.getElementById('inputCari').addEventListener('keyup', function() {
        let fd = new FormData(); fd.append('keyword', this.value);
        fetch('process/cari_buku.php', { method: 'POST', body: fd })
        .then(r => r.text()).then(html => {
            document.getElementById('containerBuku').innerHTML = html;
            attachPinjamEvent(); 
        });
    });
</script>
<style>.card-hover:hover { transform: translateY(-5px); transition: 0.3s; }</style>
</body></html>