<?php
class Peminjaman {
    private $conn;

    public function __construct($db) { 
        $this->conn = $db; 
    }

    // 1. MAHASISWA MENGAJUKAN PEMINJAMAN
    public function buatPeminjaman($id_mhs, $id_buku, $tgl_pinjam, $tgl_tempo) {
        $buku = new Buku($this->conn);
        $dataBuku = $buku->getBukuById($id_buku);

        if ($dataBuku && $dataBuku['stok_buku'] > 0) {
            $buku->updateStock($id_buku, -1);
            $stmt = $this->conn->prepare("INSERT INTO peminjaman (id_mahasiswa, id_buku, tanggal_pinjam, tanggal_jatuh_tempo, status) VALUES (?, ?, ?, ?, 'menunggu')");
            return $stmt->execute([$id_mhs, $id_buku, $tgl_pinjam, $tgl_tempo]);
        }
        return false;
    }

    // 2. MAHASISWA MENGEMBALIKAN (Request)
    public function ajukanPengembalian($id_pinjam) {
        $stmt = $this->conn->prepare("UPDATE peminjaman SET status = 'menunggu_kembali' WHERE id_peminjaman = ?");
        return $stmt->execute([$id_pinjam]);
    }

    // 3. PETUGAS VERIFIKASI (Terima/Tolak Pinjaman Awal)
    public function verifikasiPeminjaman($id_pinjam, $aksi) {
        if ($aksi == 'terima') {
            $stmt = $this->conn->prepare("UPDATE peminjaman SET status = 'dipinjam' WHERE id_peminjaman = ?");
            return $stmt->execute([$id_pinjam]);
        } elseif ($aksi == 'tolak') {
            $stmt = $this->conn->prepare("SELECT id_buku FROM peminjaman WHERE id_peminjaman = ?");
            $stmt->execute([$id_pinjam]);
            $id_buku = $stmt->fetchColumn();
            
            $buku = new Buku($this->conn);
            $buku->updateStock($id_buku, 1);
            
            $stmt = $this->conn->prepare("UPDATE peminjaman SET status = 'ditolak' WHERE id_peminjaman = ?");
            return $stmt->execute([$id_pinjam]);
        }
    }

    // 4. PROSES PENGEMBALIAN FINAL (Dengan Class Denda)
    public function prosesPengembalian($id_pinjam) {
        $tgl_kembali = date('Y-m-d');
        
        $stmt = $this->conn->prepare("SELECT * FROM peminjaman WHERE id_peminjaman = ?");
        $stmt->execute([$id_pinjam]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($data) {
            // A. Update Status jadi 'kembali'
            $this->conn->prepare("UPDATE peminjaman SET status = 'kembali', tanggal_kembali = ? WHERE id_peminjaman = ?")->execute([$tgl_kembali, $id_pinjam]);
            
            // B. Balikin Stok Buku (+1)
            $buku = new Buku($this->conn);
            $buku->updateStock($data['id_buku'], 1);
            
            // C. Hitung Denda
            $dendaObj = new Denda($this->conn);
            $jumlahDenda = $dendaObj->hitungDenda($data['tanggal_jatuh_tempo'], $tgl_kembali);
            
            if ($jumlahDenda > 0) {
                $dendaObj->simpanDenda($id_pinjam, $jumlahDenda);
                return "Terlambat! Denda: Rp " . number_format($jumlahDenda);
            }

            return "Buku diterima tepat waktu. Terima kasih!";
        }
        return "Data tidak ditemukan.";
    }

    // --- FUNGSI GET DATA (SELECT) ---

    public function getPermintaanMenunggu() {
        return $this->conn->query("SELECT p.*, m.nama_mahasiswa, b.judul_buku FROM peminjaman p JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa JOIN buku b ON p.id_buku = b.id_buku WHERE p.status = 'menunggu'")->fetchAll(PDO::FETCH_ASSOC);
    }
    
    public function getPermintaanKembali() {
        return $this->conn->query("SELECT p.*, m.nama_mahasiswa, b.judul_buku FROM peminjaman p JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa JOIN buku b ON p.id_buku = b.id_buku WHERE p.status = 'menunggu_kembali'")->fetchAll(PDO::FETCH_ASSOC);
    }

    // [UPDATE] Menambahkan m.nim di sini
    public function getPeminjamanAktif() {
        return $this->conn->query("SELECT p.*, m.nama_mahasiswa, m.nim, b.judul_buku FROM peminjaman p JOIN mahasiswa m ON p.id_mahasiswa = m.id_mahasiswa JOIN buku b ON p.id_buku = b.id_buku WHERE p.status = 'dipinjam' ORDER BY p.tanggal_jatuh_tempo ASC")->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getPeminjamanByMahasiswa($id_mhs) {
        $query = "SELECT p.*, b.judul_buku, b.gambar, b.penerbit 
                  FROM peminjaman p 
                  JOIN buku b ON p.id_buku = b.id_buku 
                  WHERE p.id_mahasiswa = ? AND p.status IN ('dipinjam', 'menunggu', 'menunggu_kembali')
                  ORDER BY p.tanggal_jatuh_tempo ASC";
        $stmt = $this->conn->prepare($query);
        $stmt->execute([$id_mhs]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>