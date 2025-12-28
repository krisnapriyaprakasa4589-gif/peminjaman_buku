class DashboardMahasiswa {
  constructor() {
    this.currentUser = JSON.parse(sessionStorage.getItem("currentUser"));

    // Proteksi akses
    if (!this.currentUser || this.currentUser.role !== "mahasiswa") {
      alert("Akses ditolak!");
      location.href = "../login/login.html";
      return;
    }

    // DOM
    this.userInfoEl = document.getElementById("userInfo");
    this.bookListEl = document.getElementById("bookList");
    this.requestTableBody = document.querySelector("#myRequestsTable tbody");
    this.searchInput = document.getElementById("searchInput");
    this.searchBtn = document.getElementById("searchBtn");

    this.init();
  }

  /* ======================
     INIT
  ====================== */
  init() {
    this.renderUserInfo();
    this.bindEvents();
    this.renderBooks();
    this.renderMyRequests();
  }

  renderUserInfo() {
    this.userInfoEl.textContent = `${this.currentUser.name} • ${
      this.currentUser.nim || this.currentUser.username
    } • ${this.currentUser.dept || ""}`;
  }

  bindEvents() {
    this.searchBtn.addEventListener("click", () => this.renderBooks());
    this.searchInput.addEventListener("keypress", (e) => {
      if (e.key === "Enter") this.renderBooks();
    });

    // expose ke HTML
    window.applyRequest = (id) => this.applyRequest(id);
    window.returnBook = (id) => this.returnBook(id);
    window.logout = () => this.logout();
  }

  /* ======================
     API
  ====================== */
  async getBooks(query = "") {
    const url =
      "../backend/get_books.php" +
      (query ? `?q=${encodeURIComponent(query)}` : "");

    const res = await fetch(url);
    const data = await res.json();
    return data.books || [];
  }

  async getRequests() {
    const nim = this.currentUser.nim || this.currentUser.username;
    const res = await fetch(
      `../backend/get_requests.php?student_nim=${encodeURIComponent(nim)}`
    );
    const data = await res.json();
    return data.requests || [];
  }

  /* ======================
     RENDER BUKU
  ====================== */
  async renderBooks() {
    const query = this.searchInput.value;
    const books = await this.getBooks(query);
    this.bookListEl.innerHTML = "";

    const availableBooks = books.filter((b) => b.stock > 0);

    if (availableBooks.length === 0) {
      this.bookListEl.innerHTML =
        '<div class="card">Tidak ada buku tersedia</div>';
      return;
    }

    availableBooks.forEach((book) => {
      const card = document.createElement("div");
      card.className = "card";
      card.innerHTML = `
        <img class="cover" src="../${book.image}"
          onerror="this.src='https://via.placeholder.com/200x280'">
        <strong>${book.title}</strong>
        <div>${book.author || "-"}</div>
        <div class="pill">${book.category || ""}</div>
        <div>Stok: <strong>${book.stock}</strong></div>
        <button class="btn" style="width:100%;margin-top:10px"
          onclick="applyRequest(${book.id})">
          Ajukan Peminjaman
        </button>
      `;
      this.bookListEl.appendChild(card);
    });
  }

  /* ======================
     AJUKAN PEMINJAMAN
  ====================== */
  async applyRequest(bookId) {
    const books = await this.getBooks();
    const book = books.find((b) => Number(b.id) === Number(bookId));

    if (!book || book.stock <= 0) {
      alert("Buku tidak tersedia!");
      return;
    }

    const dueDate = prompt("Masukkan tanggal kembali (YYYY-MM-DD):");
    if (!dueDate) {
      alert("Tanggal wajib diisi!");
      return;
    }

    const note = prompt("Catatan (opsional):") || "";

    const form = new FormData();
    form.append("book_id", bookId);
    form.append("judul_buku", book.title);
    form.append(
      "mahasiswa_nim",
      this.currentUser.nim || this.currentUser.username
    );
    form.append("mahasiswa_nama", this.currentUser.name);
    form.append("mahasiswa_dept", this.currentUser.dept || "");
    form.append("due_date", dueDate);
    form.append("note", note);

    const res = await fetch("../backend/create_request.php", {
      method: "POST",
      body: form,
    });

    const data = await res.json();

    if (data.ok) {
      alert("Permintaan peminjaman berhasil dikirim");
      this.renderBooks();
      this.renderMyRequests();
    } else {
      alert("Gagal: " + (data.msg || "Unknown error"));
    }
  }

  /* ======================
     RENDER REQUEST
  ====================== */
  async renderMyRequests() {
    const requests = await this.getRequests();
    this.requestTableBody.innerHTML = "";

    if (requests.length === 0) {
      this.requestTableBody.innerHTML =
        '<tr><td colspan="5">Belum ada peminjaman</td></tr>';
      return;
    }

    requests.forEach((r) => {
      const action =
        r.status === "Approved"
          ? `<button class="btn" onclick="returnBook(${r.id})">Kembalikan</button>`
          : "-";

      this.requestTableBody.innerHTML += `
        <tr>
          <td>${r.title}</td>
          <td>${r.due_date || "-"}</td>
          <td>${r.note || "-"}</td>
          <td>${r.status}</td>
          <td>${action}</td>
        </tr>
      `;
    });
  }

  /* ======================
     KEMBALIKAN BUKU
  ====================== */
  async returnBook(id) {
    if (!confirm("Yakin ingin mengembalikan buku?")) return;

    const form = new FormData();
    form.append("id", id);
    form.append("action", "return");

    const res = await fetch("../backend/update_request.php", {
      method: "POST",
      body: form,
    });

    const data = await res.json();

    if (data.ok) {
      alert("Buku berhasil dikembalikan");
      this.renderBooks();
      this.renderMyRequests();
    } else {
      alert("Gagal: " + (data.msg || "Unknown error"));
    }
  }

  /* ======================
     LOGOUT
  ====================== */
  logout() {
    sessionStorage.removeItem("currentUser");
    location.href = "../login/login.html";
  }
}

/* ======================
   INIT
====================== */
document.addEventListener("DOMContentLoaded", () => {
  new DashboardMahasiswa();
});
