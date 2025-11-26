<?php
require_once '../config/conexion.php';

try {

    $db = Database::getInstance();

    // ✅ Aseguramos que estado nunca venga NULL
    $sql = "SELECT 
                id,
                nombre,
                telefono,
                tipo_solicitud,
                IFNULL(estado,'Pendiente') AS estado,
                descripcion,
                archivos 
            FROM solicitud 
            ORDER BY id DESC";

    $result = $db->executeQuery($sql)->fetchAll();

    if (!$result) {
        echo "<p class='text-gray-500 text-center'>No hay solicitudes registradas.</p>";
        exit;
    }

    foreach ($result as $row):

        // ✅ Color seguro
        $estado = $row["estado"] ?? "Pendiente";

        $color = [
            "Pendiente" => "yellow",
            "Aprobado"  => "green",
            "Rechazado" => "red"
        ][$estado] ?? "gray";


        // ✅ Manejo correcto de múltiples archivos
        if (!empty($row["archivos"])) {
            $listaArchivos = explode(",", $row["archivos"]);
            $links = [];

            foreach ($listaArchivos as $doc) {
                $ruta = "../uploads/solicitudes/" . trim($doc);
                $links[] = "<a href='$ruta' target='_blank' 
                            class='text-blue-600 underline block text-xs'>".htmlspecialchars($doc)."</a>";
            }

            $archivo = implode("", $links);

        } else {
            $archivo = "<span class='text-gray-400 text-xs'>Sin archivo</span>";
        }
?>
    <div class="border-l-4 rounded-lg shadow-md mb-6 border-<?= $color ?>-400">

        <div class="flex justify-between items-center p-5 cursor-pointer bg-white"
             onclick="toggleMessage(this)">
             
            <div>
                <p class="font-semibold text-gray-800 text-sm">
                    <?= htmlspecialchars($row["nombre"]) ?>
                </p>

                <p class="text-gray-600 text-xs">
                    Teléfono: <?= htmlspecialchars($row["telefono"]) ?>
                </p>

                <p class="text-gray-600 text-xs">
                    Tipo: <?= htmlspecialchars($row["tipo_solicitud"]) ?>
                </p>

                <span class="text-xs font-medium text-<?= $color ?>-600">
                    <?= htmlspecialchars($estado) ?>
                </span>
            </div>

            <span class="rotate-anim text-xl">▲</span>
        </div>

        <div class="transition-height overflow-hidden max-h-0 bg-gray-50">
            <div class="px-5 pb-5">

                <p class="mt-3 text-gray-700 text-sm">
                    <?= nl2br(htmlspecialchars($row["descripcion"])) ?>
                </p>

                <div class="mt-4 text-sm">
                    <strong>Archivo:</strong> <br> <?= $archivo ?>
                </div>

                <div class="mt-4 flex gap-3">

                    <button onclick="actualizarEstado(<?= $row['id'] ?>, 'Aprobado')"
                        class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                        Aprobar
                    </button>

                    <button onclick="actualizarEstado(<?= $row['id'] ?>, 'Rechazado')"
                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                        Rechazar
                    </button>

                </div>

            </div>
        </div>

    </div>

<?php endforeach; ?>

<?php
} catch (Exception $e) {
    echo "<p class='text-red-600 font-bold'>Error: {$e->getMessage()}</p>";
}
?>
