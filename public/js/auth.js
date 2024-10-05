const login_controller = new FormControl("login");
const login_username = $("#l-username");
const login_password = $("#l-password");
$(".form.login button[type=submit]").addEventListener("pointerup", async () => {
    const username = login_username.value.trim();
    const password = login_password.value.trim();

    const response = await AJAX.post("/auth/login", JSONHandler(), {
        body: JSON.stringify({ username, password })
    });

    if (response.error !== undefined) {
        login_controller.invalidate(response.error);
        return;
    }

    login_controller.clear();

    if (response.next !== undefined) {
        window.location.replace(response.next);
    }
});



const register_controller = new FormControl("register");
const register_username = $("#r-username");
const register_password = $("#r-password");
const register_password_again = $("#r-password-again");
$(".form.register button[type=submit]").addEventListener("pointerup", async () => {
    const username = register_username.value.trim();
    const password = register_password.value.trim();
    const password_again = register_password_again.value.trim();

    if (username === "" || password === "" || password_again === "") {
        register_controller.invalidate("All fields must be filled in.");
        return;
    }

    if (password !== password_again) {
        register_controller.invalidate("Passwords must match.");
        return;
    }

    const response = await AJAX.post("/auth/register", JSONHandler(), {
        body: JSON.stringify({ username, password })
    });

    if (response.error !== undefined) {
        register_controller.invalidate(response.error);
        return;
    }

    register_controller.clear();

    if (response.next !== undefined) {
        window.location.replace(response.next);
    }
});