<?php
require_once '../config/conexion.php';

try {
    $db = Database::getInstance();
    $sql = "SELECT id, nombre, carrera, estado, descripcion, evidencia FROM solicitudes ORDER BY id DESC";
    $result = $db->executeQuery($sql)->fetchAll();

    if (!$result) {
        echo "<p class='text-gray-500 text-center'>No hay solicitudes registradas.</p>";
        exit;
    }

    foreach ($result as $row):

        $color = [
            "Pendiente" => "yellow",
            "Aprobado"  => "green",
            "Rechazado" => "red"
        ][$row["estado"]];

        $evidencia = $row["evidencia"]
            ? "<img src='../img/solicitudes/{$row["evidencia"]}' class='object-cover w-full h-full'>"
            : "<span class='text-gray-400 text-xs'>Sin imagen</span>";
?>
    <div class="border-l-4 rounded-lg shadow-md card-hover border-<?= $color ?>-400 mb-6">
        <div class="flex justify-between items-center p-5 cursor-pointer select-none bg-white"
             onclick="toggleMessage(this)">
             
            <div>
                <p class="font-semibold text-gray-800 text-sm"><?= $row["nombre"] ?></p>
                <p class="text-gray-600 text-xs">Carrera: <?= $row["carrera"] ?></p>
                <span class="text-xs font-medium text-<?= $color ?>-600 mt-1"><?= $row["estado"] ?></span>
            </div>

            <span class="text-black text-xl font-bold rotate-anim">▲</span>
        </div>

        <div class="transition-height bg-gray-50">
            <div class="px-5 pb-5">

                <p class="text-gray-700 text-sm mt-2">
                    <?= $row["descripcion"] ?>
                </p>

                <div class="mt-4">
                    <p class="font-semibold text-gray-700 text-sm mb-2">Evidencia</p>
                    <div class="bg-gray-100 w-24 h-24 border rounded overflow-hidden">
                        <?= $evidencia ?>
                    </div>
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

<?php
    endforeach;

} catch (Exception $e) {
    echo "<p class='text-red-600 font-bold'>Error: {$e->getMessage()}</p>";
}
?>

<script>
function toggleMessage(header) {
    const content = header.nextElementSibling;
    const arrow = header.querySelector(".rotate-anim");

    if (content.style.maxHeight && content.style.maxHeight !== "0px") {
        content.style.maxHeight = "0";
        arrow.style.transform = "rotate(0deg)";
    } else {
        content.style.maxHeight = content.scrollHeight + "px";
        arrow.style.transform = "rotate(180deg)";
    }
}

async function actualizarEstado(id, estado) {
    const response = await fetch("../controller/actualizarEstadoSolicitud.php", {
        method: "POST",
        headers: {"Content-Type": "application/json"},
        body: JSON.stringify({id, estado})
    });

    const result = await response.json();

    if (result.success) {
        alert("Estado actualizado correctamente");
        location.reload();
    } else {
        alert("Error: " + result.error);
    }
}
</script>
