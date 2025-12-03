
<?php
require_once __DIR__ . '/../../../config/conexion-local.php';
$pdo = Conexion::getInstance()->getConnection();

$stmt = $pdo->query("SELECT * FROM tipo_pago ORDER BY id DESC");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pagos - IESTP</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="flex bg-gray-100 min-h-screen">
  <main class="flex-1 p-8">
    <div class="bg-white shadow rounded-xl p-6 overflow-x-auto">
            <table class="min-w-full bg-white border">
                <thead class="bg-gray-200 text-gray-700">
                    <tr>
                        <th class="py-2 px-3 border">ID</th>
                        <th class="py-2 px-3 border">Nombre</th>
                        <th class="py-2 px-3 border">Descripción</th>
                        <th class="py-2 px-3 border">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($lista as $item): ?>
                        <tr class="border hover:bg-gray-50">
                            <td class="py-2 px-3 border"><?= $item['id'] ?></td>
                            <td class="py-2 px-3 border"><?= $item['nombre'] ?></td>
                            <td class="py-2 px-3 border"><?= $item['descripcion'] ?></td>
                            <td class="py-2 px-3 border text-center">

                            <a href="#"
                            class="px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
                            Pagar
                            </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
    </div>
  </main>
  <script>
    (function(){
      // Encuentra todos los enlaces que apunten (o contengan) 'usuario-metodo-pago.html'
      const links = Array.from(document.querySelectorAll('a')).filter(a => (a.getAttribute('href')||'').includes('usuario-metodo-pago.html'));
      links.forEach(link => {
        // Asegurar que el href apunte de forma absoluta a la ruta dentro del proyecto
        try{
          const absolutePath = window.location.origin + '/Sistema-de-pagos-IESTP/views/includes/usuario/usuario-metodo-pago.html';
          link.setAttribute('href', absolutePath);
        }catch(e){ /* ignore */ }

        link.addEventListener('click', function(e){
          e.preventDefault();
          const tr = this.closest('tr');
          if(!tr){
            // Si no hay fila, redirige de forma normal
            const fallback = resolveHref(this.getAttribute('href'));
            window.location.assign(fallback);
            return;
          }
          const cells = tr.querySelectorAll('td');
          const concept = (cells[1] && cells[1].textContent.trim()) || '';
          const uit = (cells[2] && cells[2].textContent.trim()) || '';
          const params = new URLSearchParams({ concept: concept, uit: uit });

          // Construir URL absoluta de destino de forma robusta
          const base = resolveHref(this.getAttribute('href'));
          const dest = base + (base.indexOf('?') === -1 ? '?' : '&') + params.toString();

          // Depuración ligera (ver en consola del navegador)
          console.log('Redirigiendo a:', dest);

          // Intentar la redirección
          try {
            window.location.assign(dest);
          } catch (err) {
            console.error('Redirección fallida, abriendo en nueva pestaña:', err);
            window.open(dest, '_self');
          }
        });
      });

      function resolveHref(href){
        // Si ya es absoluta (empieza por / o http), devolverla tal cual
        if(!href) return window.location.href;
        if(/^https?:\/\//i.test(href) || href.charAt(0) === '/') return new URL(href, window.location.origin).toString();

        // Si es relativo, resolverlo usando la ubicación actual
        try{
          return new URL(href, window.location.href).toString();
        }catch(e){
          // Fallback construyendo a partir del path actual
          const path = window.location.pathname;
          const basePath = path.substring(0, path.lastIndexOf('/') + 1);
          return window.location.origin + basePath + href;
        }
      }
    })();
  </script>
</body>
</html>
