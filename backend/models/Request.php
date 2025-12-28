<?php
require_once __DIR__."/../core/Database.php";

class Request extends Database {

  public function getAll() {
    return $this->conn
      ->query("SELECT * FROM requests")
      ->fetch_all(MYSQLI_ASSOC);
  }

  public function updateStatus($id,$status) {
    $stmt = $this->conn->prepare(
      "UPDATE requests SET status=? WHERE id=?"
    );
    $stmt->bind_param("si",$status,$id);
    return $stmt->execute();
  }
}
