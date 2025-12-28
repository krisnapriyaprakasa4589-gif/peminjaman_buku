<?php
require "db.php";
$id=$_POST['id'];
mysqli_query($conn,"DELETE FROM books WHERE id=$id");
echo json_encode(["ok"=>true]);
