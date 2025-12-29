<?php
header('Content-Type: application/json');
require "db.php";

$studentNim = $_GET['student_nim'] ?? null;

$sql = "
  SELECT
    id,
    mahasiswa_nama,
    mahasiswa_nim,
    judul_buku,
    due_date,
    status,
    note,
    fine
  FROM peminjaman
";

if($studentNim){
  $sql .= " WHERE mahasiswa_nim='$studentNim'";
}

$sql .= " ORDER BY created_at DESC";

$q = mysqli_query($conn, $sql);

$data = [];
while($r = mysqli_fetch_assoc($q)){
  $data[] = $r;
}

echo json_encode(["requests"=>$data]);
