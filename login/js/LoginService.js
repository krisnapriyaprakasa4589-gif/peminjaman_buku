export default class LoginService {

    static async login(role, username, password) {
        const form = new FormData();
        form.append("role", role);
        form.append("username", username);
        form.append("password", password);

        const response = await fetch("../backend/login.php", {
            method: "POST",
            body: form
        });

        return response.json();
    }

}
