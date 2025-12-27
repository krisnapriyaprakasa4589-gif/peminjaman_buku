<?php
// ================== REGISTER PROCESS ==================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    require "db.php";

    $role     = $_POST['role'] ?? '';
    $name     = $_POST['name'] ?? '';
    $username = $_POST['username'] ?? '';
    $dept     = $_POST['dept'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($role === '' || $name === '' || $username === '' || $password === '') {
        echo json_encode(["ok" => false, "msg" => "Harap isi semua field"]);
        exit;
    }

    // Hash password
    $hashed = password_hash($password, PASSWORD_DEFAULT);

    /* ---------- SIMPAN KE TABEL USERS ---------- */
    $stmt = $conn->prepare(
        "INSERT INTO users (role, username, password, name, nim, dept)
         VALUES (?, ?, ?, ?, ?, ?)"
    );
    $stmt->bind_param("ssssss", $role, $username, $hashed, $name, $username, $dept);

    if (!$stmt->execute()) {
        echo json_encode(["ok" => false, "msg" => $stmt->error]);
        exit;
    }

    /* ---------- SIMPAN KE TABEL MAHASISWA ---------- */
    if ($role === "mahasiswa") {
        $q = $conn->prepare("INSERT INTO mahasiswa (Nim, Nama) VALUES (?, ?)");
        $q->bind_param("ss", $username, $name);
        $q->execute();
    }

    /* ---------- SIMPAN KE TABEL PETUGAS ---------- */
    if ($role === "petugas") {
        $jabatan = "Petugas Perpustakaan";
        $q = $conn->prepare(
            "INSERT INTO petugas (Nama, Username, Password, Jabatan)
             VALUES (?, ?, ?, ?)"
        );
        $q->bind_param("ssss", $name, $username, $hashed, $jabatan);
        $q->execute();
    }

    echo json_encode(["ok" => true]);
    exit;
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<title>Register Akun Perpustakaan</title>

<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

<style>
  body{
    font-family:Inter;
    background:#f3f6fb;
    margin:0;
    display:flex;
    justify-content:center;
    align-items:center;
    height:100vh
  }
  .card{
    background:#fff;
    padding:24px;
    border-radius:12px;
    width:380px;
    box-shadow:0 6px 20px rgba(0,0,0,.1)
  }
  input,select{
    width:100%;
    padding:10px;
    margin-top:6px;
    border:1px solid #d1d5db;
    border-radius:8px
  }
  button{
    width:100%;
    margin-top:14px;
    background:#2563eb;
    color:#fff;
    padding:10px;
    border-radius:8px;
    border:none;
    font-weight:600;
    cursor:pointer
  }
</style>
</head>

<body>

<div class="card">
  <h2>Buat Akun Baru</h2>
  <p style="font-size:14px;color:#555">Daftarkan akun Mahasiswa atau Petugas</p>

  <form id="registerForm">
    <label>Daftar sebagai</label>
    <select name="role" required>
      <option value="mahasiswa">Mahasiswa</option>
      <option value="petugas">Petugas</option>
    </select>

    <label>Nama Lengkap</label>
    <input type="text" name="name" required>

    <label>Username / NIM</label>
    <input type="text" name="username" required>

    <label>Jurusan / Departemen</label>
    <input type="text" name="dept" required>

    <label>Password</label>
    <input type="password" name="password" required>

    <button type="submit">Daftar</button>
  </form>

  <p style="margin-top:10px;font-size:14px">
    Sudah punya akun? <a href="login.php">Login</a>
  </p>
</div>

<script>
class RegisterController {
  constructor(formId) {
    this.form = document.getElementById(formId);
    this.form.addEventListener("submit", e => this.submit(e));
  }

  submit(e) {
    e.preventDefault();
    const formData = new FormData(this.form);

    fetch("register.php", {
      method: "POST",
      body: formData
    })
    .then(res => res.json())
    .then(res => {
      if (!res.ok) {
        alert("Gagal: " + res.msg);
        return;
      }

      alert("Registrasi berhasil!");
      window.location.href = "login.php";
    })
    .catch(() => alert("Error koneksi server"));
  }
}

new RegisterController("registerForm");
</script>

</body>
</html>
