<?php
session_start();
session_destroy();
header("Location: index.php"); // Kembali ke index
exit;
?>