<?php
require "db.php";
$id=$_POST['id'];
mysqli_query($conn,"DELETE FROM requests WHERE id=$id");
echo json_encode(["ok"=>true]);
