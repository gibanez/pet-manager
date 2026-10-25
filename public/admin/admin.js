(function () {
    "use strict";

    var TOKEN_KEY = "cms_token";
    var loginView = document.getElementById("login-view");
    var appView = document.getElementById("app-view");
    var logoutBtn = document.getElementById("logout");
    var loginForm = document.getElementById("login-form");
    var loginError = document.getElementById("login-error");
    var pagesBody = document.getElementById("pages-body");
    var pagesEmpty = document.getElementById("pages-empty");
    var formPanel = document.getElementById("page-form-panel");
    var formTitle = document.getElementById("page-form-title");
    var pageForm = document.getElementById("page-form");
    var cancelBtn = document.getElementById("cancel-page");
    var formStatus = document.getElementById("page-form-status");
    var newBtn = document.getElementById("new-page");

    function token() {
        return localStorage.getItem(TOKEN_KEY);
    }

    function api(path, options) {
        options = options || {};

        var headers = { "Content-Type": "application/json" };
        var current = token();

        if (current) {
            headers.Authorization = "Bearer " + current;
        }

        return fetch(path, {
            method: options.method || "GET",
            headers: headers,
            body: options.body ? JSON.stringify(options.body) : undefined
        }).then(function (response) {
            if (response.status === 401 && !options.ignore401) {
                logout();
                throw new Error("Sesión expirada.");
            }

            if (response.status === 204) {
                return null;
            }

            return response.json().then(function (data) {
                if (!response.ok) {
                    throw new Error(data.error || "Ocurrió un error.");
                }
                return data;
            });
        });
    }

    function showLogin() {
        loginView.hidden = false;
        appView.hidden = true;
        logoutBtn.hidden = true;
    }

    function showApp() {
        loginView.hidden = true;
        appView.hidden = false;
        logoutBtn.hidden = false;
        loadPages();
    }

    function logout() {
        localStorage.removeItem(TOKEN_KEY);
        showLogin();
    }

    function loadPages() {
        api("/api/pages")
            .then(function (data) {
                var pages = data.pages || [];
                pagesBody.innerHTML = "";
                pagesEmpty.hidden = pages.length > 0;

                pages.forEach(function (page) {
                    var row = document.createElement("tr");
                    row.innerHTML =
                        '<td><code></code></td><td></td><td></td><td class="row-actions"></td>';

                    row.querySelector("code").textContent = page.slug;
                    row.querySelectorAll("td")[1].textContent = page.title;
                    row.querySelectorAll("td")[2].textContent =
                        page.status === "active" ? "Activa" : "Inactiva";

                    var actions = row.querySelector(".row-actions");

                    var edit = document.createElement("button");
                    edit.type = "button";
                    edit.className = "btn btn-ghost";
                    edit.textContent = "Editar";
                    edit.addEventListener("click", function () {
                        openForm(page);
                    });

                    var remove = document.createElement("button");
                    remove.type = "button";
                    remove.className = "btn btn-danger";
                    remove.textContent = "Borrar";
                    remove.addEventListener("click", function () {
                        removePage(page);
                    });

                    actions.appendChild(edit);
                    actions.appendChild(remove);
                    pagesBody.appendChild(row);
                });
            })
            .catch(function (error) {
                pagesEmpty.hidden = false;
                pagesEmpty.textContent = error.message;
            });
    }

    function openForm(page) {
        formPanel.hidden = false;
        formTitle.textContent = page ? "Editar página" : "Nueva página";
        pageForm.elements.id.value = page ? page.id : "";
        pageForm.elements.slug.value = page ? page.slug : "";
        pageForm.elements.title.value = page ? page.title : "";
        pageForm.elements.body.value = page ? page.body : "";
        pageForm.elements.status.value = page ? page.status : "active";
        formStatus.textContent = "";
        pageForm.elements.slug.focus();
    }

    function closeForm() {
        formPanel.hidden = true;
        pageForm.reset();
        pageForm.elements.id.value = "";
        formStatus.textContent = "";
    }

    function removePage(page) {
        if (!window.confirm('¿Borrar "' + page.title + '"?')) {
            return;
        }

        api("/api/pages/" + encodeURIComponent(page.id), { method: "DELETE" })
            .then(loadPages)
            .catch(function (error) {
                window.alert(error.message);
            });
    }

    loginForm.addEventListener("submit", function (event) {
        event.preventDefault();
        loginError.textContent = "";

        api("/api/auth/login", {
            method: "POST",
            ignore401: true,
            body: {
                email: loginForm.elements.email.value.trim(),
                password: loginForm.elements.password.value
            }
        })
            .then(function (data) {
                localStorage.setItem(TOKEN_KEY, data.token);
                showApp();
            })
            .catch(function (error) {
                loginError.textContent = error.message;
            });
    });

    pageForm.addEventListener("submit", function (event) {
        event.preventDefault();

        var id = pageForm.elements.id.value;
        var payload = {
            slug: pageForm.elements.slug.value.trim(),
            title: pageForm.elements.title.value.trim(),
            body: pageForm.elements.body.value,
            status: pageForm.elements.status.value
        };

        formStatus.textContent = "Guardando…";

        var request = id
            ? api("/api/pages/" + encodeURIComponent(id), { method: "PUT", body: payload })
            : api("/api/pages", { method: "POST", body: payload });

        request
            .then(function () {
                closeForm();
                loadPages();
            })
            .catch(function (error) {
                formStatus.textContent = error.message;
            });
    });

    newBtn.addEventListener("click", function () {
        openForm(null);
    });

    cancelBtn.addEventListener("click", closeForm);
    logoutBtn.addEventListener("click", logout);

    if (token()) {
        showApp();
    } else {
        showLogin();
    }
})();
