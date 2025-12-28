<?php
require_once "models/Book.php";

$book = new Book();
echo json_encode(["books"=>$book->getAll()]);
