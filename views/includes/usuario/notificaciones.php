<?php
// views/includes/usuario/notificaciones.php
require_once __DIR__ . "/../../../config/conexion.php";

$db = Database::getInstance()->getConnection();

// Obtener notificaciones (solicitudes con respuesta)
$sql = "SELECT * FROM solicitudes 
        WHERE estado IN ('Aprobado', 'Rechazado') 
        AND notificacion_enviada = TRUE
        ORDER BY fecha_respuesta DESC";
$stmt = $db->prepare($sql);
$stmt->execute();
$notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener la ruta base para las imágenes (AGREGAR ESTA LÍNEA)
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Sistema-de-pagos-IESTP-main';
?>

<main class="flex-1 p-8">
  <h1 class="text-2xl font-bold mb-6 text-gray-800">Notificaciones</h1>

  <?php if (empty($notificaciones)): ?>
    <!-- Sin notificaciones -->
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
      <div class="text-gray-400 text-6xl mb-4">
        <i class="fas fa-bell-slash"></i>
      </div>
      <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay notificaciones</h3>
      <p class="text-gray-500">No tienes notificaciones pendientes.</p>
    </div>
  <?php else: ?>
    <?php foreach ($notificaciones as $notif): ?>
      <?php if ($notif['estado'] == 'Rechazado'): ?>
        <!-- Notificación Rechazada -->
        <div class="bg-white rounded-2xl shadow-lg border border-red-200 p-6 relative hover:shadow-xl transition mb-6">
          <div class="absolute right-5 top-5 text-red-500 text-xl font-bold">❌</div>

          <p class="font-semibold text-gray-800">OF. Bienestar Estudiantil</p>
          <p class="text-gray-700 mt-2 text-sm leading-relaxed">
            Hola <span class="font-medium"><?= htmlspecialchars($notif['nombre_completo']) ?></span>, su solicitud ha sido <span class="font-bold text-red-600">rechazada</span> 
            <?php if (!empty($notif['motivo_respuesta'])): ?>
              debido a que <?= htmlspecialchars($notif['motivo_respuesta']) ?>.
            <?php else: ?>
              por motivos administrativos.
            <?php endif; ?>
          </p>

          <div class="mt-5">
            <p class="font-semibold text-gray-700 text-sm">Resolución solicitada</p>
            <span class="inline-block bg-red-100 text-red-800 text-xs font-semibold px-3 py-1 rounded-full mt-1">
              N°<?= $notif['id'] ?> <?= htmlspecialchars($notif['tipo_solicitud']) ?>
            </span>
          </div>

          <?php if ($notif['archivos']): ?>
            <div class="mt-5">
              <p class="font-semibold text-gray-700 text-sm mb-2">Evidencias</p>
              <div class="flex space-x-3">
                <?php 
                $archivos = explode(",", $notif['archivos']);
                foreach($archivos as $archivo): 
                  $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                  if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])): 
                ?>
                  <div class="bg-gray-100 w-24 h-20 flex items-center justify-center border rounded-xl hover:shadow-md transition cursor-pointer overflow-hidden"
                       onclick="abrirImagen('<?= $base_url ?>/uploads/solicitudes/<?= htmlspecialchars($archivo) ?>')">
                    <img src="<?= $base_url ?>/uploads/solicitudes/<?= htmlspecialchars($archivo) ?>" 
                         class="object-cover w-full h-full">
                  </div>
                <?php else: ?>
                  <div class="bg-gray-100 w-24 h-20 flex items-center justify-center border rounded-xl hover:shadow-md transition cursor-pointer">
                    <span class="text-gray-400 text-2xl">📄</span>
                  </div>
                <?php endif; endforeach; ?>
              </div>
            </div>
          <?php else: ?>
            <div class="mt-5">
              <p class="font-semibold text-gray-700 text-sm mb-2">Evidencias</p>
              <div class="flex space-x-3">
                <div class="bg-gray-100 w-24 h-20 flex items-center justify-center border rounded-xl">
                  <span class="text-gray-400 text-2xl">🖼️</span>
                </div>
                <div class="bg-gray-100 w-24 h-20 flex items-center justify-center border rounded-xl">
                  <span class="text-gray-400 text-2xl">🖼️</span>
                </div>
                <div class="bg-gray-100 w-24 h-20 flex items-center justify-center border rounded-xl">
                  <span class="text-gray-400 text-2xl">🖼️</span>
                </div>
              </div>
            </div>
          <?php endif; ?>
        </div>

      <?php else: ?>
        <!-- Notificación Aprobada -->
        <div class="bg-white rounded-2xl shadow-lg border border-blue-200 p-6 hover:shadow-xl transition flex flex-col md:flex-row justify-between items-start md:items-center mb-6">
          <div>
            <p class="font-semibold text-gray-800 text-sm"><?= htmlspecialchars($notif['nombre_completo']) ?></p>
            <span class="inline-block mt-2 bg-blue-100 text-blue-800 text-xs font-semibold px-3 py-1 rounded-full">
              N°<?= $notif['id'] ?> <?= htmlspecialchars($notif['tipo_solicitud']) ?> exitoso
            </span>
          </div>
          <div class="mt-3 md:mt-0 text-blue-500 text-xl font-bold">✔️</div>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  <?php endif; ?>
</main>

<!-- Modal para ver imágenes -->
<div id="modalImagen" class="hidden fixed inset-0 bg-black bg-opacity-90 z-50 flex items-center justify-center p-4">
  <div class="bg-white rounded-2xl max-w-4xl max-h-full w-full">
    <div class="flex justify-between items-center p-4 border-b border-gray-200">
      <h3 class="text-lg font-semibold text-gray-800">Vista previa</h3>
      <button onclick="cerrarModalImagen()" class="text-gray-500 hover:text-gray-700 p-2 rounded-lg hover:bg-gray-100 transition">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <div class="p-4 flex justify-center">
      <img id="imagenModal" src="" class="max-w-full max-h-96 object-contain rounded-lg">
    </div>
  </div>
</div>

<script>
function abrirImagen(src) {
  document.getElementById('imagenModal').src = src;
  document.getElementById('modalImagen').classList.remove('hidden');
  document.body.style.overflow = 'hidden';
}

function cerrarModalImagen() {
  document.getElementById('modalImagen').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

// Cerrar modal con ESC
document.addEventListener('keydown', function(e) {
  if (e.key === 'Escape') {
    cerrarModalImagen();
  }
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalImagen').addEventListener('click', function(e) {
  if (e.target === this) {
    cerrarModalImagen();
  }
});
</script>