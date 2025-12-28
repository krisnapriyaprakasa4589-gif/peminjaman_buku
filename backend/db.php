<?php
$conn = mysqli_connect("localhost","root","","tubes_ipl");
if(!$conn){
  http_response_code(500);
  echo json_encode(["ok"=>false,"msg"=>"Koneksi DB gagal"]);
  exit;
}
header("Content-Type: application/json");
