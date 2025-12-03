<?php
require_once __DIR__ . '/../../../config/conexion-local.php';
$pdo = Conexion::getInstance()->getConnection();

/* -----------------------------------------------------------
   NOTIFICACIONES
----------------------------------------------------------- */
function showMessage($type) {
    $messages = [
        "creado"     => ["Usuario creado correctamente.",        "bg-emerald-100 border-emerald-500 text-emerald-700"],
        "actualizado"=> ["Usuario actualizado correctamente.",   "bg-blue-100 border-blue-500 text-blue-700"],
        "eliminado"  => ["Usuario eliminado correctamente.",     "bg-red-100 border-red-500 text-red-700"],
        "error"      => ["Ha ocurrido un error.",                 "bg-red-100 border-red-500 text-red-700"]
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

/* -----------------------------------------------------------
   ACCIÓN: CREAR USUARIO
----------------------------------------------------------- */
if (isset($_POST['accion']) && $_POST['accion'] === "crear") {
    try {
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (usuario, password, tipo, estuempleado, token)
            VALUES (:usuario, :password, :tipo, :estuempleado, :token)
        ");

        $stmt->execute([
            ":usuario"      => $_POST['usuario'],
            ":password"     => password_hash($_POST['password'], PASSWORD_DEFAULT),
            ":tipo"         => $_POST['tipo'],
            ":estuempleado" => $_POST['estuempleado'] ?: null,
            ":token"        => $_POST['token'] ?: null
        ]);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=creado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=error';</script>";
        exit;
    }
}


/* -----------------------------------------------------------
   ACCIÓN: EDITAR USUARIO
----------------------------------------------------------- */
if (isset($_POST['accion']) && $_POST['accion'] === "editar") {
    try {

        // Si no cambia contraseña → no actualizarla
        if (!empty($_POST['password'])) {
            $sql = "UPDATE usuarios SET usuario=:usuario, password=:password, tipo=:tipo, estuempleado=:estuempleado, token=:token WHERE id=:id";
            $params = [
                ":id"           => $_POST['id'],
                ":usuario"      => $_POST['usuario'],
                ":password"     => password_hash($_POST['password'], PASSWORD_DEFAULT),
                ":tipo"         => $_POST['tipo'],
                ":estuempleado" => $_POST['estuempleado'] ?: null,
                ":token"        => $_POST['token'] ?: null,
            ];
        } else {
            $sql = "UPDATE usuarios SET usuario=:usuario, tipo=:tipo, estuempleado=:estuempleado, token=:token WHERE id=:id";
            $params = [
                ":id"           => $_POST['id'],
                ":usuario"      => $_POST['usuario'],
                ":tipo"         => $_POST['tipo'],
                ":estuempleado" => $_POST['estuempleado'] ?: null,
                ":token"        => $_POST['token'] ?: null,
            ];
        }

        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=actualizado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=error';</script>";
        exit;
    }
}


/* -----------------------------------------------------------
   ACCIÓN: ELIMINAR USUARIO
----------------------------------------------------------- */
if (isset($_GET['eliminar'])) {
    try {
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :id");
        $stmt->execute([":id" => $_GET['eliminar']]);

        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=eliminado';</script>";
        exit;

    } catch (Exception $e) {
        echo "<script>location.href='dashboard-admin.php?pagina=admin-agregar-usuario&msg=error';</script>";
        exit;
    }
}


/* -----------------------------------------------------------
   FORMULARIO DE EDICIÓN
----------------------------------------------------------- */
$editData = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $_GET['editar']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}


/* -----------------------------------------------------------
   LISTADO
----------------------------------------------------------- */
$stmt = $pdo->query("SELECT * FROM usuarios ORDER BY id DESC");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!-- ==========================================================
     FRONTEND
=========================================================== -->

<div class="max-w-4xl mx-auto">

    <?php if (isset($_GET['msg'])) showMessage($_GET['msg']); ?>

    <!-- FORMULARIO -->
    <div class="bg-white p-6 rounded-xl shadow-md mb-8 border border-gray-200">

        <h2 class="text-2xl font-bold text-gray-800 mb-3">
            <?= $editData ? "Editar Usuario" : "Registrar Usuario" ?>
        </h2>

        <form method="POST" class="space-y-4">

            <input type="hidden" name="accion" value="<?= $editData ? "editar" : "crear" ?>">

            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= $editData['id'] ?>">
            <?php endif; ?>

            <!-- Usuario -->
            <div>
                <label class="font-medium text-gray-700">Usuario *</label>
                <input 
                    required
                    type="text"
                    name="usuario"
                    value="<?= $editData ? $editData['usuario'] : "" ?>"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="font-medium text-gray-700"><?= $editData ? "Nueva contraseña (opcional)" : "Contraseña *" ?></label>
                <input 
                    type="password"
                    name="password"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Tipo -->
            <div>
                <label class="font-medium text-gray-700">Tipo de Usuario *</label>
                <select 
                    required
                    name="tipo"
                    class="w-full mt-1 px-4 py-2 border rounded-lg bg-white focus:ring-2 focus:ring-indigo-500"
                >
                    <option value="">Seleccione...</option>

                    <option value="1" <?= ($editData && $editData['tipo']==1) ? "selected":"" ?>>Empleado</option>
                    <option value="2" <?= ($editData && $editData['tipo']==2) ? "selected":"" ?>>Estudiante</option>
                    <option value="3" <?= ($editData && $editData['tipo']==3) ? "selected":"" ?>>Empresa</option>
                    <option value="4" <?= ($editData && $editData['tipo']==4) ? "selected":"" ?>>Administrador</option>
                    <option value="5" <?= ($editData && $editData['tipo']==5) ? "selected":"" ?>>Bienestar</option>

                </select>
            </div>

            <!-- Estuempleado -->
            <div>
                <label class="font-medium text-gray-700">ID Estudiante / Empleado (Opcional)</label>
                <input 
                    type="number"
                    name="estuempleado"
                    value="<?= $editData ? $editData['estuempleado'] : "" ?>"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                >
            </div>

            <!-- Token -->
            <div>
                <label class="font-medium text-gray-700">Token (Opcional)</label>
                <textarea 
                    name="token"
                    rows="2"
                    class="w-full mt-1 px-4 py-2 border rounded-lg focus:ring-2 focus:ring-indigo-500"
                ><?= $editData ? $editData['token'] : "" ?></textarea>
            </div>

            <button 
                class="w-full bg-indigo-600 hover:bg-indigo-700 text-white py-2 rounded-lg shadow">
                <?= $editData ? "Guardar Cambios" : "Guardar" ?>
            </button>

        </form>

    </div>


    <!-- TABLA -->
    <div class="bg-white p-6 rounded-xl shadow-md border border-gray-200">

        <h2 class="text-xl font-bold mb-4">Lista de Usuarios</h2>

        <div class="overflow-x-auto">
            <table class="w-full border rounded-lg">
                <thead class="bg-gray-100">
                    <tr class="text-left">
                        <th class="p-2 border">ID</th>
                        <th class="p-2 border">Usuario</th>
                        <th class="p-2 border">Tipo</th>
                        <th class="p-2 border">ID Asoc.</th>
                        <th class="p-2 border text-center">Acciones</th>
                    </tr>
                </thead>

                <tbody>
                    <?php foreach ($lista as $item): ?>

                        <tr class="hover:bg-gray-50">
                            <td class="p-2 border"><?= $item['id'] ?></td>
                            <td class="p-2 border"><?= $item['usuario'] ?></td>
                            <td class="p-2 border">
                                <?php
                                    $t = [
                                        1=>"Empleado",
                                        2=>"Estudiante",
                                        3=>"Empresa",
                                        4=>"Administrador",
                                        5=>"Bienestar"
                                    ];
                                    echo $t[$item['tipo']];
                                ?>
                            </td>
                            <td class="p-2 border"><?= $item['estuempleado'] ?></td>

                            <td class="p-2 border text-center">

                                <a href="dashboard-admin.php?pagina=admin-agregar-usuario&editar=<?= $item['id'] ?>"
                                class="px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600">
                                    Editar
                                </a>

                                <a href="dashboard-admin.php?pagina=admin-agregar-usuario&eliminar=<?= $item['id'] ?>"
                                onclick="return confirm('¿Seguro de eliminar este usuario?')"
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
