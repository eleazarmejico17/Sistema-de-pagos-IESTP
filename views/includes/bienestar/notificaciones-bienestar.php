<?php
require_once __DIR__ . "/../../../config/conexion.php";

// Asegurar que la sesión esté iniciada
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Obtener notificaciones para usuarios de bienestar
$notificaciones = [];
$errorConsulta = null;
$noLeidas = 0;

try {
    $db = Database::getInstance()->getConnection();
    
    // Verificar si la tabla existe, si no, crearla
    $checkTable = $db->query("SHOW TABLES LIKE 'notificaciones'");
    if ($checkTable->rowCount() === 0) {
        // Crear la tabla notificaciones
        $createTable = "CREATE TABLE IF NOT EXISTS notificaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitud_id INT NOT NULL,
            usuario_id INT,
            mensaje TEXT NOT NULL,
            tipo ENUM('Aprobado','Rechazado','Aviso') DEFAULT 'Aviso',
            leido TINYINT(1) DEFAULT 0,
            creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (solicitud_id) REFERENCES solicitud(id) ON DELETE CASCADE,
            FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
            INDEX idx_usuario_leido (usuario_id, leido),
            INDEX idx_solicitud (solicitud_id)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
        $db->exec($createTable);
    }
    
    // Obtener el ID del usuario actual de bienestar
    $usuarioId = $_SESSION['usuario']['id'] ?? null;
    
    if (!$usuarioId) {
        throw new Exception("Usuario no autenticado");
    }
    
    // Obtener notificaciones no leídas relacionadas con solicitudes
    $sql = "SELECT 
                n.id AS notificacion_id,
                n.solicitud_id,
                n.mensaje,
                n.tipo,
                n.leido,
                n.creado_en,
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
                s.fecha_registro
            FROM notificaciones n
            INNER JOIN solicitud s ON s.id = n.solicitud_id
            WHERE n.usuario_id = :usuario_id 
            AND n.tipo = 'Aviso'
            ORDER BY n.leido ASC, n.creado_en DESC";
    
    $stmt = $db->prepare($sql);
    $stmt->execute([':usuario_id' => $usuarioId]);
    $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    // Contar no leídas
    $noLeidas = count(array_filter($notificaciones, fn($n) => !$n['leido']));
    
} catch (PDOException $e) {
    $notificaciones = [];
    $noLeidas = 0;
    $errorConsulta = "Error de base de datos: " . $e->getMessage();
    error_log('Error PDO al obtener notificaciones: ' . $e->getMessage());
} catch (Exception $e) {
    $notificaciones = [];
    $noLeidas = 0;
    $errorConsulta = $e->getMessage();
    error_log('Error al obtener notificaciones: ' . $e->getMessage());
}

// Función para obtener el color según el estado
function getEstadoColor($estado) {
    $colores = [
        'Pendiente' => ['bg' => 'yellow', 'text' => 'yellow-800', 'border' => 'yellow-400', 'icon' => 'fa-clock'],
        'En evaluación' => ['bg' => 'blue', 'text' => 'blue-800', 'border' => 'blue-400', 'icon' => 'fa-hourglass-half'],
        'Aprobado' => ['bg' => 'green', 'text' => 'green-800', 'border' => 'green-400', 'icon' => 'fa-check-circle'],
        'Rechazado' => ['bg' => 'red', 'text' => 'red-800', 'border' => 'red-400', 'icon' => 'fa-times-circle']
    ];
    return $colores[$estado] ?? ['bg' => 'gray', 'text' => 'gray-800', 'border' => 'gray-400', 'icon' => 'fa-question'];
}
?>

<style>
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(15px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}
.card-anim {
    animation: fadeInUp 0.4s ease-out forwards;
}
.notificacion-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.notificacion-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 16px rgba(0,0,0,0.1);
}
.notificacion-no-leida {
    border-left: 4px solid #3b82f6;
    background: linear-gradient(90deg, #eff6ff 0%, #ffffff 10%);
}
.expand-content {
    max-height: 0;
    overflow: hidden;
    transition: max-height 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}
.expand-content.expanded {
    max-height: 2000px;
}
</style>

<!-- Header con contador -->
<div class="mb-4 pb-3 border-b border-gray-200/50">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h2 class="text-xl font-bold text-gray-800 mb-0.5 flex items-center gap-2">
                <i class="fas fa-bell text-indigo-600"></i>
                <span>Notificaciones</span>
                <?php if ($noLeidas > 0): ?>
                    <span class="px-2.5 py-1 bg-red-500 text-white text-xs font-bold rounded-full">
                        <?= $noLeidas ?> nueva<?= $noLeidas > 1 ? 's' : '' ?>
                    </span>
                <?php endif; ?>
            </h2>
            <p class="text-gray-600 text-xs">Gestiona las solicitudes de los estudiantes</p>
        </div>
        <?php if ($noLeidas > 0): ?>
            <button 
                onclick="marcarTodasLeidas()"
                class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-semibold transition-all flex items-center gap-2">
                <i class="fas fa-check-double text-xs"></i>
                <span>Marcar todas como leídas</span>
            </button>
        <?php endif; ?>
    </div>
</div>

<!-- Error -->
<?php if ($errorConsulta): ?>
    <div class="mb-4 p-4 bg-gradient-to-r from-red-50 to-rose-50 border-l-4 border-red-500 rounded-xl shadow-lg card-anim">
        <div class="flex items-start gap-3">
            <i class="fas fa-exclamation-triangle text-red-600 text-lg mt-0.5"></i>
            <div>
                <p class="text-red-800 font-semibold mb-1 text-sm">Error al cargar las notificaciones</p>
                <p class="text-red-700 text-xs"><?= htmlspecialchars($errorConsulta, ENT_QUOTES, 'UTF-8') ?></p>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Estado vacío -->
<?php if (empty($notificaciones)): ?>
    <div class="flex flex-col items-center justify-center py-16 px-4 card-anim">
        <div class="relative mb-4">
            <div class="inline-flex items-center justify-center w-20 h-20 rounded-2xl bg-gradient-to-br from-indigo-100 via-purple-100 to-pink-100 shadow-lg">
                <i class="fas fa-bell-slash text-4xl text-indigo-600"></i>
            </div>
        </div>
        <h3 class="text-xl font-bold text-gray-800 mb-2">No hay notificaciones</h3>
        <p class="text-gray-600 text-center mb-6 max-w-md mx-auto text-sm leading-relaxed">
            Las nuevas solicitudes de estudiantes aparecerán aquí para su revisión.
        </p>
    </div>
<?php else: ?>
    <!-- Lista de notificaciones -->
    <div class="space-y-3">
        <?php foreach ($notificaciones as $index => $notif): 
            $estado = $notif['estado'] ?? 'Pendiente';
            $color = getEstadoColor($estado);
            $fechaRegistro = !empty($notif['fecha_registro']) ? date('d/m/Y H:i', strtotime($notif['fecha_registro'])) : 'N/A';
            $fechaNotificacion = !empty($notif['creado_en']) ? date('d/m/Y H:i', strtotime($notif['creado_en'])) : 'N/A';
            $noLeida = !$notif['leido'];
        ?>
        <div class="notificacion-card bg-white rounded-xl shadow-md hover:shadow-xl border border-gray-100 overflow-hidden card-anim <?= $noLeida ? 'notificacion-no-leida' : '' ?>" 
             style="animation-delay: <?= $index * 0.05 ?>s"
             data-notificacion-id="<?= $notif['notificacion_id'] ?>"
             data-solicitud-id="<?= $notif['solicitud_id'] ?>">
            
            <!-- Header clickeable -->
            <div class="flex justify-between items-start p-4 cursor-pointer hover:bg-gradient-to-r hover:from-gray-50 hover:to-indigo-50/30 transition-all"
                 onclick="toggleNotificacion(<?= $notif['notificacion_id'] ?>, <?= $notif['solicitud_id'] ?>)">
                <div class="flex-1 min-w-0">
                    <div class="flex items-start gap-3 mb-2">
                        <?php if ($noLeida): ?>
                            <div class="w-2 h-2 bg-blue-500 rounded-full mt-2 flex-shrink-0 animate-pulse"></div>
                        <?php endif; ?>
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-<?= $color['bg'] ?>-100 to-<?= $color['bg'] ?>-200 flex items-center justify-center flex-shrink-0 shadow-sm">
                            <i class="fas <?= $color['icon'] ?> text-<?= $color['text'] ?> text-base"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1.5 flex-wrap">
                                <h3 class="text-base font-bold text-gray-800 truncate"><?= htmlspecialchars($notif['nombre'], ENT_QUOTES, 'UTF-8') ?></h3>
                                <span class="px-2 py-0.5 rounded-lg text-xs font-bold bg-gradient-to-r from-<?= $color['bg'] ?>-100 to-<?= $color['bg'] ?>-200 text-<?= $color['text'] ?> border border-<?= $color['border'] ?>/50 flex items-center gap-1">
                                    <i class="fas <?= $color['icon'] ?> text-[10px]"></i><?= htmlspecialchars($estado, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                            </div>
                            <p class="text-xs text-gray-600 mb-1.5"><?= htmlspecialchars($notif['mensaje'], ENT_QUOTES, 'UTF-8') ?></p>
                            <div class="flex flex-wrap gap-x-3 gap-y-1 text-xs text-gray-500">
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-calendar text-gray-400 text-[10px]"></i>
                                    <?= htmlspecialchars($fechaNotificacion, ENT_QUOTES, 'UTF-8') ?>
                                </span>
                                <span class="flex items-center gap-1.5">
                                    <i class="fas fa-tag text-gray-400 text-[10px]"></i>
                                    <strong class="truncate"><?= htmlspecialchars($notif['tipo_solicitud'], ENT_QUOTES, 'UTF-8') ?></strong>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
                <button class="ml-3 p-1.5 rounded-lg hover:bg-gray-100 transition-colors flex-shrink-0">
                    <i class="fas fa-chevron-down text-gray-400 text-sm transition-transform duration-300" id="arrow-<?= $notif['notificacion_id'] ?>"></i>
                </button>
            </div>

            <!-- Contenido expandible -->
            <div class="expand-content bg-gradient-to-b from-gray-50/50 to-white" id="content-<?= $notif['notificacion_id'] ?>">
                <div class="px-4 pb-4 pt-2 space-y-3">
                    <!-- Información de contacto -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2">
                        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2 mb-1.5">
                                <i class="fas fa-phone text-indigo-600 text-sm"></i>
                                <p class="font-semibold text-gray-700 text-xs">Teléfono</p>
                            </div>
                            <p class="text-gray-700 text-xs"><?= htmlspecialchars($notif['telefono'], ENT_QUOTES, 'UTF-8') ?></p>
                        </div>
                        <?php if (!empty($notif['correo'])): ?>
                            <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                                <div class="flex items-center gap-2 mb-1.5">
                                    <i class="fas fa-envelope text-indigo-600 text-sm"></i>
                                    <p class="font-semibold text-gray-700 text-xs">Correo</p>
                                </div>
                                <p class="text-gray-700 text-xs truncate"><?= htmlspecialchars($notif['correo'], ENT_QUOTES, 'UTF-8') ?></p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Descripción -->
                    <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                        <div class="flex items-center gap-2 mb-2">
                            <i class="fas fa-align-left text-indigo-600 text-sm"></i>
                            <p class="font-semibold text-gray-700 text-xs">Descripción</p>
                        </div>
                        <p class="text-gray-700 text-xs leading-relaxed">
                            <?= nl2br(htmlspecialchars($notif['descripcion'], ENT_QUOTES, 'UTF-8')) ?>
                        </p>
                    </div>

                    <!-- Archivos -->
                    <?php if (!empty($notif['archivos'])): ?>
                        <div class="bg-white rounded-lg p-3 border border-gray-200 shadow-sm">
                            <div class="flex items-center gap-2 mb-2">
                                <i class="fas fa-paperclip text-indigo-600 text-sm"></i>
                                <p class="font-semibold text-gray-700 text-xs">Evidencias Adjuntas</p>
                            </div>
                            <div class="flex flex-wrap gap-2">
                                <?php 
                                $archivos = explode(",", $notif['archivos']);
                                foreach($archivos as $archivo){
                                    $archivo = trim($archivo);
                                    if ($archivo === '') continue;
                                    
                                    $ruta = "../uploads/solicitudes/" . rawurlencode($archivo);
                                    $ext = strtolower(pathinfo($archivo, PATHINFO_EXTENSION));
                                    $archivoSeguro = htmlspecialchars($archivo, ENT_QUOTES, 'UTF-8');

                                    if (in_array($ext, ['jpg','jpeg','png','gif','webp'])) {
                                        echo "
                                        <div onclick='abrirImagen(\"$ruta\")'
                                             class='group/thumb relative w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-indigo-400 cursor-pointer transition-all hover:shadow-lg'>
                                            <img src='$ruta' alt='$archivoSeguro' class='w-full h-full object-cover group-hover/thumb:scale-110 transition-transform duration-300'>
                                            <div class='absolute inset-0 bg-black/0 group-hover/thumb:bg-black/30 transition-colors flex items-center justify-center'>
                                                <i class='fas fa-search-plus text-white opacity-0 group-hover/thumb:opacity-100 transition-opacity text-sm'></i>
                                            </div>
                                        </div>";
                                    } else {
                                        echo "
                                        <a href='$ruta' target='_blank'
                                           class='flex flex-col items-center justify-center w-20 h-20 rounded-lg border-2 border-gray-200 hover:border-indigo-400 bg-gray-50 hover:bg-indigo-50 transition-all hover:shadow-lg'>
                                            <i class='fas fa-file text-xl text-gray-400 mb-1'></i>
                                            <span class='text-[10px] text-gray-600 text-center px-1 truncate w-full'>$archivoSeguro</span>
                                        </a>";
                                    }
                                }
                                ?>
                            </div>
                        </div>
                    <?php endif; ?>

                    <!-- Botones de acción -->
                    <?php if ($estado === 'Pendiente' || $estado === 'En evaluación'): ?>
                        <div class="flex gap-2 pt-1">
                            <button onclick="accionSolicitudDesdeNotificacion(<?= $notif['solicitud_id'] ?>, <?= $notif['notificacion_id'] ?>, 'Aprobado')"
                                class="flex-1 py-2.5 px-4 bg-gradient-to-r from-green-500 to-emerald-600 hover:from-green-600 hover:to-emerald-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-check-circle text-sm"></i>
                                <span>Aprobar</span>
                            </button>

                            <button onclick="accionSolicitudDesdeNotificacion(<?= $notif['solicitud_id'] ?>, <?= $notif['notificacion_id'] ?>, 'Rechazado')"
                                class="flex-1 py-2.5 px-4 bg-gradient-to-r from-red-500 to-rose-600 hover:from-red-600 hover:to-rose-700 text-white font-bold rounded-lg shadow-md hover:shadow-lg transition-all transform hover:scale-[1.02] flex items-center justify-center gap-2 text-sm">
                                <i class="fas fa-times-circle text-sm"></i>
                                <span>Rechazar</span>
                            </button>
                        </div>
                    <?php else: ?>
                        <div class="p-3 bg-gradient-to-r from-<?= $color['bg'] ?>-50 to-<?= $color['bg'] ?>-100 border border-<?= $color['border'] ?>/50 rounded-lg">
                            <div class="flex items-center gap-2">
                                <i class="fas <?= $color['icon'] ?> text-<?= $color['text'] ?> text-sm"></i>
                                <p class="text-xs font-semibold text-<?= $color['text'] ?>">
                                    Esta solicitud ya ha sido procesada.
                                </p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Modal de imagen -->
<div id="modalImagen" class="hidden fixed inset-0 bg-black/80 backdrop-blur-sm z-50 flex items-center justify-center p-4">
    <div class="bg-white rounded-xl p-4 max-w-5xl max-h-[90vh] relative shadow-2xl">
        <button onclick="cerrarModalImagen()" class="absolute top-3 right-3 w-8 h-8 rounded-full bg-gray-100 hover:bg-gray-200 flex items-center justify-center text-gray-700 hover:text-gray-900 transition-colors">
            <i class="fas fa-times text-sm"></i>
        </button>
        <div class="mb-3">
            <h3 class="text-lg font-bold text-gray-800">Previsualización</h3>
        </div>
        <img id="imagenModal" src="" class="max-w-full max-h-[75vh] object-contain rounded-lg shadow-lg">
    </div>
</div>

<script>
let abierto = null;

function toggleNotificacion(notificacionId, solicitudId) {
    const content = document.getElementById('content-' + notificacionId);
    const arrow = document.getElementById('arrow-' + notificacionId);
    
    if (abierto && abierto !== content) {
        abierto.classList.remove('expanded');
        const prevArrow = document.getElementById('arrow-' + abierto.id.split('-')[1]);
        if (prevArrow) {
            prevArrow.style.transform = "rotate(0deg)";
        }
    }
    
    if (content.classList.contains('expanded')) {
        content.classList.remove('expanded');
        arrow.style.transform = "rotate(0deg)";
        abierto = null;
    } else {
        content.classList.add('expanded');
        arrow.style.transform = "rotate(180deg)";
        abierto = content;
        
        // Marcar como leída al expandir
        marcarComoLeida(notificacionId);
    }
}

function marcarComoLeida(notificacionId) {
    fetch('../../controller/marcarNotificacionLeida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            notificacion_id: notificacionId
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remover el indicador de no leída
            const card = document.querySelector(`[data-notificacion-id="${notificacionId}"]`);
            if (card) {
                card.classList.remove('notificacion-no-leida');
                const dot = card.querySelector('.bg-blue-500');
                if (dot) dot.remove();
            }
        }
    })
    .catch(error => {
        console.error('Error al marcar como leída:', error);
    });
}

function marcarTodasLeidas() {
    const noLeidas = document.querySelectorAll('.notificacion-no-leida');
    const ids = Array.from(noLeidas).map(card => card.dataset.notificacionId);
    
    fetch('../../controller/marcarNotificacionLeida.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({
            todas: true
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            noLeidas.forEach(card => {
                card.classList.remove('notificacion-no-leida');
                const dot = card.querySelector('.bg-blue-500');
                if (dot) dot.remove();
            });
            location.reload();
        }
    })
    .catch(error => {
        console.error('Error al marcar todas como leídas:', error);
    });
}

function abrirImagen(src) {
    document.getElementById('imagenModal').src = src;
    document.getElementById('modalImagen').classList.remove('hidden');
}

function cerrarModalImagen() {
    document.getElementById('modalImagen').classList.add('hidden');
}

function accionSolicitudDesdeNotificacion(solicitudId, notificacionId, estado) {
    const accionTexto = estado === 'Aprobado' ? 'aprobar' : 'rechazar';
    
    if (!confirm(`¿Estás seguro de ${accionTexto} esta solicitud?`)) {
        return;
    }

    const motivo = estado === 'Rechazado' 
        ? prompt('Por favor, ingresa el motivo del rechazo:') 
        : '';

    if (estado === 'Rechazado' && !motivo) {
        alert('Debes ingresar un motivo para rechazar la solicitud.');
        return;
    }

    // Obtener el botón que disparó el evento
    const buttonContainer = event.target.closest('.flex');
    const buttons = buttonContainer ? buttonContainer.querySelectorAll('button') : [];
    
    // Deshabilitar botones
    buttons.forEach(btn => {
        btn.disabled = true;
        const originalHTML = btn.innerHTML;
        btn.dataset.originalHtml = originalHTML;
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
            id: solicitudId,
            estado: estado,
            motivo: motivo || ''
        })
    })
    .then(response => {
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            // Marcar notificación como leída
            marcarComoLeida(notificacionId);
            
            const alertDiv = document.createElement('div');
            alertDiv.className = 'fixed top-4 right-4 bg-green-500 text-white px-4 py-3 rounded-lg shadow-2xl z-50 flex items-center gap-2 animate-slide-in';
            alertDiv.innerHTML = `
                <i class="fas fa-check-circle text-lg"></i>
                <div>
                    <p class="font-bold text-sm">${data.message}</p>
                </div>
            `;
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                alertDiv.style.opacity = '0';
                alertDiv.style.transform = 'translateX(100%)';
                setTimeout(() => alertDiv.remove(), 300);
            }, 3000);
            
            setTimeout(() => location.reload(), 1000);
        } else {
            const errorMsg = data.error || 'No se pudo actualizar la solicitud';
            alert('❌ Error: ' + errorMsg);
            console.error('Error del servidor:', data);
            buttons.forEach(btn => {
                btn.disabled = false;
                btn.innerHTML = btn.dataset.originalHtml || '';
            });
        }
    })
    .catch(error => {
        console.error('Error completo:', error);
        alert('❌ Error al procesar la solicitud. Verifica la conexión y vuelve a intentar.');
        buttons.forEach(btn => {
            btn.disabled = false;
            btn.innerHTML = btn.dataset.originalHtml || '';
        });
    });
}

document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('button[onclick*="accionSolicitudDesdeNotificacion"]').forEach(btn => {
        btn.dataset.originalHtml = btn.innerHTML;
    });
});
</script>

<style>
@keyframes slideIn {
    from {
        opacity: 0;
        transform: translateX(100%);
    }
    to {
        opacity: 1;
        transform: translateX(0);
    }
}
.animate-slide-in {
    animation: slideIn 0.3s ease-out;
}
</style>

