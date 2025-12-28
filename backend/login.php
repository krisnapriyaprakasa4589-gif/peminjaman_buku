<?php
require "db.php";

$role = $_POST['role'];
$username = $_POST['username'];
$password = $_POST['password'];

$q = mysqli_query($conn,"SELECT * FROM users WHERE username='$username' AND role='$role'");
$u = mysqli_fetch_assoc($q);

if($u && password_verify($password,$u['password'])){
  echo json_encode([
    "ok"=>true,
    "user"=>[
      "name"=>$u['name'],
      "username"=>$u['username'],
      "nim"=>$u['username'],
      "dept"=>$u['dept'],
      "role"=>$u['role']
    ]
  ]);
}else{
  echo json_encode(["ok"=>false,"msg"=>"Login gagal"]);
}
