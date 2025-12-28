export default class AuthService {

  async login(username, password, role) {
    const f = new FormData();
    f.append("username", username);
    f.append("password", password);
    f.append("role", role);

    const res = await fetch("../backend/login.php", {
      method: "POST",
      body: f
    });

    return res.json();
  }

  async register(data) {
    const f = new FormData();
    Object.keys(data).forEach(k => f.append(k, data[k]));

    const res = await fetch("../backend/register.php", {
      method: "POST",
      body: f
    });

    return res.json();
  }

  logout() {
    sessionStorage.removeItem("currentUser");
    window.location.href = "../login/login.html";
  }
}
