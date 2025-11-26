<?php
require_once __DIR__ . "/../../../config/conexion.php";

$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Sistema-de-pagos-IESTP-main';

try {
    $db = Database::getInstance()->getConnection();

    // TABLA REAL
    $sql = "SELECT * FROM solicitud ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (Exception $e) {
    echo "<p style='color:red'>Error BD: " . $e->getMessage() . "</p>";
    exit;
}
?>

<h2 class="text-2xl font-bold mb-6">Solicitudes Registradas</h2>

<?php if (empty($solicitudes)): ?>
    <p class="text-center text-gray-500">No hay solicitudes registradas</p>
<?php else: ?>
    <?php foreach ($solicitudes as $sol): ?>
    <div class="border-l-4 border-blue-400 rounded-lg shadow-md mb-5">

        <div class="p-5 bg-white">
            <p><strong>ID:</strong> <?= $sol['id'] ?></p>
            <p><strong>Nombre:</strong> <?= htmlspecialchars($sol['nombre']) ?></p>
            <p><strong>Teléfono:</strong> <?= htmlspecialchars($sol['telefono']) ?></p>
            <p><strong>Tipo:</strong> <?= htmlspecialchars($sol['tipo_solicitud']) ?></p>
            <p><strong>Fecha:</strong> <?= $sol['fecha'] ?></p>
            <p><strong>Registrado:</strong> <?= $sol['fecha_registro'] ?></p>

            <p class="mt-3"><strong>Descripción:</strong></p>
            <p class="text-gray-700"><?= nl2br(htmlspecialchars($sol['descripcion'])) ?></p>

            <p class="mt-3"><strong>Archivos:</strong></p>
            <?php
            if (!empty($sol['archivos'])) {
                $archivos = explode(',', $sol['archivos']);
                foreach ($archivos as $archivo) {
                    $ruta = $base_url . "/uploads/solicitudes/" . $archivo;
                    echo "<a href='$ruta' target='_blank' class='text-blue-600 underline block'>$archivo</a>";
                }
            } else {
                echo "<span class='text-gray-500'>Sin archivos</span>";
            }
            ?>

            <!-- BOTONES -->
            <div class="mt-4 flex gap-3">
                <button onclick="accionSolicitud(<?= $sol['id'] ?>,'Aprobado')"
                        class="bg-green-600 text-white px-4 py-2 rounded">
                        ✅ Aprobar
                </button>

                <button onclick="accionSolicitud(<?= $sol['id'] ?>,'Rechazado')"
                        class="bg-red-600 text-white px-4 py-2 rounded">
                        ❌ Rechazar
                </button>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<script>
function accionSolicitud(id, accion){
    fetch("../controller/procesarSolicitud.php", {
        method: "POST",
        headers: {"Content-Type":"application/json"},
        body: JSON.stringify({id, accion})
    }).then(r=>r.json())
      .then(res=>{
        alert(res.mensaje);
        location.reload();
      });
}
</script>
