<?php
$pagina = $_GET['pagina'] ?? 'usuario-solicitud';
$ruta = "includes/usuario/$pagina.html";

/* --- Configuración central del menú --- */
$menu = [
  'usuario-solicitud'   => ['icon' => '➕', 'texto' => 'NUEVO'],
  'notificaciones'      => ['icon' => '🔔', 'texto' => 'NOTIFICACIONES'],
  'usuario-metodo-pago' => ['icon' => '💳', 'texto' => 'MÉTODO DE PAGO'],
  'pagos'               => ['icon' => '📄', 'texto' => 'PAGOS'],
  'comprobantes'        => ['icon' => '📁', 'texto' => 'COMPROBANTES']
];

$titulo = $menu[$pagina]['texto'] ?? 'INICIO';

function activo($actual, $pagina) {
  return $actual === $pagina
    ? 'bg-blue-500 text-white rounded-lg px-3 py-2'
    : 'hover:text-blue-400 px-3 py-2';
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title><?= $titulo ?></title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="flex min-h-screen bg-gray-100 font-sans">

  <aside class="w-64 bg-[#0b1437] text-white flex flex-col justify-between min-h-screen">
    <div>
      <div class="flex items-center justify-center py-6">
        <img src="assets/img/logo.png"
             alt="Logo" class="w-24 h-24 rounded-full border-4 border-white">
      </div>

      <nav class="px-6 space-y-2">
        <?php foreach ($menu as $key => $item): ?>
          <a href="?pagina=<?= $key ?>"
             class="flex items-center space-x-2 <?= activo($key, $pagina) ?>">
            <span><?= $item['icon'] ?></span>
            <span><?= $item['texto'] ?></span>
          </a>
        <?php endforeach; ?>
      </nav>
    </div>

    <div class="px-6 py-4 border-t border-gray-600">
      <a href="#" class="flex items-center space-x-2 hover:text-red-400"><span>⏻</span><span>SALIR</span></a>
    </div>
  </aside>

  <main class="flex-1">
    <header class="bg-cover bg-center h-40 relative" style="background-image: url('assets/img/img-background.jpeg');">
      <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
        <div class="flex items-center space-x-3">
          <span class="text-3xl text-white"><?= $menu[$pagina]['icon'] ?? '＋' ?></span>
          <h1 class="text-3xl text-white font-bold"><?= strtoupper($titulo) ?></h1>
        </div>
      </div>
    </header>

    <section class="p-6">
      <?php
      if (file_exists($ruta)) {
          include $ruta;
      } else {
          echo "<h2 class='text-center text-gray-600 mt-10 text-lg font-semibold'>Página no encontrada</h2>";
      }
      ?>
    </section>
  </main>

</body>
</html>
