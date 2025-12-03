<?php
require_once __DIR__ . '/../../../config/conexion-local.php';
$pdo = Conexion::getInstance()->getConnection();

// --------------------------------------------------
// NOTIFICACIONES
// --------------------------------------------------
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
        <div class='alert-auto p-4 mb-4 border-l-4 rounded-lg shadow-md animate-fadeIn $color'>
            <p class='font-semibold text-sm'>$text</p>
        </div>
        <style>
            @keyframes fadeIn { from {opacity:0;} to {opacity:1;} }
            .animate-fadeIn { animation: fadeIn .5s ease-in-out; }
        </style>
    ";
}


// ---------------------------------------------------------------------
// ACCIONES
// ---------------------------------------------------------------------

// CREAR
if (isset($_POST['accion']) && $_POST['accion'] === "crear") {
    try {
        $stmt = $pdo->prepare("INSERT INTO tipo_pago (nombre, descripcion) VALUES (:nombre, :descripcion)");
        $stmt->execute([
            ":nombre"      => $_POST['nombre'],
            ":descripcion" => $_POST['descripcion']
        ]);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=creado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=error';</script>";
        exit;
    }
}


// EDITAR
if (isset($_POST['accion']) && $_POST['accion'] === "editar") {
    try {
        $stmt = $pdo->prepare("UPDATE tipo_pago SET nombre=:nombre, descripcion=:descripcion WHERE id=:id");
        $stmt->execute([
            ":id"          => $_POST['id'],
            ":nombre"      => $_POST['nombre'],
            ":descripcion" => $_POST['descripcion']
        ]);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=actualizado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=error';</script>";
        exit;
    }
}


// ELIMINAR
if (isset($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM tipo_pago WHERE id = :id");
        $stmt->execute([":id" => $_GET['eliminar']]);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=eliminado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-tipo-pago&msg=error';</script>";
        exit;
    }
}


// ---------------------------------------------------------------------
// FORMULARIO DE EDICIÓN
// ---------------------------------------------------------------------
$editData = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM tipo_pago WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $_GET['editar']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}


// ---------------------------------------------------------------------
// LISTADO
// ---------------------------------------------------------------------
$stmt = $pdo->query("SELECT * FROM tipo_pago ORDER BY id DESC");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<div class="max-w-4xl mx-auto">

    <?php if (isset($_GET['msg'])) showMessage($_GET['msg']); ?>

    <!-- FORMULARIO -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-200">
        <h2 class="text-2xl font-bold text-gray-800 mb-3">
            <?= $editData ? "Editar Tipo de Pago" : "Registrar Tipo de Pago" ?>
        </h2>

        <form method="POST" class="space-y-4">

            <input type="hidden" name="accion" value="<?= $editData ? "editar" : "crear" ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id']; ?>">
            <?php endif; ?>

            <div>
                <label class="font-medium text-gray-700">Nombre *</label>
                <input 
                    required
                    type="text"
                    name="nombre"
                    value="<?= $editData ? $editData['nombre'] : "" ?>"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                >
            </div>

            <div>
                <label class="font-medium text-gray-700">Descripción</label>
                <textarea 
                    name="descripcion"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-emerald-500"
                    rows="3"><?= $editData ? $editData['descripcion'] : "" ?></textarea>
            </div>

            <button 
                class="w-full bg-emerald-600 hover:bg-emerald-700 text-white py-2 rounded-lg shadow">
                <?= $editData ? "Guardar Cambios" : "Guardar" ?>
            </button>
        </form>
    </div>

    <!-- TABLA -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">
        <h2 class="text-xl font-bold mb-4">Lista de Tipos de Pago</h2>

        <div class="overflow-x-auto">
            <table class="w-full border rounded-lg">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Nombre</th>
                        <th class="p-2 border">Descripción</th>
                        <th class="p-2 border text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($lista as $item): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="p-2 border"><?= $item['id'] ?></td>
                            <td class="p-2 border"><?= $item['nombre'] ?></td>
                            <td class="p-2 border"><?= $item['descripcion'] ?></td>
                            <td class="p-2 border text-center">

                                <a href="dashboard-admin.php?pagina=admin-tipo-pago&editar=<?= $item['id'] ?>"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Editar
                                </a>

                                <a href="dashboard-admin.php?pagina=admin-tipo-pago&eliminar=<?= $item['id'] ?>"
                                onclick="return confirm('¿Seguro de eliminar?')"
                                class="px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600">
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
