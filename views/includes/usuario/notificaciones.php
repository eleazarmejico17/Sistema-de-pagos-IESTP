<?php
require_once __DIR__ . "/../../../config/conexion.php";

$notificaciones = [];
$errorConsulta = null;

try {
    $db = Conexion::getInstance()->getConnection();
    
    // Obtener notificaciones de la tabla solicitud que estén aprobadas o rechazadas
    // y que tengan notificacion_enviada = 1 o fecha_respuesta no nula
    $sql = "SELECT 
                s.id,
                s.nombre,
                s.telefono,
                s.correo,
                s.tipo_solicitud,
                s.descripcion,
                s.archivos,
                s.fecha,
                COALESCE(s.estado, 'Pendiente') AS estado,
                s.motivo_respuesta,
                s.fecha_respuesta,
                s.fecha_registro,
                COALESCE(s.notificacion_enviada, 0) AS notificacion_enviada
            FROM solicitud s
            WHERE s.estado IN ('Aprobado', 'Rechazado')
            AND (s.notificacion_enviada = 1 OR s.fecha_respuesta IS NOT NULL)
            ORDER BY COALESCE(s.fecha_respuesta, s.fecha_registro, s.fecha, NOW()) DESC
            LIMIT 50";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch (PDOException $e) {
    $errorConsulta = "Error de base de datos: " . $e->getMessage();
    error_log('Error PDO en notificaciones: ' . $e->getMessage());
} catch (Exception $e) {
    $errorConsulta = "Error: " . $e->getMessage();
    error_log('Error en notificaciones: ' . $e->getMessage());
}
?>

<section class="w-full space-y-4">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Notificaciones</h1>

<?php if ($errorConsulta): ?>
  <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
    <p class="font-semibold">Error al cargar notificaciones:</p>
    <p><?= htmlspecialchars($errorConsulta, ENT_QUOTES, 'UTF-8') ?></p>
  </div>
<?php elseif (empty($notificaciones)): ?>

  <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
    <div class="text-gray-400 text-6xl mb-4">🔕</div>
    <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay notificaciones</h3>
    <p class="text-gray-500">No tienes notificaciones pendientes.</p>
  </div>

<?php else: ?>

<?php foreach ($notificaciones as $notif): ?>
<div class="mb-4">

<?php if ($notif['estado'] == 'Rechazado'): ?>

<!-- TARJETA RECHAZADA -->
<div onclick="toggleNotificacion(this)"
     class="cursor-pointer bg-white rounded-2xl shadow-lg border border-red-200 p-6 relative hover:shadow-xl transition">

  <div class="absolute right-5 top-5 text-red-500 text-xl font-bold">❌</div>

  <!-- RESUMEN -->
  <div class="flex justify-between items-center">
    <div>
      <p class="font-semibold text-gray-800"><?= htmlspecialchars($notif['nombre'] ?? 'OF. Bienestar Estudiantil', ENT_QUOTES, 'UTF-8') ?></p>
      <span class="inline-block text-xs mt-2 bg-red-100 text-red-700 px-3 py-1 rounded-full">
        Solicitud Rechazada
      </span>
    </div>
  </div>

  <!-- DETALLE -->
  <div class="detalle-notificacion hidden mt-4">

    <p class="text-gray-700 text-sm leading-relaxed mt-3">
      Hola <span class="font-medium"><?= htmlspecialchars($notif['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></span>,
      su solicitud ha sido <span class="font-bold text-red-600">rechazada</span>
      <?php if (!empty($notif['motivo_respuesta'])): ?>
        debido a que <?= htmlspecialchars($notif['motivo_respuesta'], ENT_QUOTES, 'UTF-8') ?>.
      <?php else: ?>
        por motivos administrativos.
      <?php endif; ?>
    </p>

    <div class="mt-4">
      <p class="font-semibold text-gray-700 text-sm">Resolución</p>
      <span class="inline-block bg-red-50 text-red-700 text-xs font-semibold px-3 py-1 rounded-full mt-1">
        N°<?= $notif['id'] ?> <?= htmlspecialchars($notif['tipo_solicitud']) ?>
      </span>
    </div>

<?php if ($notif['archivos']): ?>
  <div class="mt-4">
    <p class="font-semibold text-gray-700 text-sm mb-2">Evidencias</p>
    <div class="flex flex-wrap gap-3">
      <?php 
      $archivos = explode(",", $notif['archivos']);
      foreach($archivos as $archivo): 
        $archivo = trim($archivo);
        if ($archivo === '') continue;
        $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
        $ruta = "../uploads/solicitudes/" . rawurlencode($archivo);
        $archivoSeguro = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');
        if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])): ?>
          <div onclick="event.stopPropagation(); abrirImagen('<?= $ruta ?>')"
               class="w-24 h-20 bg-gray-100 rounded-xl overflow-hidden hover:shadow cursor-pointer">
            <img src="<?= $ruta ?>" class="w-full h-full object-cover">
          </div>
        <?php else: ?>
          <div class="w-24 h-20 flex items-center justify-center border rounded-xl text-xs text-gray-500">
            <?= $archivoSeguro ?>
          </div>
        <?php endif; endforeach; ?>
    </div>
  </div>
<?php endif; ?>

</div> <!-- DETALLE -->
</div> <!-- TARJETA -->

<?php else: ?>

<!-- TARJETA APROBADA -->
<div onclick="toggleNotificacion(this)"
     class="cursor-pointer bg-white rounded-2xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition">

  <div class="flex justify-between items-center">
    <div>
      <p class="font-semibold text-gray-800"><?= htmlspecialchars($notif['nombre'] ?? 'Usuario', ENT_QUOTES, 'UTF-8') ?></p>
      <span class="inline-block mt-2 bg-blue-100 text-blue-700 text-xs font-semibold px-3 py-1 rounded-full">
        Solicitud Aprobada
      </span>
    </div>
    <span class="text-blue-500 text-xl font-bold">✔️</span>
  </div>

  <div class="detalle-notificacion hidden mt-4">
    <p class="text-gray-600 text-sm">
      Esta solicitud fue aprobada correctamente.
    </p>

    <div class="mt-3">
      <span class="inline-block bg-blue-50 text-blue-700 text-xs px-3 py-1 rounded-full">
        N°<?= $notif['id'] ?> <?= htmlspecialchars($notif['tipo_solicitud']) ?>
      </span>
    </div>
  </div>

</div>

<?php endif; ?>

</div>
<?php endforeach; ?>
<?php endif; ?>
</section>

<!-- MODAL DE IMAGEN -->
<div id="modalImagen" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-4xl overflow-hidden">
    <div class="flex justify-between items-center p-4 border-b">
      <h3 class="font-semibold">Vista previa</h3>
      <button onclick="cerrarModalImagen()">✖</button>
    </div>
    <div class="p-4">
      <img id="imagenModal" class="max-w-full max-h-96 rounded">
    </div>
  </div>
</div>

<script>
function toggleNotificacion(card){
  const detalle = card.querySelector('.detalle-notificacion');

  if (!detalle) return;

  const abierta = !detalle.classList.contains('hidden');

  document.querySelectorAll('.detalle-notificacion').forEach(d => d.classList.add('hidden'));

  if (!abierta) detalle.classList.remove('hidden');
}

function abrirImagen(src){
  document.getElementById('imagenModal').src = src;
  document.getElementById('modalImagen').classList.remove('hidden');
}

function cerrarModalImagen(){
  document.getElementById('modalImagen').classList.add('hidden');
}
</script>
