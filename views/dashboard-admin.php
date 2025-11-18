<?php



$pagina = $_GET['pagina'] ?? 'panel-admin';
$baseRuta = "includes/admin/" . $pagina;

if (file_exists($baseRuta . ".php")) {
  $ruta = $baseRuta . ".php";
} elseif (file_exists($baseRuta . ".html")) {
  $ruta = $baseRuta . ".html";
} else {
  $ruta = null;
}



$titulo = $menu[$pagina]['texto'] ?? 'INICIO';

function activo($actual, $pagina)
{
  return $actual === $pagina
    ? 'bg-gradient-to-r from-indigo-600 to-purple-600 text-white shadow-md scale-[1.02]'
    : 'hover:bg-indigo-500 hover:text-white transition-all duration-300 text-gray-300';
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Panel Administrativo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://kit.fontawesome.com/a2e0d6d123.js" crossorigin="anonymous"></script>
</head>

<body class="flex min-h-screen bg-gray-100 font-sans">


  <!-- CONTENIDO PRINCIPAL -->
  <main class="flex-1 flex flex-col">

    <!-- HEADER -->
    <header class="relative h-48 bg-center bg-cover shadow-md"
            style="background-image: url('assets/img/img-background.png');">
      <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-indigo-900/60 to-transparent flex items-center px-10">
        <div class="flex items-center space-x-4 text-white">
          <div class="bg-white/20 p-3 rounded-xl">
            <i class="<?= $menu[$pagina]['icon'] ?? 'fa-solid fa-plus' ?> text-3xl"></i>
          </div>
          <h1 class="text-3xl font-bold tracking-wide"><?= strtoupper($titulo) ?></h1>
        </div>
      </div>
    </header>

    <!-- CONTENIDO -->
    <section class="p-8 bg-gray-50 flex-1 overflow-y-auto">
      <?php
      if ($ruta) {
        include $ruta;
      } else {
        echo "<div class='text-center text-gray-500 text-xl font-semibold mt-10'>
                <i class='fa-solid fa-triangle-exclamation text-3xl mb-3 text-red-500'></i><br>
                Página no encontrada
              </div>";
      }
      ?>
    </section>
  </main>

</body>
</html>
