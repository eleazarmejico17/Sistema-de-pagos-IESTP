<?php
require_once __DIR__ . '/../../../config/conexion-local.php';
$pdo = Conexion::getInstance()->getConnection();

// ---------------------------------------------------------------------
// 1. NOTIFICACIONES
// ---------------------------------------------------------------------
function showMessage($type) {
    $messages = [
        "creado"     => ["Registro creado correctamente.",       "bg-emerald-100 border-emerald-500 text-emerald-700"],
        "actualizado"=> ["Registro actualizado correctamente.",  "bg-blue-100 border-blue-500 text-blue-700"],
        "eliminado"  => ["Registro eliminado correctamente.",    "bg-red-100 border-red-500 text-red-700"],
        "error"      => ["Ha ocurrido un error.",                "bg-red-100 border-red-500 text-red-700"]
    ];

    if (!isset($messages[$type])) return;

    [$text, $color] = $messages[$type];

    echo "
        <div class='alert-auto p-4 mb-4 border-l-4 rounded-md shadow-sm $color'>
            <p class='font-semibold'>$text</p>
        </div>
    ";
}

// ---------------------------------------------------------------------
// 2. ACCIONES (CREATE, UPDATE, DELETE)
// ---------------------------------------------------------------------

// Crear
if (isset($_POST['accion']) && $_POST['accion'] === "crear") {

    try {
        $stmt = $pdo->prepare("INSERT INTO tipo_pago (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->execute([
            ":nombre"      => $_POST['nombre'],
            ":descripcion" => $_POST['descripcion']
        ]);

        header("Location: admin-tipo-pago.php?msg=creado");
        exit;

    } catch (Exception $e) {
        header("Location: admin-tipo-pago.php?msg=error");
        exit;
    }
}

// Actualizar
if (isset($_POST['accion']) && $_POST['accion'] === "editar") {

    try {
        $stmt = $pdo->prepare("UPDATE tipo_pago SET nombre = :nombre, descripcion = :descripcion WHERE id = :id");
        $stmt->execute([
            ":id"          => $_POST['id'],
            ":nombre"      => $_POST['nombre'],
            ":descripcion" => $_POST['descripcion']
        ]);

        header("Location: dashboard-admin.php?modulo=admin-tipo-pago&msg=actualizado");

        exit;

    } catch (Exception $e) {
        header("Location: admin-tipo-pago.php?msg=error");
        exit;
    }
}

// Eliminar
if (isset($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tipo_pago WHERE id = :id");
        $stmt->execute([":id" => $_GET['eliminar']]);

        header("Location: dashboard-admin.php?modulo=admin-tipo-pago&msg=eliminado");

        exit;
    } catch (Exception $e) {
        header("Location: admin-tipo-pago.php?msg=error");
        exit;
    }
}


// ---------------------------------------------------------------------
// 3. SI SE VA A EDITAR, OBTENER DATOS PARA AUTORELLENAR
// ---------------------------------------------------------------------
$editData = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM tipo_pago WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $_GET['editar']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}


// ---------------------------------------------------------------------
// 4. LISTA COMPLETA
// ---------------------------------------------------------------------
$stmt = $pdo->query("SELECT * FROM tipo_pago ORDER BY id DESC");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Tipos de Pago</title>
</head>

<body class="min-h-screen bg-gray-100 p-6">

<div class="max-w-4xl mx-auto">

    <?php if (isset($_GET['msg'])) showMessage($_GET['msg']); ?>

    <div class="bg-white p-6 rounded-2xl shadow-lg mb-8">
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            <?= $editData ? "Editar Tipo de Pago" : "Registrar Tipo de Pago" ?>
        </h2>

        <form method="POST" class="space-y-5">

            <input type="hidden" name="accion" value="<?= $editData ? "editar" : "crear" ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id']; ?>">
            <?php endif; ?>

            <div>
                <label class="block font-semibold">Nombre *</label>
                <input 
                    required
                    type="text"
                    name="nombre"
                    value="<?= $editData ? $editData['nombre'] : "" ?>"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                >
            </div>

            <div>
                <label class="block font-semibold">Descripción</label>
                <textarea
                    name="descripcion"
                    rows="3"
                    class="w-full px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                ><?= $editData ? $editData['descripcion'] : "" ?></textarea>
            </div>

            <button 
                type="submit"
                class="w-full bg-emerald-500 hover:bg-emerald-600 text-white py-2 rounded-lg font-semibold shadow-lg transition">
                <?= $editData ? "Guardar Cambios" : "Guardar" ?>
            </button>

        </form>
    </div>


    <!-- TABLA -->
    <div class="bg-white p-6 rounded-2xl shadow-lg">
        <h2 class="text-xl font-bold mb-4">Lista de Tipos de Pago</h2>

        <div class="overflow-x-auto">
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

<a href="dashboard-admin.php?modulo=admin-tipo-pago&editar=<?= $item['id'] ?>"
   class="px-3 py-1 bg-blue-500 text-white rounded-lg hover:bg-blue-600">
   Editar
</a>



<a href="dashboard-admin.php?modulo=admin-tipo-pago&eliminar=<?= $item['id'] ?>"
   onclick="return confirm('¿Seguro de eliminar?')"
   class="px-3 py-1 bg-red-500 text-white rounded-lg hover:bg-red-600">
   Eliminar
</a>


                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        </div>
    </div>

</div>

<script>
setTimeout(() => {
    const alert = document.querySelector('.alert-auto');
    if (alert) alert.remove();
}, 3500);
</script>

</body>
</html>
