<?php
// ================== LOGIN PROCESS ==================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    header("Content-Type: application/json");
    require "db.php";

    $role     = $_POST['role'] ?? '';
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        echo json_encode(["ok" => false, "msg" => "Harap isi semua field"]);
        exit;
    }

    $q = $conn->prepare("SELECT * FROM users WHERE username=? AND role=? LIMIT 1");
    $q->bind_param("ss", $username, $role);
    $q->execute();
    $user = $q->get_result()->fetch_assoc();

    if (!$user) {
        echo json_encode(["ok" => false, "msg" => "User tidak ditemukan"]);
        exit;
    }

    if (!password_verify($password, $user['password'])) {
        echo json_encode(["ok" => false, "msg" => "Password salah"]);
        exit;
    }

    echo json_encode(["ok" => true, "user" => $user]);
    exit;
}
?>

<!doctype html>
<html lang="id">
<head>
  <meta charset="utf-8" />
  <meta name="viewport" content="width=device-width,initial-scale=1" />
  <title>Login - Universitas Jenderal Ahmad Yani</title>

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">

  <style>
    :root{
      --bg:#f3f6fb;
      --card:#fff;
      --accent:#2563eb;
      --muted:#6b7280;
      --danger:#ef4444;
      font-family:Inter,system-ui,sans-serif
    }
    *{box-sizing:border-box}
    html,body{
      height:100%;
      margin:0;
      background:linear-gradient(180deg,var(--bg),#eef4ff);
    }
    .container{
      min-height:100vh;
      display:flex;
      align-items:center;
      justify-content:center;
      padding:32px
    }
    .card{
      width:100%;
      max-width:420px;
      background:var(--card);
      border-radius:12px;
      box-shadow:0 6px 30px rgba(16,24,40,.08);
      padding:28px
    }
    .brand{display:flex;gap:12px;margin-bottom:18px}
    .logo{
      width:48px;
      height:48px;
      border-radius:10px;
      background:linear-gradient(135deg,var(--accent),#4f46e5);
      display:flex;
      align-items:center;
      justify-content:center;
      color:#fff;
      font-weight:700
    }
    h1{font-size:20px;margin:0}
    .lead{margin:6px 0 18px;color:var(--muted);font-size:14px}
    form{display:grid;gap:12px}
    label{font-size:13px}
    input,select{
      padding:12px 14px;
      border:1px solid #e6e9ef;
      border-radius:8px
    }
    .btn{
      margin-top:6px;
      padding:12px;
      background:var(--accent);
      color:#fff;
      border:none;
      border-radius:10px;
      font-weight:600;
      cursor:pointer
    }
    .switch{text-align:center;margin-top:14px}
    .error{
      color:var(--danger);
      font-size:13px;
      display:none
    }
  </style>
</head>

<body>
<div class="container">
  <div class="card">

    <div class="brand">
      <div class="logo">UP</div>
      <div>
        <h1>Login Universitas Jenderal Ahmad Yani</h1>
        <p class="lead">Masuk sebagai Mahasiswa atau Petugas</p>
      </div>
    </div>

    <form id="loginForm">
      <div>
        <label>Masuk sebagai</label>
        <select id="role">
          <option value="mahasiswa">Mahasiswa</option>
          <option value="petugas">Petugas</option>
        </select>
      </div>

      <div>
        <label>Username / NIM</label>
        <input id="username" type="text">
      </div>

      <div>
        <label>Password</label>
        <input id="password" type="password">
      </div>

      <button class="btn">Masuk</button>
      <div id="error" class="error"></div>
    </form>

    <div class="switch">
      Belum punya akun? <a href="register.html">Daftar</a>
    </div>

  </div>
</div>

<script>
class LoginController {
  constructor() {
    this.form = document.getElementById("loginForm");
    this.errorBox = document.getElementById("error");
    this.form.addEventListener("submit", e => this.submit(e));
  }

  submit(e) {
    e.preventDefault();
    this.errorBox.style.display = "none";

    const formData = new FormData();
    formData.append("role", document.getElementById("role").value);
    formData.append("username", document.getElementById("username").value.trim());
    formData.append("password", document.getElementById("password").value.trim());

    fetch("login.php", { method: "POST", body: formData })
      .then(res => res.json())
      .then(res => {
        if (!res.ok) {
          this.errorBox.textContent = res.msg;
          this.errorBox.style.display = "block";
          return;
        }

        sessionStorage.setItem("currentUser", JSON.stringify(res.user));

        location.href = res.user.role === "mahasiswa"
          ? "../dashboard/dashboardMahasiswa.html"
          : "../dashboardpetugas/dashboardPetugas.html";
      })
      .catch(() => {
        this.errorBox.textContent = "Gagal terhubung ke server";
        this.errorBox.style.display = "block";
      });
  }
}

new LoginController();
</script>

</body>
</html>
