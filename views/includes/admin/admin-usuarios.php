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

// Crear estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dni_est'])) {
    $input = filter_input_array(INPUT_POST, [
        'dni_est'        => FILTER_SANITIZE_NUMBER_INT,
        'ap_est'         => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'am_est'         => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'nom_est'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'sex_est'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'cel_est'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'dir_est'        => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'mailp_est'      => FILTER_SANITIZE_EMAIL,
        'maili_est'      => FILTER_SANITIZE_EMAIL,
        'fecnac_est'     => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ubigeodir_est'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ubigeonac_est'  => FILTER_SANITIZE_FULL_SPECIAL_CHARS,
        'ubdistrito'     => FILTER_SANITIZE_NUMBER_INT
    ], true);

    $previousData = array_map(static function ($value) {
        return is_string($value) ? trim($value) : $value;
    }, $input ?? []);

    if (empty($previousData['dni_est']) || strlen($previousData['dni_est']) !== 8) {
        $errors[] = 'El DNI debe tener exactamente 8 dígitos.';
    }

    foreach (['ap_est', 'am_est', 'nom_est'] as $field) {
        if (empty($previousData[$field])) {
            $errors[] = 'Los apellidos y nombres son obligatorios.';
            break;
        }
    }

    if (empty($previousData['fecnac_est'])) {
        $errors[] = 'La fecha de nacimiento es obligatoria.';
    }

    if (!in_array($previousData['sex_est'] ?? '', ['M', 'F'], true)) {
        $errors[] = 'Selecciona un sexo válido.';
    }

    if (!empty($previousData['mailp_est']) && !filter_var($previousData['mailp_est'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo personal no es válido.';
    }

    if (!empty($previousData['maili_est']) && !filter_var($previousData['maili_est'], FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'El correo institucional no es válido.';
    }

    if (!empty($previousData['ubdistrito']) && !ctype_digit((string) $previousData['ubdistrito'])) {
        $errors[] = 'El ID de distrito debe ser numérico.';
    }

    if (empty($errors)) {
        if ($ctrl->crear($previousData, $_FILES)) {
            header("Location: {$redirectUrl}&status=created");
            exit;
        }

        $errors[] = 'No se pudo registrar el estudiante, intenta nuevamente.';
    }
}

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

<!-- FORMULARIO DE REGISTRO -->
<form method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-4 gap-4 bg-white p-4 shadow rounded-xl mt-6">

    <input type="text" name="dni_est" placeholder="DNI" required maxlength="8" value="<?= $oldValue('dni_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="ap_est" placeholder="Apellido Paterno" required value="<?= $oldValue('ap_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="am_est" placeholder="Apellido Materno" required value="<?= $oldValue('am_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="nom_est" placeholder="Nombres" required value="<?= $oldValue('nom_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <select name="sex_est" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
        <option value="M" <?= $oldValue('sex_est') === 'M' ? 'selected' : '' ?>>Masculino</option>
        <option value="F" <?= $oldValue('sex_est') === 'F' ? 'selected' : '' ?>>Femenino</option>
    </select>

    <input type="text" name="cel_est" placeholder="Celular" value="<?= $oldValue('cel_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="dir_est" placeholder="Dirección" value="<?= $oldValue('dir_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="email" name="mailp_est" placeholder="Correo personal" value="<?= $oldValue('mailp_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="email" name="maili_est" placeholder="Correo institucional" value="<?= $oldValue('maili_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="date" name="fecnac_est" required value="<?= $oldValue('fecnac_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <input type="text" name="ubigeodir_est" placeholder="Ubigeo Dirección" value="<?= $oldValue('ubigeodir_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="text" name="ubigeonac_est" placeholder="Ubigeo Nacimiento" value="<?= $oldValue('ubigeonac_est') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">
    <input type="number" name="ubdistrito" placeholder="ID Distrito" value="<?= $oldValue('ubdistrito') ?>" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400">

    <input type="file" name="foto_est" class="input border rounded-lg px-3 py-2 focus:ring-2 focus:ring-indigo-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">

    <button class="bg-indigo-600 hover:bg-indigo-700 transition-colors text-white font-semibold p-3 rounded col-span-1 md:col-span-4">Registrar Estudiante</button>
</form>

<!-- TABLA -->
<table class="mt-6 w-full bg-white shadow rounded-xl">
    <tr class="bg-indigo-600 text-white">
        <th class="p-2">ID</th>
        <th class="p-2">DNI</th>
        <th class="p-2">Nombre Completo</th>
        <th class="p-2">Edad</th>
        <th class="p-2">Sexo</th>
        <th class="p-2">Estado</th>
        <th class="p-2">Acciones</th>
    </tr>

    <?php foreach ($estudiantes as $e): ?>
    <tr class="border-b">
        <td class="p-2"><?= (int) $e['id'] ?></td>
        <td class="p-2"><?= htmlspecialchars($e['dni_est'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2"><?= htmlspecialchars($e['nombre_completo'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2"><?= htmlspecialchars($e['edad'], ENT_QUOTES, 'UTF-8') ?></td>
        <td class="p-2">
            <?= $e['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?>
        </td>
        <td class="p-2">
            <?= (int) $e['estado'] === 1 ? 'Activo' : 'Inactivo' ?>
        </td>
        <td class="p-2">
            <a href="<?= htmlspecialchars($redirectUrl, ENT_QUOTES, 'UTF-8') ?>&delete=<?= (int) $e['id'] ?>"
               class="text-red-600 hover:underline">Eliminar</a>
        </td>
    </tr>
    <?php endforeach ?>
</table>

