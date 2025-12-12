<?php
require_once __DIR__ . "/../../../config/conexion.php";

$solicitudes = [];
$errorConsulta = null;
$carreras = [];

try {
    // Intentar con Database primero, luego con Conexion para compatibilidad
    if (class_exists('Database')) {
        $db = Database::getInstance()->getConnection();
    } elseif (class_exists('Conexion')) {
        $db = Conexion::getInstance()->getConnection();
    } else {
        throw new Exception("No se encontró la clase de conexión");
    }

    // Obtener solicitudes que necesitan revisión de dirección
    // Incluimos las que están aprobadas por bienestar o pendientes
    $sql = "SELECT 
                s.id,
                CONCAT(COALESCE(est.nom_est, ''), ' ', COALESCE(est.ap_est, ''), ' ', COALESCE(est.am_est, '')) AS nombre,
                est.cel_est AS telefono,
                est.mailp_est AS correo,
                s.tipo_solicitud,
                s.descripcion,
                s.foto AS archivos,
                s.fecha_solicitud AS fecha,
                COALESCE(s.estado, 'Pendiente') AS estado,
                s.observaciones AS motivo_respuesta,
                s.fecha_revision AS fecha_respuesta,
                s.fecha_solicitud AS fecha_registro,
                COALESCE(e.apnom_emp, '') AS empleado_nombre,
                est.dni_est,
                CONCAT(COALESCE(est.nom_est, ''), ' ', COALESCE(est.ap_est, ''), ' ', COALESCE(est.am_est, '')) AS nombre_completo_est,
                COALESCE(prog.nom_progest, 'No especificada') AS carrera
            FROM solicitudes s
            LEFT JOIN empleado e ON e.id = s.empleado
            LEFT JOIN estudiante est ON est.id = s.estudiante
            LEFT JOIN matricula m ON m.estudiante = est.id AND (m.est_matricula = '1' OR m.est_matricula IS NULL)
            LEFT JOIN prog_estudios prog ON prog.id = m.prog_estudios
            WHERE s.estado IN ('aprobado', 'pendiente')
            ORDER BY COALESCE(s.fecha_revision, s.fecha_solicitud, NOW()) DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute();
    $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Obtener carreras únicas para el filtro
    $sqlCarreras = "SELECT DISTINCT nom_progest FROM prog_estudios WHERE nom_progest IS NOT NULL AND nom_progest != '' ORDER BY nom_progest";
    $stmtCarreras = $db->prepare($sqlCarreras);
    $stmtCarreras->execute();
    $carreras = $stmtCarreras->fetchAll(PDO::FETCH_COLUMN);

} catch (PDOException $e) {
    $solicitudes = [];
    $errorConsulta = "Error de base de datos: " . $e->getMessage();
    error_log('Error PDO al obtener solicitudes: ' . $e->getMessage());
} catch (Exception $e) {
    $solicitudes = [];
    $errorConsulta = "Error: " . $e->getMessage();
    error_log('Error al obtener solicitudes: ' . $e->getMessage());
}
?>

<style>
  .accordion-button {
    transition: all 0.3s ease;
  }
  .accordion-content {
    transition: max-height 0.3s ease, opacity 0.3s ease;
    overflow: hidden;
    max-height: 0;
    opacity: 0;
  }
  .accordion-open {
    max-height: 1000px;
    opacity: 1;
  }
  .evidencia-img {
    cursor: pointer;
    transition: transform 0.2s;
  }
  .evidencia-img:hover {
    transform: scale(1.1);
  }
</style>

<section class="w-full space-y-6">

  <!-- FILTROS DE BÚSQUEDA -->
  <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
    <input 
      type="text" 
      id="filtroDNI" 
      placeholder="Buscar por DNI" 
      class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
      onkeyup="filtrarSolicitudes()">
    <input 
      type="text" 
      id="filtroNombre" 
      placeholder="Buscar por Nombre" 
      class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
      onkeyup="filtrarSolicitudes()">
    <select 
      id="filtroCarrera" 
      class="w-full border rounded-lg px-3 py-2 focus:ring-2 focus:ring-blue-400 outline-none"
      onchange="filtrarSolicitudes()">
      <option value="">Todas las Carreras</option>
      <?php foreach($carreras as $carrera): ?>
        <option value="<?= htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8') ?>">
          <?= htmlspecialchars($carrera, ENT_QUOTES, 'UTF-8') ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>

  <?php if ($errorConsulta): ?>
    <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700">
      <p class="font-semibold">Error al cargar solicitudes:</p>
      <p><?= htmlspecialchars($errorConsulta, ENT_QUOTES, 'UTF-8') ?></p>
    </div>
  <?php elseif (empty($solicitudes)): ?>
    <div class="bg-white rounded-2xl shadow-lg border border-gray-200 p-8 text-center">
      <div class="text-gray-400 text-6xl mb-4">📋</div>
      <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay solicitudes</h3>
      <p class="text-gray-500">No hay solicitudes pendientes de revisión.</p>
    </div>
  <?php else: ?>
    <div class="grid grid-cols-1 gap-4" id="listaSolicitudes">
      <?php foreach ($solicitudes as $sol): 
        $nombreCompleto = htmlspecialchars($sol['nombre'] ?? 'Sin nombre', ENT_QUOTES, 'UTF-8');
        $dni = htmlspecialchars($sol['dni_est'] ?? '', ENT_QUOTES, 'UTF-8');
        // Si no hay DNI del estudiante, intentar extraer del nombre o usar vacío
        if (empty($dni) && !empty($sol['nombre'])) {
          // Intentar extraer DNI del nombre si está al inicio
          $partes = explode(' ', $sol['nombre']);
          if (count($partes) > 0 && is_numeric($partes[0]) && strlen($partes[0]) == 8) {
            $dni = $partes[0];
          }
        }
        $carrera = htmlspecialchars($sol['carrera'] ?? 'No especificada', ENT_QUOTES, 'UTF-8');
        $estado = $sol['estado'] ?? 'Pendiente';
        $estadoTexto = $estado === 'Aprobado' ? 'aprobado' : ($estado === 'Rechazado' ? 'rechazado' : 'pendiente');
        $resolucion = htmlspecialchars($sol['tipo_solicitud'] ?? 'N/A', ENT_QUOTES, 'UTF-8');
        $archivos = !empty($sol['archivos']) ? explode(",", $sol['archivos']) : [];
      ?>
        <div 
          class="solicitud-item border border-gray-200 rounded-2xl bg-white shadow-lg"
          data-dni="<?= strtolower($dni) ?>"
          data-nombre="<?= strtolower($nombreCompleto) ?>"
          data-carrera="<?= strtolower($carrera) ?>">
          <button 
            class="accordion-button w-full flex justify-between items-center p-5 font-semibold text-gray-800 bg-gradient-to-r from-blue-50 to-white hover:from-blue-100 hover:to-white rounded-t-2xl focus:outline-none">
            <div class="flex flex-col items-start">
              <span><?= $nombreCompleto ?></span>
              <?php if (!empty($dni)): ?>
                <span class="text-xs text-gray-500 font-normal">DNI: <?= $dni ?></span>
              <?php endif; ?>
            </div>
            <span class="text-blue-700 font-bold">▼</span>
          </button>
          <div class="accordion-content p-5 border-t border-gray-200 bg-white space-y-4">
            <!-- Información del Estudiante -->
            <div class="bg-gray-50 rounded-lg p-4">
              <p class="font-semibold text-gray-700 mb-2">Información del Estudiante</p>
              <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                  <span class="text-gray-600">Nombre:</span>
                  <span class="font-medium ml-2"><?= $nombreCompleto ?></span>
                </div>
                <div>
                  <span class="text-gray-600">DNI:</span>
                  <span class="font-medium ml-2"><?= $dni ?></span>
                </div>
                <div>
                  <span class="text-gray-600">Carrera:</span>
                  <span class="font-medium ml-2"><?= $carrera ?></span>
                </div>
                <div>
                  <span class="text-gray-600">Estado:</span>
                  <span class="inline-block <?= $estadoTexto === 'aprobado' ? 'bg-green-300 text-green-900' : ($estadoTexto === 'rechazado' ? 'bg-red-300 text-red-900' : 'bg-yellow-300 text-yellow-900') ?> text-xs font-semibold px-3 py-1 rounded ml-2">
                    <?= $estadoTexto ?>
                  </span>
                </div>
              </div>
            </div>

            <!-- Detalles de la Resolución -->
            <div class="bg-blue-50 rounded-lg p-4">
              <p class="font-semibold text-gray-700 mb-3">Detalles de la Resolución</p>
              <div class="space-y-3">
                <div>
                  <label class="text-sm text-gray-600 font-medium">N° Resolución:</label>
                  <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                    <?= htmlspecialchars($sol['tipo_solicitud'] ?? 'Pendiente', ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>
                
                <div>
                  <label class="text-sm text-gray-600 font-medium">Título:</label>
                  <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                    <?= htmlspecialchars($sol['descripcion'] ?? 'Sin título', ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>

                <div>
                  <label class="text-sm text-gray-600 font-medium">Descripción / Observaciones:</label>
                  <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm min-h-[60px]">
                    <?= htmlspecialchars($sol['descripcion'] ?? 'Sin descripción', ENT_QUOTES, 'UTF-8') ?>
                  </div>
                </div>

                <div class="grid grid-cols-2 gap-3">
                  <div>
                    <label class="text-sm text-gray-600 font-medium">Fecha Solicitud:</label>
                    <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                      <?= date('d/m/Y', strtotime($sol['fecha'] ?? 'now')) ?>
                    </div>
                  </div>
                  <div>
                    <label class="text-sm text-gray-600 font-medium">Fecha Revisión:</label>
                    <div class="mt-1 px-3 py-2 bg-white border border-gray-300 rounded-md text-sm">
                      <?= $sol['fecha_respuesta'] ? date('d/m/Y', strtotime($sol['fecha_respuesta'])) : 'Pendiente' ?>
                    </div>
                  </div>
                </div>
              </div>
            </div>

            <?php 
            // Filtrar archivos vacíos y preparar evidencias válidas
            $evidenciasValidas = [];
            foreach($archivos as $archivo) {
              $archivo = trim($archivo);
              if ($archivo !== '') {
                $evidenciasValidas[] = $archivo;
              }
            }
            
            // Solo mostrar sección de evidencias si hay archivos válidos
            if (!empty($evidenciasValidas)): ?>
            <div>
              <div class="flex items-center gap-2 mb-3">
                <i class="fas fa-paperclip text-indigo-600"></i>
                <p class="font-semibold text-gray-700 text-sm">Evidencias</p>
              </div>
              <div class="flex flex-wrap gap-3 mb-3">
                <?php foreach($evidenciasValidas as $archivo): 
                  // Ruta correcta: desde el contexto del navegador (dashboard-direccion.php está en views/)
                  // Necesitamos subir un nivel para llegar a la raíz, luego uploads/solicitudes/
                  $ruta = "../uploads/solicitudes/" . rawurlencode($archivo);
                  $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                  $archivoSeguro = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');
                  
                  if (in_array($ext, ['jpg','jpeg','png','gif','webp'])): ?>
                    <div 
                      onclick="abrirImagen('<?= $ruta ?>')"
                      class="group relative w-28 h-28 rounded-xl overflow-hidden border-2 border-gray-200 hover:border-indigo-400 cursor-pointer transition-all hover:shadow-xl bg-gray-50">
                      <img 
                        src="<?= $ruta ?>" 
                        alt="<?= $archivoSeguro ?>"
                        class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-300"
                        onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'112\' height=\'112\'%3E%3Crect width=\'112\' height=\'112\' fill=\'%23f3f4f6\'/%3E%3Ctext x=\'50%25\' y=\'50%25\' text-anchor=\'middle\' dy=\'.3em\' fill=\'%239ca3af\' font-size=\'14\'%3EImagen no disponible%3C/text%3E%3C/svg%3E';">
                      <div class="absolute inset-0 bg-black/0 group-hover:bg-black/30 transition-colors flex items-center justify-center">
                        <i class="fas fa-search-plus text-white opacity-0 group-hover:opacity-100 transition-opacity text-lg"></i>
                      </div>
                      <div class="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/60 to-transparent p-2 opacity-0 group-hover:opacity-100 transition-opacity">
                        <p class="text-white text-xs truncate"><?= $archivoSeguro ?></p>
                      </div>
                    </div>
                  <?php else: ?>
                    <a 
                      href="<?= $ruta ?>" 
                      target="_blank"
                      class="flex flex-col items-center justify-center w-28 h-28 rounded-xl border-2 border-gray-200 hover:border-indigo-400 bg-gray-50 hover:bg-indigo-50 transition-all hover:shadow-xl group">
                      <i class="fas fa-file text-2xl text-gray-400 group-hover:text-indigo-600 mb-2 transition-colors"></i>
                      <span class="text-[10px] text-gray-600 group-hover:text-indigo-600 text-center px-2 truncate w-full transition-colors"><?= $archivoSeguro ?></span>
                    </a>
                  <?php endif; ?>
                <?php endforeach; ?>
              </div>
            </div>
            <?php endif; ?>

              <div class="flex gap-3">
                <button 
                  onclick="procesarSolicitud(<?= $sol['id'] ?>, 'Aprobado')"
                  class="flex-1 bg-green-600 text-white py-2 rounded-lg hover:bg-green-700 transition font-semibold btn-action">
                  ACEPTAR
                </button>
                <button 
                  onclick="procesarSolicitud(<?= $sol['id'] ?>, 'Rechazado')"
                  class="flex-1 bg-red-600 text-white py-2 rounded-lg hover:bg-red-700 transition font-semibold btn-action">
                  RECHAZAR
                </button>
              </div>
            </div>
          </div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>

</section>

<!-- Modal para ver imagen -->
<div id="modalImagen" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4" onclick="cerrarModal()">
  <div class="bg-white rounded-xl p-6 max-w-5xl max-h-[90vh] relative shadow-2xl" onclick="event.stopPropagation()">
    <button onclick="cerrarModal()" class="absolute top-4 right-4 w-10 h-10 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 hover:text-gray-900 transition-colors z-10">
      <i class="fas fa-times"></i>
    </button>
    <div class="mb-4">
      <h3 class="text-lg font-bold text-gray-800">Previsualización de Evidencia</h3>
    </div>
    <div class="flex items-center justify-center bg-gray-50 rounded-lg p-4">
      <img id="imagenModal" src="" alt="Evidencia" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
    </div>
  </div>
</div>

<script>
// Funcionalidad del acordeón
document.addEventListener('DOMContentLoaded', function() {
  const accordions = document.querySelectorAll('.accordion-button');
  accordions.forEach(button => {
    button.addEventListener('click', () => {
      const content = button.nextElementSibling;
      content.classList.toggle('accordion-open');

      // Cambiar flecha
      const arrow = button.querySelector('span:last-child');
      arrow.textContent = content.classList.contains('accordion-open') ? '▲' : '▼';
    });
  });
});

// Función para filtrar solicitudes
function filtrarSolicitudes() {
  const filtroDNI = document.getElementById('filtroDNI').value.toLowerCase();
  const filtroNombre = document.getElementById('filtroNombre').value.toLowerCase();
  const filtroCarrera = document.getElementById('filtroCarrera').value.toLowerCase();
  
  const items = document.querySelectorAll('.solicitud-item');
  
  items.forEach(item => {
    const dni = item.getAttribute('data-dni') || '';
    const nombre = item.getAttribute('data-nombre') || '';
    const carrera = item.getAttribute('data-carrera') || '';
    
    const coincideDNI = !filtroDNI || dni.includes(filtroDNI);
    const coincideNombre = !filtroNombre || nombre.includes(filtroNombre);
    const coincideCarrera = !filtroCarrera || carrera.includes(filtroCarrera);
    
    if (coincideDNI && coincideNombre && coincideCarrera) {
      item.style.display = '';
    } else {
      item.style.display = 'none';
    }
  });
}

// Función para abrir imagen en modal
function abrirImagen(ruta) {
  const modal = document.getElementById('modalImagen');
  const img = document.getElementById('imagenModal');
  img.src = ruta;
  modal.classList.remove('hidden');
}

function cerrarModal() {
  document.getElementById('modalImagen').classList.add('hidden');
}

// Función para procesar solicitud (Aceptar/Rechazar)
function procesarSolicitud(id, estado) {
  const accionTexto = estado === 'Aprobado' ? 'aprobar' : 'rechazar';
  
  if (!confirm(`¿Estás seguro de ${accionTexto} esta solicitud?`)) {
    return;
  }

  let motivo = '';
  if (estado === 'Rechazado') {
    motivo = prompt('Por favor, ingresa el motivo del rechazo:');
    if (!motivo) {
      alert('Debes ingresar un motivo para rechazar la solicitud.');
      return;
    }
  }

  // Obtener el botón que disparó el evento
  const buttonContainer = event.target.closest('.flex');
  const buttons = buttonContainer ? buttonContainer.querySelectorAll('button') : [];
  
  // Deshabilitar botones
  buttons.forEach(btn => {
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
  });

  // Determinar la ruta correcta del controlador
  const currentPath = window.location.pathname;
  let controllerPath;
  
  if (currentPath.includes('/views/')) {
    const projectRoot = currentPath.substring(0, currentPath.indexOf('/views/'));
    controllerPath = projectRoot + '/controller/actualizarEstadoSolicitud.php';
  } else {
    controllerPath = '../controller/actualizarEstadoSolicitud.php';
  }

  fetch(controllerPath, {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json'
    },
    body: JSON.stringify({
      id: id,
      estado: estado,
      motivo: motivo || ''
    })
  })
  .then(response => response.json())
  .then(data => {
    if (data.success) {
      alert('Solicitud ' + accionTexto + 'da correctamente.');
      location.reload();
    } else {
      alert('Error: ' + (data.error || 'No se pudo procesar la solicitud'));
      // Rehabilitar botones
      buttons.forEach(btn => {
        btn.disabled = false;
        btn.innerHTML = btn.classList.contains('bg-green-600') ? 'ACEPTAR' : 'RECHAZAR';
      });
    }
  })
  .catch(error => {
    console.error('Error:', error);
    alert('Error al procesar la solicitud. Por favor, intenta nuevamente.');
    // Rehabilitar botones
    buttons.forEach(btn => {
      btn.disabled = false;
      btn.innerHTML = btn.classList.contains('bg-green-600') ? 'ACEPTAR' : 'RECHAZAR';
    });
  });
}
</script>

