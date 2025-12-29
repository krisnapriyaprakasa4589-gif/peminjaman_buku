<?php
require "db.php";

$mahasiswa_id   = $_POST['mahasiswa_id'];
$mahasiswa_nim  = $_POST['mahasiswa_nim'];
$mahasiswa_nama = $_POST['mahasiswa_nama'];
$buku_id        = $_POST['buku_id'];
$judul_buku     = $_POST['judul_buku'];
$tanggal_pinjam = $_POST['tanggal_pinjam'];
$due_date       = $_POST['due_date'];
$note           = $_POST['note'] ?? '';

if(!$mahasiswa_id){
  echo json_encode(["ok"=>false,"msg"=>"Field mahasiswa_id kosong"]);
  exit;
}

$q = mysqli_query($conn, "
  INSERT INTO peminjaman
  (
    mahasiswa_id,
    mahasiswa_nim,
    mahasiswa_nama,
    buku_id,
    judul_buku,
    tanggal_pinjam,
    due_date,
    status,
    note
  ) VALUES (
    '$mahasiswa_id',
    '$mahasiswa_nim',
    '$mahasiswa_nama',
    '$buku_id',
    '$judul_buku',
    '$tanggal_pinjam',
    '$due_date',
    'Pending',
    '$note'
  )
");

if($q){
  echo json_encode(["ok"=>true]);
}else{
  echo json_encode([
    "ok"=>false,
    "msg"=>mysqli_error($conn)
  ]);
}
