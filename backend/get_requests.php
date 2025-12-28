<?php
header('Content-Type: application/json');
require "koneksi.php";

$q = mysqli_query($conn, "
  SELECT 
    id,
    mahasiswa_nama AS studentName,
    mahasiswa_nim AS studentNIM,
    judul_buku AS title,
    due_date,
    status,
    note,
    fine
  FROM peminjaman
  ORDER BY created_at DESC
");

$data = [];
while($r = mysqli_fetch_assoc($q)){
  $data[] = $r;
}

echo json_encode(['requests'=>$data]);
