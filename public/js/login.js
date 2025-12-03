// js/login.js
(() => {
  "use strict";

  /* ----------  UTILS  ---------- */
  function mostrarToast(mensaje, tipo = "error") {
    const toast = document.getElementById("toast");
    if (!toast) return;
    toast.textContent = mensaje;
    toast.className = `fixed top-6 right-6 px-5 py-3 rounded-lg text-white font-medium shadow-lg opacity-0 transform -translate-y-4 transition-all duration-300 z-50 show ${tipo}`;
    setTimeout(() => toast.classList.remove("show"), 3000);
  }

  function normalizar(txt) {
    return typeof txt === "string" ? txt.trim().toLowerCase() : "";
  }

  /* ----------  INIT  ---------- */
  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    if (!form) return;

    form.addEventListener("submit", async (e) => {
      e.preventDefault();

      try {
        const usuario  = normalizar(document.getElementById("usuario")?.value);
        const password = normalizar(document.getElementById("password")?.value);

        const res = await fetch("login.php", {
          method : "POST",
          headers: { "Content-Type": "application/json" },
          body   : JSON.stringify({ usuario, password })
        });

        const data = await res.json();

        if (!res.ok) {                  // 401 o cualquier otro código de error
          mostrarToast(data.error || "Usuario o contraseña incorrectos", "error");
          return;
        }

        // Redirigimos a la ruta que nos devuelve el servidor
        window.location.href = data.redirect;

      } catch (err) {
        console.error(err);
        mostrarToast("Error inesperado. Inténtalo de nuevo.", "error");
      }
    });
  });
})();