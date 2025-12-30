<?php
session_start();
require_once __DIR__ . '/../classes/classes.php';
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_SESSION['user_id'])) {
    $db = (new Database())->getConnection();
    $pinjamObj = new Peminjaman($db);
    $bukuObj = new Buku($db);
    
    $id_buku = $_POST['id_buku'];
    $dataBuku = $bukuObj->getBukuById($id_buku);
    
    if ($pinjamObj->buatPeminjaman($_SESSION['user_id'], $id_buku, date('Y-m-d'), $_POST['tgl_kembali'])) {
        echo json_encode(['status' => 'success', 'message' => 'Berhasil!', 'data' => [
            'id_buku' => $id_buku, 'judul' => $dataBuku['judul_buku'], 'penerbit' => $dataBuku['penerbit'],
            'gambar' => $dataBuku['gambar'], 'tgl_tempo_indo' => date('d M Y', strtotime($_POST['tgl_kembali'])), 'status_text' => 'Menunggu Persetujuan'
        ]]);
    } else echo json_encode(['status' => 'error', 'message' => 'Stok habis/Gagal.']);
} else echo json_encode(['status' => 'error', 'message' => 'Akses ditolak.']);
?>