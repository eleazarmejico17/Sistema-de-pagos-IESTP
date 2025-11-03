<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel Administrador</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="flex min-h-screen bg-gray-100 font-sans">

  <main class="flex-1">
    <section class="grid p-8 grid-cols-1 md:grid-cols-3 gap-6 mb-8">
      <a href="#" data-page="admin-bienestar" class="bg-lime-400 text-black rounded-xl p-6 flex items-center justify-between shadow-md hover:shadow-lg transition">
        <span class="text-xl font-semibold">BIENESTAR</span>
        <span class="text-3xl">👤</span>
      </a>

      <a href="#" data-page="admin-direccion" class="bg-sky-400 text-black rounded-xl p-6 flex items-center justify-between shadow-md hover:shadow-lg transition">
        <span class="text-xl font-semibold">DIRECCIÓN</span>
        <span class="text-3xl">🏢</span>
      </a>

      <a href="#" data-page="admin-usuarios" class="bg-blue-800 text-white rounded-xl p-6 flex items-center justify-between shadow-md hover:shadow-lg transition">
        <span class="text-xl font-semibold">USUARIOS</span>
        <span class="text-3xl">👥</span>
      </a>
    </section>

    <section id="contenido">
      <h2 class="text-center text-gray-600">Selecciona una sección para comenzar</h2>
    </section>
  </main>

  <script>
    document.addEventListener("DOMContentLoaded", () => {
      const links = document.querySelectorAll("a[data-page]");
      const contenedor = document.getElementById("contenido");

      links.forEach(link => {
        link.addEventListener("click", async (e) => {
          e.preventDefault();
          const page = link.getAttribute("data-page");
          const ruta = `admin/${page}.html`; 

          try {
            const respuesta = await fetch(ruta);
            if (!respuesta.ok) throw new Error("Archivo no encontrado");

            const html = await respuesta.text();
            contenedor.innerHTML = html;
          } catch (error) {
            contenedor.innerHTML = `<p class="text-center text-red-500">Error al cargar ${ruta}</p>`;
          }
        });
      });
    });
  </script>

</body>
</html>
