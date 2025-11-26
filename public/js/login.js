// js/login.js

const usuarios = [
  { usuario: "admin", password: "123456", rol: "admin" },
  { usuario: "direccion", password: "dir123", rol: "direccion" },
  { usuario: "bienestar", password: "bien123", rol: "bienestar" },
  { usuario: "usuario", password: "user123", rol: "usuario" }
];

document.getElementById("loginForm").addEventListener("submit", function (e) {
  e.preventDefault();

  const usuario = document.getElementById("usuario").value.trim();
  const password = document.getElementById("password").value.trim();

  const user = usuarios.find(u => u.usuario === usuario && u.password === password);

  if (user) {
    // Redirigir según rol
    const redirectMap = {
      admin: "../views/dashboard-admin.php",
      direccion: "../views/dashboard-direccion.php",
      bienestar: "../views/dashboard-bienestar.php",
      usuario: "../views/dashboard-usuario.php"
    };

    window.location.href = redirectMap[user.rol];
  } else {
    mostrarToast("Usuario o contraseña incorrectos", "error");
  }
});

function mostrarToast(mensaje, tipo) {
  const toast = document.getElementById("toast");
  toast.textContent = mensaje;
  toast.className = `fixed top-6 right-6 px-5 py-3 rounded-lg text-white font-medium shadow-lg opacity-0 transform -translate-y-4 transition-all duration-300 z-50 show ${tipo}`;
  setTimeout(() => toast.classList.remove("show"), 3000);
}