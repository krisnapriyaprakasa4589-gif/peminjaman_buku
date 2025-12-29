<?php
session_start();
session_destroy(); // Hapus semua sesi
header("Location: index.php"); // Kembali ke login
exit;
?>