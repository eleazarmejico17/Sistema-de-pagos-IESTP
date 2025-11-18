<?php

require_once __DIR__ . '/../../../controller/admin-usuariosController.php';



$ctrl = new EstudiantesController();

// Crear estudiante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['dni_est'])) {
    $ctrl->crear($_POST);
    header("Location: dashboard-admin.php?pagina=usuarios");
    exit;
}

// Eliminar estudiante
if (isset($_GET['delete'])) {
    $ctrl->eliminar($_GET['delete']);
    header("Location: dashboard-admin.php?pagina=usuarios");
    exit;
}

$estudiantes = $ctrl->listar();
?>

<h2 class="text-2xl font-bold mb-6">Gestión de Estudiantes</h2>

<!-- FORMULARIO DE REGISTRO -->
<form method="POST" enctype="multipart/form-data" class="grid grid-cols-4 gap-4 bg-white p-4 shadow rounded-xl">

    <input type="text" name="dni_est" placeholder="DNI" required class="input">
    <input type="text" name="ap_est" placeholder="Apellido Paterno" required class="input">
    <input type="text" name="am_est" placeholder="Apellido Materno" required class="input">
    <input type="text" name="nom_est" placeholder="Nombres" required class="input">

    <select name="sex_est" class="input">
        <option value="M">Masculino</option>
        <option value="F">Femenino</option>
    </select>

    <input type="text" name="cel_est" placeholder="Celular" class="input">
    <input type="text" name="dir_est" placeholder="Dirección" class="input">
    <input type="email" name="mailp_est" placeholder="Correo personal" class="input">
    <input type="email" name="maili_est" placeholder="Correo institucional" class="input">
    <input type="date" name="fecnac_est" required class="input">

    <input type="text" name="ubigeodir_est" placeholder="Ubigeo Dirección" class="input">
    <input type="text" name="ubigeonac_est" placeholder="Ubigeo Nacimiento" class="input">
    <input type="number" name="ubdistrito" placeholder="ID Distrito" class="input">

    <input type="file" name="foto_est" class="input">

    <button class="bg-indigo-600 text-white p-2 rounded col-span-4">Registrar Estudiante</button>
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
        <td class="p-2"><?= $e['id'] ?></td>
        <td class="p-2"><?= $e['dni_est'] ?></td>
        <td class="p-2"><?= $e['nombre_completo'] ?></td>
        <td class="p-2"><?= $e['edad'] ?></td>
        <td class="p-2">
            <?= $e['sexo'] === 'M' ? 'Masculino' : 'Femenino' ?>
        </td>
        <td class="p-2">
            <?= $e['estado'] == 1 ? 'Activo' : 'Inactivo' ?>
        </td>
        <td class="p-2">
            <a href="dashboard-admin.php?pagina=usuarios&delete=<?= $e['id'] ?>"
               class="text-red-600">Eliminar</a>
        </td>
    </tr>
    <?php endforeach ?>
</table>

