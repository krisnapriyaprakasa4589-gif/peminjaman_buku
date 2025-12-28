<?php
$conn = mysqli_connect("localhost","root","","tubes_ipl");

if(!$conn){
    die("Koneksi DB gagal: " . mysqli_connect_error());
}
