<?php
require_once __DIR__."/../core/Database.php";

class Book extends Database {

  public function getAll() {
    $q = $this->conn->query("SELECT * FROM books");
    return $q->fetch_all(MYSQLI_ASSOC);
  }

  public function save($data) {
    $stmt = $this->conn->prepare(
      "INSERT INTO books(title,author,category,stock,image)
       VALUES(?,?,?,?,?)"
    );
    $stmt->bind_param(
      "sssis",
      $data['title'],
      $data['author'],
      $data['category'],
      $data['stock'],
      $data['image']
    );
    return $stmt->execute();
  }

  public function delete($id) {
    return $this->conn->query("DELETE FROM books WHERE id=$id");
  }
}
