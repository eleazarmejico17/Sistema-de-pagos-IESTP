<?php
require_once __DIR__ . '/../../../controller/admin-usuariosController.php';

$ctrl = new EstudiantesController();
$moduleParam = isset($_GET['modulo']) ? 'modulo' : 'pagina';
$validTargets = ['admin-usuarios', 'usuarios'];
$moduleValue = $_GET[$moduleParam] ?? 'admin-usuarios';

if (!in_array($moduleValue, $validTargets, true)) {
    $moduleValue = 'admin-usuarios';
}

$redirectUrl = sprintf('%s?%s=%s', basename($_SERVER['PHP_SELF']), $moduleParam, $moduleValue);
$status = $_GET['status'] ?? null;
$alerts = [];

if ($status === 'created') {
    $alerts[] = ['type' => 'success', 'text' => 'Estudiante registrado correctamente.'];
} elseif ($status === 'deleted') {
    $alerts[] = ['type' => 'success', 'text' => 'Estudiante eliminado correctamente.'];
} elseif ($status === 'error') {
    $alerts[] = ['type' => 'error', 'text' => 'No se pudo completar la operación.'];
}

$errors = [];
$previousData = [];

// Eliminar estudiante
if (isset($_GET['delete'])) {
    $deleteId = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

    if ($deleteId) {
        $ctrl->eliminar($deleteId);
        header("Location: {$redirectUrl}&status=deleted");
        exit;
    }

    $errors[] = 'El identificador del estudiante no es válido.';
}

$estudiantes = $ctrl->listar();

$oldValue = function (string $key) use ($previousData): string {
    return htmlspecialchars($previousData[$key] ?? '', ENT_QUOTES, 'UTF-8');
};
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://kit.fontawesome.com/a2e0d6d123.js" crossorigin="anonymous"></script>
</head>
<body>
    


<h2 class="text-2xl font-bold mb-6">Gestión de Estudiantes</h2>

<?php if (!empty($alerts)): ?>
    <?php foreach ($alerts as $alert): ?>
        <div class="mt-4 p-4 rounded-xl <?= $alert['type'] === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200' ?>">
            <?= htmlspecialchars($alert['text'], ENT_QUOTES, 'UTF-8') ?>
        </div>
    <?php endforeach; ?>
<?php endif; ?>

<?php if (!empty($errors)): ?>
    <div class="mt-4 p-4 rounded-xl bg-red-50 text-red-700 border border-red-200">
        <ul class="list-disc pl-5 space-y-1 text-sm text-left">
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>




<!-- TABLA -->
<div class="overflow-x-auto mt-6">
  <table class="w-full rounded-xl overflow-hidden shadow-lg">
    <thead>
      <tr class="bg-gradient-to-r from-blue-50 to-purple-50 border-b-2 border-blue-200">
        
        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-id-card mr-2"></i>ID
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-address-card mr-2"></i>DNI
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-user mr-2"></i>Nombre Completo
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-calendar mr-2"></i>Edad
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-venus-mars mr-2"></i>Sexo
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-check-circle mr-2"></i>Estado
        </th>

        <th class="px-6 py-4 text-left text-sm font-bold text-gray-700 uppercase tracking-wider">
          <i class="fas fa-cog mr-2"></i>Acciones
        </th>

      </tr>
    </thead>

    <tbody class="divide-y divide-gray-200">
      <?php foreach ($estudiantes as $e): ?>
      <tr class="hover:bg-blue-50 transition">

        <td class="px-6 py-4 text-gray-700 font-semibold">
          <?= $e['id'] ?>
        </td>

        <td class="px-6 py-4 text-gray-700">
          <?= $e['dni_est'] ?>
        </td>

        <td class="px-6 py-4 text-gray-700 font-medium">
          <?= $e['nombre_completo'] ?>
        </td>

        <td class="px-6 py-4 text-gray-700">
          <?= $e['edad'] ?> ---
        </td>

        <td class="px-6 py-4">
          <?php if ($e['sexo'] === "M"): ?>
            <span class="px-3 py-1 bg-blue-100 text-blue-700 text-xs rounded-full font-semibold">
              <i class="fas fa-mars mr-1"></i> Masculino
            </span>
          <?php else: ?>
            <span class="px-3 py-1 bg-pink-100 text-pink-700 text-xs rounded-full font-semibold">
              <i class="fas fa-venus mr-1"></i> Femenino
            </span>
          <?php endif; ?>
        </td>

        <td class="px-6 py-4">
          <?php if ($e['estado'] == 1): ?>
            <span class="px-3 py-1 bg-green-100 text-green-700 text-xs rounded-full font-semibold">
              <i class="fas fa-check mr-1"></i> Activo
            </span>
          <?php else: ?>
            <span class="px-3 py-1 bg-red-100 text-red-700 text-xs rounded-full font-semibold">
              <i class="fas fa-times mr-1"></i> Inactivo
            </span>
          <?php endif; ?>
        </td>

        <td class="px-6 py-4 flex gap-3">

          <!-- Botón Editar -->
          <a href="dashboard-admin.php?pagina=usuarios&edit=<?= $e['id'] ?>"
             class="text-blue-600 hover:text-blue-800 font-semibold">
            <i class="fas fa-edit"></i>
          </a>

          <!-- Botón Eliminar -->
          <a href="dashboard-admin.php?pagina=usuarios&delete=<?= $e['id'] ?>"
             class="text-red-600 hover:text-red-800 font-semibold">
            <i class="fas fa-trash-alt"></i>
          </a>

        </td>

      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

</body>
</html>


