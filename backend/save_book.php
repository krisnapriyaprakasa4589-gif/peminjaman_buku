<?php
require "db.php";

$title = $_POST['title'];
$author = $_POST['author'];
$category = $_POST['category'];
$stock = $_POST['stock'];
$imagePath = "";

// ⬇️ PROSES UPLOAD GAMBAR
if(isset($_FILES['image'])){
  $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
  $name = uniqid("book_") . "." . $ext;
  $target = "../uploads/books/" . $name;

  if(move_uploaded_file($_FILES['image']['tmp_name'], $target)){
    $imagePath = "uploads/books/" . $name;
  }
}

// INSERT
mysqli_query($conn,"INSERT INTO books 
VALUES(NULL,'$title','$author','$category',$stock,'$imagePath')");

echo json_encode(["ok"=>true]);
