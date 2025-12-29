<?php
class Denda {
    private $conn;
    private $tarifPerHari = 1000; // Ubah disini jika tarif denda naik

    public function __construct($db) {
        $this->conn = $db;
    }

    // Fungsi Murni: Hanya menghitung jumlah rupiah (Tanpa koneksi DB)
    public function hitungDenda($tgl_jatuh_tempo, $tgl_kembali) {
        $tempo = new DateTime($tgl_jatuh_tempo);
        $kembali = new DateTime($tgl_kembali);

        if ($kembali > $tempo) {
            $selisih = $tempo->diff($kembali);
            return $selisih->days * $this->tarifPerHari;
        }
        return 0; // Tidak ada denda
    }

    // Fungsi Database: Menyimpan tagihan ke tabel denda
    public function simpanDenda($id_peminjaman, $jumlah) {
        if ($jumlah > 0) {
            $stmt = $this->conn->prepare("INSERT INTO denda (id_peminjaman, jumlah_denda) VALUES (?, ?)");
            return $stmt->execute([$id_peminjaman, $jumlah]);
        }
        return false;
    }
}
?>