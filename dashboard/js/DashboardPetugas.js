/* ==========================
   DashboardPetugas.js
========================== */

class DashboardPetugas {
  constructor() {
    this.currentUser = JSON.parse(sessionStorage.getItem("currentUser"));

    if (!this.currentUser || this.currentUser.role !== "petugas") {
      alert("Akses ditolak!");
      window.location.href = "../login/login.html";
      return;
    }

    this.userInfoEl = document.getElementById("userInfo");
    this.booksGrid = document.getElementById("booksGrid");
    this.requestsTable = document.getElementById("requestsTable");

    this.init();
  }

  /* ==========================
     INIT
  ========================== */
  init() {
    this.renderUserInfo();
    this.bindEvents();
    this.renderBooks();
    this.renderRequests();
  }

  renderUserInfo() {
    this.userInfoEl.textContent =
      `${this.currentUser.name} • ${this.currentUser.username}`;
  }

  bindEvents() {
    window.saveBook = () => this.saveBook();
    window.editBook = (id) => this.editBook(id);
    window.deleteBook = (id) => this.deleteBook(id);

    window.approve = (id) => this.updateRequest(id, "approve");
    window.reject = (id) => this.updateRequest(id, "reject");
    window.returnBook = (id) => this.returnBook(id);
    window.deleteRequest = (id) => this.deleteRequest(id);

    window.logout = () => this.logout();

    document.getElementById("image")
      .addEventListener("change", (e) => this.previewImage(e));
  }

  /* ==========================
     API
  ========================== */
  async fetchJSON(url, formData = null) {
    const opt = formData
      ? { method: "POST", body: formData }
      : {};

    const res = await fetch(url, opt);
    return res.json();
  }

  /* ==========================
     BUKU
  ========================== */
  async renderBooks() {
    const data = await this.fetchJSON("../backend/get_books.php");
    const books = data.books || [];

    this.booksGrid.innerHTML = "";

    books.forEach(b => {
      this.booksGrid.innerHTML += `
        <div class="card">
          <img class="cover" src="../${b.image}"
            onerror="this.src='https://via.placeholder.com/200x160'">
          <strong>${b.title}</strong>
          <div>${b.author}</div>
          <div>${b.category}</div>
          <div>Stok: ${b.stock}</div>
          <button class="btn" onclick="editBook(${b.id})">Edit</button>
          <button class="btn btn-danger" onclick="deleteBook(${b.id})">Hapus</button>
        </div>
      `;
    });
  }

  async saveBook() {
    const form = new FormData();
    ["title", "author", "category", "stock"]
      .forEach(id => form.append(id, document.getElementById(id).value));

    const file = document.getElementById("image").files[0];
    if (file) form.append("image", file);

    const res = await this.fetchJSON("../backend/save_book.php", form);

    if (res.ok) {
      alert("Buku berhasil disimpan");
      this.renderBooks();
    } else {
      alert(res.msg || "Gagal menyimpan buku");
    }
  }

  async editBook(id) {
    const books = (await this.fetchJSON("../backend/get_books.php")).books;
    const b = books.find(x => x.id == id);
    if (!b) return;

    const form = new FormData();
    form.append("id", id);
    form.append("title", prompt("Judul:", b.title));
    form.append("author", prompt("Pengarang:", b.author));
    form.append("category", prompt("Kategori:", b.category));
    form.append("stock", prompt("Stok:", b.stock));

    await this.fetchJSON("../backend/save_book.php", form);
    this.renderBooks();
  }

  async deleteBook(id) {
    if (!confirm("Hapus buku?")) return;

    const f = new FormData();
    f.append("id", id);
    await this.fetchJSON("../backend/delete_book.php", f);
    this.renderBooks();
  }

  /* ==========================
     REQUEST
  ========================== */
  async renderRequests() {
    const data = await this.fetchJSON("../backend/get_requests.php");
    const reqs = data.requests || [];

    this.requestsTable.innerHTML = "";

    reqs.forEach(r => {
      let aksi = "-";

      if (r.status === "Pending") {
        aksi = `
          <button class="btn" onclick="approve('${r.id}')">Terima</button>
          <button class="btn" onclick="reject('${r.id}')">Tolak</button>`;
      } else if (r.status === "Approved") {
        aksi = `<button class="btn" onclick="returnBook('${r.id}')">Kembalikan</button>`;
      } else if (r.status === "Returned") {
        aksi = `<button class="btn btn-danger" onclick="deleteRequest('${r.id}')">Hapus</button>`;
      }

      this.requestsTable.innerHTML += `
        <tr>
          <td>${r.studentName}<br><small>${r.studentNIM}</small></td>
          <td>${r.title}</td>
          <td>${r.due_date}</td>
          <td>${r.note || "-"}</td>
          <td>${r.status}</td>
          <td>${aksi}</td>
        </tr>
      `;
    });
  }

  async updateRequest(id, action) {
    const f = new FormData();
    f.append("id", id);
    f.append("action", action);
    await this.fetchJSON("../backend/update_request.php", f);
    this.renderRequests();
    this.renderBooks();
  }

  async returnBook(id) {
    const f = new FormData();
    f.append("id", id);
    f.append("action", "return");

    const res = await this.fetchJSON("../backend/update_request.php", f);

    if (res.fine > 0) {
      alert(`Denda: Rp ${res.fine.toLocaleString()}`);
    } else {
      alert("Pengembalian tepat waktu");
    }

    this.renderRequests();
    this.renderBooks();
  }

  async deleteRequest(id) {
    const f = new FormData();
    f.append("id", id);
    await this.fetchJSON("../backend/delete_request.php", f);
    this.renderRequests();
  }

  previewImage(e) {
    const reader = new FileReader();
    reader.onload = () => {
      const img = document.getElementById("preview");
      img.src = reader.result;
      img.style.display = "block";
    };
    reader.readAsDataURL(e.target.files[0]);
  }

  logout() {
    sessionStorage.removeItem("currentUser");
    window.location.href = "../login/login.html";
  }
}

/* ==========================
   INIT
========================== */
document.addEventListener("DOMContentLoaded", () => {
  new DashboardPetugas();
});
