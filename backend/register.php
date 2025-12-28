<?php
require "db.php";

$role = $_POST['role'];
$name = $_POST['name'];
$username = $_POST['username'];
$dept = $_POST['dept'];
$password = password_hash($_POST['password'], PASSWORD_DEFAULT);

$cek = mysqli_query($conn,"SELECT id FROM users WHERE username='$username'");
if(mysqli_num_rows($cek)>0){
  echo json_encode(["ok"=>false,"msg"=>"Username sudah terdaftar"]);
  exit;
}

mysqli_query($conn,"INSERT INTO users VALUES(NULL,'$role','$name','$username','$dept','$password')");
echo json_encode(["ok"=>true]);
