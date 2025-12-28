<?php
header('Content-Type: application/json');
require "koneksi.php";

$id = $_POST['id'] ?? null;
$action = $_POST['action'] ?? null;

if (!$id || !$action) {
    echo json_encode(['ok'=>false,'msg'=>'Parameter tidak lengkap']);
    exit;
}

/* =========================
   APPROVE PEMINJAMAN
========================= */
if ($action === 'approve') {

    // Ambil book_id
    $q = mysqli_query($conn, "SELECT book_id FROM peminjaman WHERE id='$id'");
    $r = mysqli_fetch_assoc($q);

    if (!$r) {
        echo json_encode(['ok'=>false,'msg'=>'Data tidak ditemukan']);
        exit;
    }

    $book_id = $r['book_id'];

    // Update status peminjaman
    mysqli_query($conn, "
        UPDATE peminjaman 
        SET status='Approved' 
        WHERE id='$id'
    ");

    // Kurangi stok buku
    mysqli_query($conn, "
        UPDATE books 
        SET stock = stock - 1 
        WHERE id='$book_id'
    ");

    echo json_encode(['ok'=>true]);
    exit;
}

/* =========================
   KEMBALIKAN BUKU
========================= */
if ($action === 'return') {

    $returned_date = date('Y-m-d');

    $q = mysqli_query($conn, "
        SELECT due_date, book_id 
        FROM peminjaman 
        WHERE id='$id'
    ");
    $r = mysqli_fetch_assoc($q);

    if (!$r) {
        echo json_encode(['ok'=>false,'msg'=>'Data tidak ditemukan']);
        exit;
    }

    $due = new DateTime($r['due_date']);
    $ret = new DateTime($returned_date);

    $lateDays = 0;
    if ($ret > $due) {
        $lateDays = $due->diff($ret)->days;
    }

    $fine = $lateDays * 2000;

    mysqli_query($conn, "
        UPDATE peminjaman
        SET status='Returned',
            returned_date='$returned_date',
            fine='$fine'
        WHERE id='$id'
    ");

    // Tambah stok buku kembali
    mysqli_query($conn, "
        UPDATE books 
        SET stock = stock + 1 
        WHERE id='{$r['book_id']}'
    ");

    echo json_encode(['ok'=>true,'fine'=>$fine]);
    exit;
}

/* =========================
   TOLAK PEMINJAMAN
========================= */
if ($action === 'reject') {

    mysqli_query($conn, "
        UPDATE peminjaman 
        SET status='Rejected' 
        WHERE id='$id'
    ");

    echo json_encode(['ok'=>true]);
    exit;
}

/* =========================
   ACTION TIDAK VALID
========================= */
echo json_encode(['ok'=>false,'msg'=>'Action tidak dikenali']);
