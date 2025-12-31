<?php
require_once __DIR__ . '/../classes/classes.php';
$petugasObj = new Petugas((new Database())->getConnection());
header('Content-Type: application/json');

// [CLEAN CODE] Fungsi sanitasi lokal
function clean($data) { return htmlspecialchars(strip_tags(trim($data))); }

// 1. LOGIKA HAPUS
if (isset($_POST['aksi']) && $_POST['aksi'] == 'hapus') {
    $id = $_POST['id'];
    session_start();
    if ($id == $_SESSION['user_id']) {
        echo json_encode(['status' => 'error', 'message' => 'Anda tidak bisa menghapus akun sendiri!']);
        exit;
    }
    if ($petugasObj->hapusPetugas($id)) {
        echo json_encode(['status' => 'success', 'message' => 'Petugas berhasil dihapus!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal menghapus data.']);
    }
    exit;
}

// 2. LOGIKA TAMBAH
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if ($petugasObj->tambahPetugas(clean($_POST['nama_petugas']), clean($_POST['username']), $_POST['password'])) {
        echo json_encode(['status' => 'success', 'message' => 'Petugas baru berhasil ditambahkan!']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal! Username mungkin sudah dipakai.']);
    }
}
?>