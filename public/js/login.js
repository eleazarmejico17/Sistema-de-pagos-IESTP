// js/login.js
(() => {
  "use strict";

  const usuarios = [
    { usuario: "admin",     password: "123456",  rol: "admin"     },
    { usuario: "direccion", password: "dir123",  rol: "direccion" },
    { usuario: "bienestar", password: "bien123", rol: "bienestar" },
    { usuario: "usuario",   password: "user123", rol: "usuario"   }
  ];

  const redirectMap = {
    admin:     "../views/dashboard-admin.php",
    direccion: "../views/dashboard-direccion.php",
    bienestar: "../views/dashboard-bienestar.php",
    usuario:   "../views/dashboard-usuario.php"
  };

  function mostrarToast(mensaje, tipo = "error") {
    const toast = document.getElementById("toast");
    if (!toast) return;                       // si no hay toast, no pasa nada
    toast.textContent = mensaje;
    toast.className = `fixed top-6 right-6 px-5 py-3 rounded-lg text-white font-medium shadow-lg opacity-0 transform -translate-y-4 transition-all duration-300 z-50 show ${tipo}`;
    setTimeout(() => toast.classList.remove("show"), 3000);
  }

  function normalizar(txt) {
    return typeof txt === "string" ? txt.trim().toLowerCase() : "";
  }

  document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("loginForm");
    if (!form) return;                        // evita error si el script se carga fuera de login.html

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      try {
        const usuario  = normalizar(document.getElementById("usuario")?.value);
        const password = normalizar(document.getElementById("password")?.value);

        const user = usuarios.find(
          u => normalizar(u.usuario) === usuario && normalizar(u.password) === password
        );

        if (!user) {
          mostrarToast("Usuario o contraseña incorrectos", "error");
          return;
        }

        const destino = redirectMap[user.rol];
        if (!destino) {                       // rol desconocido (nunca debería pasar)
          mostrarToast("Rol no configurado", "error");
          return;
        }

        window.location.href = destino;       // redirección segura
      } catch (err) {
        console.error(err);
        mostrarToast("Error inesperado. Inténtalo de nuevo.", "error");
      }
    });
  });
})();