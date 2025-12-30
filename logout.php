<?php
session_start();
session_destroy(); // Fungsi untuk menghapus semua sesi
header("Location: index.php"); // fungsi kembali ke login
exit;
?>