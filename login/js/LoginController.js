import AuthService from "./AuthService.js";

class LoginController {

  constructor() {
    this.auth = new AuthService();
    this.form = document.getElementById("loginForm");
    this.init();
  }

  init() {
    this.form.addEventListener("submit", e => this.handleLogin(e));
  }

  async handleLogin(e) {
    e.preventDefault();

    const role = document.getElementById("role").value;
    const username = document.getElementById("username").value;
    const password = document.getElementById("password").value;

    const result = await this.auth.login(username, password, role);

    if (result.ok) {
      sessionStorage.setItem("currentUser", JSON.stringify(result.user));

      location.href =
        result.user.role === "mahasiswa"
          ? "../dashboard/dashboardMahasiswa.html"
          : "../dashboardpetugas/dashboardPetugas.html";
    } else {
      alert(result.msg);
    }
  }
}

new LoginController();
