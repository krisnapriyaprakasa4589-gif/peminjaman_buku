<?php
class Database {
  protected $conn;

  public function __construct() {
    $this->conn = new mysqli("localhost","root","","tubes_ipl");
    if($this->conn->connect_error){
      die("DB Error");
    }
  }

  public function getConnection() {
    return $this->conn;
  }
}
