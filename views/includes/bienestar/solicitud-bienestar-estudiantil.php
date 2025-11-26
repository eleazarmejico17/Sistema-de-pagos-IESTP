<?php
require_once __DIR__ . "/../../../config/conexion.php";

// Obtener la ruta base del proyecto
$base_url = 'http://' . $_SERVER['HTTP_HOST'] . '/Sistema-de-pagos-IESTP-main';

try {
    $db = Database::getInstance()->getConnection();

    // Obtener solicitudes
    $sql = "SELECT * FROM solicitudes ORDER BY id DESC";
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    error_log("Error obteniendo solicitudes: " . $e->getMessage());
    $solicitudes = [];
}
?>

<h2 class="text-2xl font-bold mb-6">Solicitudes Registradas</h2>

<?php if (empty($solicitudes)): ?>
    <div class="bg-yellow-50 border border-yellow-200 rounded-lg p-6 text-center">
        <i class="fas fa-inbox text-yellow-500 text-4xl mb-3"></i>
        <p class="text-yellow-700 font-semibold">No hay solicitudes registradas</p>
        <p class="text-yellow-600 text-sm mt-1">Las solicitudes aparecerán aquí cuando los usuarios las envíen.</p>
    </div>
<?php else: ?>
    <?php foreach ($solicitudes as $sol): ?>
    <div class="border-l-4 rounded-lg shadow-md mb-5 
        <?= $sol['estado']=='Aprobado'?'border-green-400':($sol['estado']=='Rechazado'?'border-red-400':'border-yellow-400') ?>">

        <div class="flex justify-between items-center p-5 cursor-pointer bg-white"
             onclick="toggleSolicitud(this)">
            <div>
                <p class="font-semibold text-gray-800"><?= htmlspecialchars($sol['nombre_completo']) ?></p>
                <p class="text-gray-600 text-xs">Teléfono: <?= htmlspecialchars($sol['telefono']) ?></p>
                <p class="text-gray-600 text-xs">Tipo: <?= htmlspecialchars($sol['tipo_solicitud']) ?></p>
                <span class="text-xs font-medium 
                    <?= $sol['estado']=='Aprobado'?'text-green-600':
                        ($sol['estado']=='Rechazado'?'text-red-600':'text-yellow-600') ?>">
                    <?= $sol['estado'] ?>
                </span>
            </div>
            <span class="text-black text-xl font-bold rotate-anim">▲</span>
        </div>

        <div class="transition-height bg-gray-50" style="max-height:0; overflow:hidden;">
            <div class="px-5 pb-5">
                <p class="text-gray-700 text-sm mt-2">
                    <?= nl2br(htmlspecialchars($sol['descripcion'])) ?>
                </p>

                <div class="mt-4">
                    <p class="font-semibold">Evidencias</p>

                    <div class="flex space-x-3 mt-2">
                        <?php 
                        if ($sol['archivos']) {
                            $archivos = explode(",", $sol['archivos']);
                            foreach($archivos as $archivo){
                                $extension = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                                if (in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
                                    echo "
                                    <div class='bg-gray-100 w-24 h-24 flex items-center justify-center border rounded overflow-hidden cursor-pointer' 
                                         onclick='abrirImagen(\"$base_url/uploads/solicitudes/$archivo\")'>
                                        <img src='$base_url/uploads/solicitudes/$archivo' class='object-cover w-full h-full'>
                                    </div>";
                                } else {
                                    echo "
                                    <div class='bg-gray-100 w-24 h-24 flex flex-col items-center justify-center border rounded p-2'>
                                        <i class='fas fa-file text-gray-400 text-2xl mb-1'></i>
                                        <p class='text-xs text-gray-500 text-center truncate w-full'>$archivo</p>
                                    </div>";
                                }
                            }
                        } else {
                            echo "<p class='text-gray-500'>No hay archivos adjuntos</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- BOTONES -->
                <div class="mt-4 flex gap-3">
                    <button onclick="actualizarEstado(<?= $sol['id'] ?>, 'Aprobado')" 
                            class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-check"></i>
                        Aprobar
                    </button>

                    <button onclick="rechazarConMotivo(<?= $sol['id'] ?>)" 
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition flex items-center gap-2">
                        <i class="fas fa-times"></i>
                        Rechazar
                    </button>
                </div>

            </div>
        </div>
    </div>
    <?php endforeach; ?>
<?php endif; ?>

<!-- Modal para motivo de rechazo -->
<div id="modalRechazo" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-6 w-96">
        <h3 class="text-lg font-semibold mb-4">Motivo del rechazo</h3>
        <textarea id="motivoRechazo" 
                  placeholder="Ingrese el motivo del rechazo..."
                  class="w-full h-32 border border-gray-300 rounded-lg p-3 resize-none focus:ring-2 focus:ring-red-400 focus:outline-none"></textarea>
        <div class="flex justify-end gap-3 mt-4">
            <button onclick="cerrarModalRechazo()" 
                    class="px-4 py-2 text-gray-600 hover:text-gray-800 transition">
                Cancelar
            </button>
            <button onclick="confirmarRechazo()" 
                    class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                <i class="fas fa-times mr-2"></i>Rechazar
            </button>
        </div>
    </div>
</div>

<!-- Modal para ver imágenes -->
<div id="modalImagen" class="hidden fixed inset-0 bg-black bg-opacity-75 z-50 flex items-center justify-center">
    <div class="bg-white rounded-lg p-4 max-w-3xl max-h-full">
        <div class="flex justify-between items-center mb-4">
            <h3 class="text-lg font-semibold">Vista previa</h3>
            <button onclick="cerrarModalImagen()" class="text-gray-500 hover:text-gray-700">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>
        <img id="imagenModal" src="" class="max-w-full max-h-96 object-contain">
    </div>
</div>

<script>
// URL base para las peticiones AJAX
const BASE_URL = '<?= $base_url ?>';
let solicitudIdRechazo = null;

function toggleSolicitud(header){
    const box = header.nextElementSibling;
    const arrow = header.querySelector('.rotate-anim');

    if(box.style.maxHeight !== "0px"){
        box.style.maxHeight = "0px";
        arrow.style.transform = "rotate(0deg)";
    } else {
        box.style.maxHeight = box.scrollHeight + "px";
        arrow.style.transform = "rotate(180deg)";
    }
}

function rechazarConMotivo(id) {
    solicitudIdRechazo = id;
    document.getElementById('motivoRechazo').value = '';
    document.getElementById('modalRechazo').classList.remove('hidden');
}

function cerrarModalRechazo() {
    document.getElementById('modalRechazo').classList.add('hidden');
    solicitudIdRechazo = null;
}

function confirmarRechazo() {
    const motivo = document.getElementById('motivoRechazo').value.trim();
    
    if (motivo === '') {
        alert('Por favor, ingrese el motivo del rechazo.');
        return;
    }

    if (solicitudIdRechazo) {
        actualizarEstado(solicitudIdRechazo, 'Rechazado', motivo);
        cerrarModalRechazo();
    }
}

function actualizarEstado(id, estado, motivo = ''){
    console.log('Enviando solicitud:', {id, estado, motivo});
    
    // Mostrar loading
    const originalText = event.target.innerHTML;
    event.target.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
    event.target.disabled = true;
    
    // RUTA ABSOLUTA - desde la raíz del proyecto
    fetch(BASE_URL + '/controller/actualizarEstadoSolicitud.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/json'},
        body: JSON.stringify({id, estado, motivo})
    })
    .then(response => {
        console.log('Respuesta recibida, status:', response.status);
        if (!response.ok) {
            throw new Error(`Error HTTP: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        console.log('Datos recibidos:', data);
        if(data.success){
            alert("✅ " + data.message);
            location.reload();
        } else {
            alert("❌ Error: " + data.error);
        }
    })
    .catch(error => {
        console.error('Error en fetch:', error);
        alert("❌ Error de conexión: " + error.message);
    })
    .finally(() => {
        // Restaurar botón
        event.target.innerHTML = originalText;
        event.target.disabled = false;
    });
}

function abrirImagen(src) {
    document.getElementById('imagenModal').src = src;
    document.getElementById('modalImagen').classList.remove('hidden');
}

function cerrarModalImagen() {
    document.getElementById('modalImagen').classList.add('hidden');
}

// Cerrar modales con ESC
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        if (!document.getElementById('modalRechazo').classList.contains('hidden')) {
            cerrarModalRechazo();
        }
        if (!document.getElementById('modalImagen').classList.contains('hidden')) {
            cerrarModalImagen();
        }
    }
});

// Cerrar modal al hacer clic fuera
document.getElementById('modalRechazo').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalRechazo();
    }
});

document.getElementById('modalImagen').addEventListener('click', function(e) {
    if (e.target === this) {
        cerrarModalImagen();
    }
});
</script>

<style>
.rotate-anim {
    transition: transform 0.3s ease;
}

.transition-height {
    transition: max-height 0.3s ease;
}
</style>