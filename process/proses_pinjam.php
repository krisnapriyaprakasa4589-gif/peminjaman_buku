<?php

session_start();
header('Content-Type: application/json');

require_once __DIR__ . '/../classes/classes.php';

function response(array $data): void
{
    echo json_encode($data);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    response([
        'status' => 'error',
        'message' => 'Metode tidak valid'
    ]);
}

if (!isset($_SESSION['user_id'])) {
    response([
        'status' => 'error',
        'message' => 'Akses ditolak'
    ]);
}

$idBuku = $_POST['id_buku'] ?? null;
$tglKembali = $_POST['tgl_kembali'] ?? null;

if (!$idBuku || !$tglKembali) {
    response([
        'status' => 'error',
        'message' => 'Data tidak lengkap'
    ]);
}

$db = (new Database())->getConnection();
$peminjaman = new Peminjaman($db);
$buku = new Buku($db);

$dataBuku = $buku->getBukuById((int)$idBuku);

if (!$dataBuku) {
    response([
        'status' => 'error',
        'message' => 'Buku tidak ditemukan'
    ]);
}

$berhasil = $peminjaman->buatPeminjaman(
    (int)$_SESSION['user_id'],
    (int)$idBuku,
    date('Y-m-d'),
    $tglKembali
);

if (!$berhasil) {
    response([
        'status' => 'error',
        'message' => 'Stok habis atau peminjaman gagal'
    ]);
}

response([
    'status' => 'success',
    'message' => 'Peminjaman berhasil',
    'data' => [
        'id_buku' => (int)$idBuku,
        'judul' => $dataBuku['judul_buku'],
        'penerbit' => $dataBuku['penerbit'],
        'gambar' => $dataBuku['gambar'],
        'tgl_tempo_indo' => date('d M Y', strtotime($tglKembali)),
        'status_text' => 'Menunggu Persetujuan'
    ]
]);
