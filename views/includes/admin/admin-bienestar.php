<?php
require_once __DIR__ . '/../../../controller/admin-bienestarController.php';

$ctrl = new BienestarController();
$moduleParam = isset($_GET['modulo']) ? 'modulo' : 'pagina';
$validTargets = ['admin-bienestar', 'bienestar'];
$moduleValue = $_GET[$moduleParam] ?? 'admin-bienestar';

if (!in_array($moduleValue, $validTargets, true)) {
    $moduleValue = 'admin-bienestar';
}

$redirectUrl = sprintf('%s?%s=%s', basename($_SERVER['PHP_SELF']), $moduleParam, $moduleValue);
$status = $_GET['status'] ?? null;
$alerts = [];

if ($status === 'created') {
    $alerts[] = ['type' => 'success', 'text' => 'Empleado registrado correctamente.'];
} elseif ($status === 'deleted') {
    $alerts[] = ['type' => 'success', 'text' => 'Empleado eliminado correctamente.'];
} elseif ($status === 'error') {
    $alerts[] = ['type' => 'error', 'text' => 'Ocurrió un problema al procesar la solicitud.'];
}

$errors = [];
$previousData = [];

// Crear empleado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dni_emp'])) {
    $input = filter_input_array(INPUT_POST, [
        'dni_emp'        => FILTER_SANITIZE_NUMBER_INT,
        'apnom_emp'      => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'sex_emp'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'cel_emp'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'mailp_emp'      => FILTER_SANITIZE_EMAIL,
        'maili_emp'      => FILTER_SANITIZE_EMAIL,
        'fecnac_emp'     => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'dir_emp'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ubigeodir_emp'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ubigeonac_emp'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'cargo_emp'      => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'estado'         => FILTER_SANITIZE_NUMBER_INT,
        'cond_emp'       => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'id_progest'     => FILTER_SANITIZE_NUMBER_INT,
        'fecinc_emp'     => FILTER_SANITIZE_FULL_SPECIAL_CHARS
    ], true);

    $previousData = array_map(static function ($value) {
        return is_string($value) ? trim($value) : $value;
    }, $input ?? []);

    if (empty($previousData['dni_emp']) || strlen($previousData['dni_emp']) !== 8) {
        $errors[] = 'El DNI debe tener exactamente 8 dígitos.';
    }

    if (empty($previousData['apnom_emp'])) {
        $errors[] = 'Los apellidos y nombres son obligatorios.';
    }

    if (!empty($previousData['mailp_emp']) && !filter_var($previousData['mailp_emp'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo personal no es válido.';
    }

    if (!empty($previousData['maili_emp']) && !filter_var($previousData['maili_emp'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo institucional no es válido.';
    }

    if (empty($errors)) {
        if ($ctrl->crear($previousData, $_FILES)) {
            header("Location: {$redirectUrl}&status=created");
            exit;
        }

        $errors[] = 'No se pudo registrar el empleado, intenta nuevamente.';
    }
}

// Eliminar empleado
if (isset($_GET['delete'])) {
    $deleteId = filter_input(INPUT_GET, 'delete', FILTER_SANITIZE_NUMBER_INT);

    if ($deleteId) {
        $ctrl->eliminar($deleteId);
        header("Location: {$redirectUrl}&status=deleted");
        exit;
    }

    $errors[] = 'El identificador del empleado no es válido.';
}

$empleados = $ctrl->listar();
$uploadsPath = '../uploads/empleados/';

$oldValue = function (string $key) use ($previousData): string {
    return htmlspecialchars($previousData[$key] ?? '', ENT_QUOTES, 'UTF-8');
};
?>

<h2 class="text-2xl font-bold mb-6">Gestión de Personal Bienestar</h2>

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

<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 shadow rounded-xl mt-6">

    <input type="text" name="dni_emp" placeholder="DNI" required maxlength="8" value="<?= $oldValue('dni_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="apnom_emp" placeholder="Apellidos y Nombres" required value="<?= $oldValue('apnom_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <select name="sex_emp" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
        <option value="M" <?= $oldValue('sex_emp') === 'M' ? 'selected' : '' ?>>Masculino</option>
        <option value="F" <?= $oldValue('sex_emp') === 'F' ? 'selected' : '' ?>>Femenino</option>
    </select>

    <input type="text" name="cel_emp" placeholder="Celular" value="<?= $oldValue('cel_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="email" name="mailp_emp" placeholder="Correo personal" value="<?= $oldValue('mailp_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="email" name="maili_emp" placeholder="Correo institucional" value="<?= $oldValue('maili_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <input type="date" name="fecnac_emp" required value="<?= $oldValue('fecnac_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="dir_emp" placeholder="Dirección" value="<?= $oldValue('dir_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <input type="text" name="ubigeodir_emp" placeholder="Ubigeo Dirección" value="<?= $oldValue('ubigeodir_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="ubigeonac_emp" placeholder="Ubigeo Nacimiento" value="<?= $oldValue('ubigeonac_emp') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <input type="file" name="foto_emp" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

    <select name="cargo_emp" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
        <option value="A" <?= $oldValue('cargo_emp') === 'A' ? 'selected' : '' ?>>Administrativo</option>
        <option value="D" <?= $oldValue('cargo_emp') === 'D' ? 'selected' : '' ?>>Docente</option>
        <option value="B" <?= $oldValue('cargo_emp') === 'B' ? 'selected' : '' ?>>Bienestar</option>
    </select>

    <select name="estado" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
        <option value="1" <?= $oldValue('estado') !== '0' ? 'selected' : '' ?>>Activo</option>
        <option value="0" <?= $oldValue('estado') === '0' ? 'selected' : '' ?>>Inactivo</option>
    </select>

    <button class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white font-semibold p-3 rounded col-span-1 md:col-span-4">Registrar Empleado</button>
</form>

<!-- TABLA -->
<table class="mt-6 w-full bg-white shadow rounded-xl">
    <tr class="bg-indigo-600 text-white">
        <th class="p-2">DNI</th>
        <th class="p-2">Nombre Completo</th>
        <th class="p-2">Celular</th>
        <th class="p-2">Correo</th>
        <th class="p-2">Cargo</th>
        <th class="p-2">Foto</th>
        <th class="p-2">Estado</th>
        <th class="p-2">Acciones</th>
    </tr>

    <?php foreach ($empleados as $e): ?>
    <tr class="border-b">
        <td class="p-2"><?= htmlspecialchars($e['dni_emp'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2"><?= htmlspecialchars($e['nombre_completo'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2"><?= htmlspecialchars($e['cel_emp'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2"><?= htmlspecialchars($e['mailp_emp'], ENT_QUOTES, 'UTF-8') ?></td>

        <td class="p-2">
            <?php
                switch ($e['cargo_emp']) {
                    case 'A': echo "Administrativo"; break;
                    case 'D': echo "Docente"; break;
                    case 'B': echo "Bienestar"; break;
                    default: echo "Sin definir";
                }
            ?>
        </td>

        <td class="p-2">
            <?php if (!empty($e['foto_emp'])): ?>
                <img src="<?= htmlspecialchars($uploadsPath . rawurlencode($e['foto_emp']), ENT_QUOTES, 'UTF-8') ?>" alt="Foto de <?= htmlspecialchars($e['nombre_completo'], ENT_QUOTES, 'UTF-8') ?>" class="w-12 h-12 rounded-full object-cover border">
            <?php else: ?>
                <span class="text-gray-500">Sin foto</span>
            <?php endif ?>
        </td>

        <td class="p-2">
            <?= (int) $e['estado'] === 1 ? 'Activo' : 'Inactivo' ?>
        </td>

        <td class="p-2">
            <a href="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>&delete=<?= (int) $e['id'] ?>" class="text-red-600 hover:underline">Eliminar</a>
        </td>
    </tr>
    <?php endforeach ?>
</table>
