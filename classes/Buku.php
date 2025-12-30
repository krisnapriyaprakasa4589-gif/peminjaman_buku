<?php
class Buku {
    private $conn;
    public function __construct($db) { $this->conn = $db; }

    // [UPDATE DISINI] Mengubah urutan menjadi Abjad (A-Z)
    public function getAllBooks() {
        return $this->conn->query("SELECT * FROM buku ORDER BY judul_buku ASC")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getStokMenipis($batas = 5) {
        $stmt = $this->conn->prepare("SELECT * FROM buku WHERE stok_buku <= ? AND stok_buku > 0 ORDER BY stok_buku ASC");
        $stmt->execute([$batas]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBukuById($id) {
        $stmt = $this->conn->prepare("SELECT * FROM buku WHERE id_buku = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function tambahBuku($judul, $kategori, $penerbit, $deskripsi, $stok, $file) {
        $gambar = $this->uploadGambar($file);
        $stmt = $this->conn->prepare("INSERT INTO buku (judul_buku, kategori_buku, penerbit, deskripsi, stok_buku, gambar) VALUES (?, ?, ?, ?, ?, ?)");
        $result = $stmt->execute([$judul, $kategori, $penerbit, $deskripsi, $stok, $gambar]);
        
        if ($result) {
            return ['status' => true, 'gambar' => $gambar, 'id' => $this->conn->lastInsertId()];
        }
        return ['status' => false];
    }

    public function hapusBuku($id) {
        $stmt = $this->conn->prepare("SELECT COUNT(*) FROM peminjaman WHERE id_buku = ? AND status IN ('dipinjam', 'menunggu', 'menunggu_kembali')");
        $stmt->execute([$id]);
        if ($stmt->fetchColumn() > 0) return false;

        $stmt = $this->conn->prepare("SELECT gambar FROM buku WHERE id_buku = ?");
        $stmt->execute([$id]);
        $gambar = $stmt->fetchColumn();

        try {
            $this->conn->prepare("DELETE FROM denda WHERE id_peminjaman IN (SELECT id_peminjaman FROM peminjaman WHERE id_buku = ?)")->execute([$id]);
            $this->conn->prepare("DELETE FROM peminjaman WHERE id_buku = ?")->execute([$id]);
            $stmt = $this->conn->prepare("DELETE FROM buku WHERE id_buku = ?");
            if ($stmt->execute([$id])) {
                if ($gambar && $gambar != 'default.jpg' && file_exists(__DIR__ . '/../uploads/' . $gambar)) {
                    unlink(__DIR__ . '/../uploads/' . $gambar);
                }
                return true;
            }
        } catch (PDOException $e) { return false; }
        return false;
    }

    public function updateBuku($id, $judul, $kategori, $penerbit, $deskripsi, $stok, $file) {
        $query = "UPDATE buku SET judul_buku=?, kategori_buku=?, penerbit=?, deskripsi=?, stok_buku=? WHERE id_buku=?";
        $params = [$judul, $kategori, $penerbit, $deskripsi, $stok, $id];
        
        if (isset($file['name']) && $file['name'] != "") {
            $gambar = $this->uploadGambar($file);
            $query = "UPDATE buku SET judul_buku=?, kategori_buku=?, penerbit=?, deskripsi=?, stok_buku=?, gambar=? WHERE id_buku=?";
            $params = [$judul, $kategori, $penerbit, $deskripsi, $stok, $gambar, $id];
        }
        $stmt = $this->conn->prepare($query);
        return $stmt->execute($params);
    }
    
    public function updateStock($id, $qty) {
        $stmt = $this->conn->prepare("UPDATE buku SET stok_buku = stok_buku + ? WHERE id_buku = ?");
        $stmt->execute([$qty, $id]);
    }

    public function cariBuku($keyword) {
        $keyword = "%{$keyword}%"; 
        // [UPDATE] Pencarian juga diurutkan Abjad
        $query = "SELECT * FROM buku WHERE judul_buku LIKE ? OR kategori_buku LIKE ? OR penerbit LIKE ? ORDER BY judul_buku ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$keyword, $keyword, $keyword]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function uploadGambar($file) {
        $gambar = 'default.jpg';
        if (isset($file['name']) && $file['name'] != "") {
            $target_dir = __DIR__ . '/../uploads/';
            if (!file_exists($target_dir)) { mkdir($target_dir, 0777, true); }
            $nama_file = time() . "_" . basename($file["name"]);
            $target_file = $target_dir . $nama_file;
            $ext = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));
            if(in_array($ext, ['jpg', 'png', 'jpeg', 'gif'])) {
                if (move_uploaded_file($file["tmp_name"], $target_file)) $gambar = $nama_file;
            }
        }
        return $gambar;
    }
}
?>