<?php
session_start();

// hapus semua session (mahasiswa / petugas / admin)
$_SESSION = [];
session_unset();
session_destroy();

echo json_encode([
  'ok' => true
]);
