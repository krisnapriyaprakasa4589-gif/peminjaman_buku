<?php

class Buku
{
    private PDO $conn;
    private string $uploadDir;

    public function __construct(PDO $db)
    {
        $this->conn = $db;
        $this->uploadDir = __DIR__ . '/../uploads/';
    }

    public function getAllBooks(): array
    {
        $sql = "SELECT * FROM buku ORDER BY judul_buku ASC";
        return $this->conn->query($sql)->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStokMenipis(int $batas = 5): array
    {
        $sql = "SELECT * FROM buku 
                WHERE stok_buku <= :batas AND stok_buku > 0 
                ORDER BY stok_buku ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['batas' => $batas]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getBukuById(int $id): ?array
    {
        $stmt = $this->conn->prepare("SELECT * FROM buku WHERE id_buku = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function tambahBuku(
        string $judul,
        string $kategori,
        string $penerbit,
        string $deskripsi,
        int $stok,
        array $file
    ): array {
        $gambar = $this->uploadGambar($file);

        $sql = "INSERT INTO buku 
                (judul_buku, kategori_buku, penerbit, deskripsi, stok_buku, gambar) 
                VALUES (:judul, :kategori, :penerbit, :deskripsi, :stok, :gambar)";

        $stmt = $this->conn->prepare($sql);
        $success = $stmt->execute([
            'judul'     => $judul,
            'kategori'  => $kategori,
            'penerbit'  => $penerbit,
            'deskripsi' => $deskripsi,
            'stok'      => $stok,
            'gambar'    => $gambar
        ]);

        return [
            'status' => $success,
            'gambar' => $gambar,
            'id'     => $success ? $this->conn->lastInsertId() : null
        ];
    }

    public function hapusBuku(int $id): bool
    {
        if ($this->bukuSedangDipinjam($id)) {
            return false;
        }

        $gambar = $this->getGambarById($id);

        try {
            $this->hapusRelasiPeminjaman($id);

            $stmt = $this->conn->prepare("DELETE FROM buku WHERE id_buku = :id");
            $stmt->execute(['id' => $id]);

            $this->hapusFileGambar($gambar);

            return true;
        } catch (PDOException) {
            return false;
        }
    }

    public function updateBuku(
        int $id,
        string $judul,
        string $kategori,
        string $penerbit,
        string $deskripsi,
        int $stok,
        array $file
    ): bool {
        $data = [
            'judul'     => $judul,
            'kategori'  => $kategori,
            'penerbit'  => $penerbit,
            'deskripsi' => $deskripsi,
            'stok'      => $stok,
            'id'        => $id
        ];

        $sql = "UPDATE buku 
                SET judul_buku=:judul, kategori_buku=:kategori, 
                    penerbit=:penerbit, deskripsi=:deskripsi, stok_buku=:stok";

        if (!empty($file['name'])) {
            $data['gambar'] = $this->uploadGambar($file);
            $sql .= ", gambar=:gambar";
        }

        $sql .= " WHERE id_buku=:id";

        $stmt = $this->conn->prepare($sql);
        return $stmt->execute($data);
    }

    public function updateStock(int $id, int $qty): void
    {
        $stmt = $this->conn->prepare(
            "UPDATE buku SET stok_buku = stok_buku + :qty WHERE id_buku = :id"
        );
        $stmt->execute(['qty' => $qty, 'id' => $id]);
    }

    public function cariBuku(string $keyword): array
    {
        $keyword = "%{$keyword}%";

        $sql = "SELECT * FROM buku 
                WHERE judul_buku LIKE :key 
                   OR kategori_buku LIKE :key 
                   OR penerbit LIKE :key
                ORDER BY judul_buku ASC";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['key' => $keyword]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    private function bukuSedangDipinjam(int $id): bool
    {
        $sql = "SELECT COUNT(*) FROM peminjaman 
                WHERE id_buku = :id 
                AND status IN ('dipinjam', 'menunggu', 'menunggu_kembali')";

        $stmt = $this->conn->prepare($sql);
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn() > 0;
    }

    private function getGambarById(int $id): ?string
    {
        $stmt = $this->conn->prepare("SELECT gambar FROM buku WHERE id_buku = :id");
        $stmt->execute(['id' => $id]);

        return $stmt->fetchColumn();
    }

    private function hapusRelasiPeminjaman(int $id): void
    {
        $this->conn->prepare(
            "DELETE FROM denda 
             WHERE id_peminjaman IN 
             (SELECT id_peminjaman FROM peminjaman WHERE id_buku = :id)"
        )->execute(['id' => $id]);

        $this->conn->prepare(
            "DELETE FROM peminjaman WHERE id_buku = :id"
        )->execute(['id' => $id]);
    }

    private function hapusFileGambar(?string $gambar): void
    {
        if ($gambar && $gambar !== 'default.jpg') {
            $path = $this->uploadDir . $gambar;
            if (file_exists($path)) {
                unlink($path);
            }
        }
    }

    private function uploadGambar(array $file): string
    {
        if (empty($file['name'])) {
            return 'default.jpg';
        }

        if (!file_exists($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $namaFile = time() . '_' . basename($file['name']);
        $target = $this->uploadDir . $namaFile;
        $ext = strtolower(pathinfo($target, PATHINFO_EXTENSION));

        if (in_array($ext, ['jpg', 'jpeg', 'png', 'gif']) &&
            move_uploaded_file($file['tmp_name'], $target)) {
            return $namaFile;
        }

        return 'default.jpg';
    }
}
