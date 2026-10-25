(function () {
    "use strict";

    var year = document.getElementById("year");
    if (year) {
        year.textContent = String(new Date().getFullYear());
    }

    var title = document.getElementById("cms-title");
    var body = document.getElementById("cms-body");

    if (title && body) {
        fetch("/api/public/pages/home")
            .then(function (response) {
                if (!response.ok) {
                    throw new Error("not found");
                }
                return response.json();
            })
            .then(function (page) {
                if (page.title) {
                    title.textContent = page.title;
                }
                if (page.body) {
                    body.textContent = page.body;
                }
            })
            .catch(function () {
                body.textContent =
                    "No pude cargar el contenido del CMS. Levantá el servidor con: php -S localhost:9980 -t public";
            });
    }

    var form = document.getElementById("contact-form");
    if (!form) {
        return;
    }

    var status = document.getElementById("contact-status");

    form.addEventListener("submit", function (event) {
        event.preventDefault();

        if (status) {
            status.textContent = "Enviando…";
        }

        var payload = {
            name: form.elements.name.value.trim(),
            email: form.elements.email.value.trim(),
            message: form.elements.message.value.trim()
        };

        fetch("/api/inquiries", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify(payload)
        })
            .then(function (response) {
                return response.json().then(function (data) {
                    return { ok: response.ok, data: data };
                });
            })
            .then(function (result) {
                if (!result.ok) {
                    throw new Error(result.data.error || "No se pudo enviar.");
                }
                if (status) {
                    status.textContent = "¡Gracias! Te vamos a responder a la brevedad.";
                }
                form.reset();
            })
            .catch(function (error) {
                if (status) {
                    status.textContent = error.message;
                }
            });
    });
})();
