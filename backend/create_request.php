<?php
header('Content-Type: application/json');
require "koneksi.php";

if (empty($_POST)) {
    echo json_encode(['ok'=>false,'msg'=>'POST kosong']);
    exit;
}

$sql = "
INSERT INTO peminjaman
(book_id, judul_buku, mahasiswa_nim, mahasiswa_nama, mahasiswa_dept,
 due_date, note, status, created_at)
VALUES (
  '{$_POST['book_id']}',
  '{$_POST['judul_buku']}',
  '{$_POST['mahasiswa_nim']}',
  '{$_POST['mahasiswa_nama']}',
  '{$_POST['mahasiswa_dept']}',
  '{$_POST['due_date']}',
  '{$_POST['note']}',
  'Pending',
  NOW()
)";

$q = mysqli_query($conn, $sql);

if (!$q) {
    echo json_encode([
        'ok'=>false,
        'msg'=>'Query gagal',
        'error'=>mysqli_error($conn)
    ]);
    exit;
}

echo json_encode(['ok'=>true]);
