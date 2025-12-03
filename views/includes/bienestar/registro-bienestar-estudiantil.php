<?php
require_once __DIR__ . '/../../../controller/bienestar-registroController.php';

// Ruta base para las peticiones AJAX (relativa desde views/)
$basePath = '..';

$ctrl = new BienestarRegistroController();
$status = $_GET['status'] ?? null;
$alerts = [];

if ($status === 'beneficiario_created') {
    $alerts[] = ['type' => 'success', 'text' => 'Beneficiario registrado correctamente.'];
} elseif ($status === 'resolucion_created') {
    $alerts[] = ['type' => 'success', 'text' => 'Resolución registrada correctamente.'];
} elseif ($status === 'error') {
    $alerts[] = ['type' => 'error', 'text' => 'Ocurrió un problema al procesar la solicitud.'];
}

// Recuperar errores y datos previos de la sesión
$errors = $_SESSION['bienestar_errors'] ?? [];
$previousData = $_SESSION['bienestar_previous_data'] ?? [];

// Limpiar datos de sesión después de usarlos
unset($_SESSION['bienestar_errors'], $_SESSION['bienestar_previous_data']);

// Obtener resoluciones para el select
$resoluciones = $ctrl->listarResoluciones();

$oldValue = function (string $key) use ($previousData): string {
    return htmlspecialchars($previousData[$key] ?? '', ENT_QUOTES, 'UTF-8');
};
?>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: translateY(0); }
}
@keyframes shimmer {
    0% { background-position: -1000px 0; }
    100% { background-position: 1000px 0; }
}
.card-anim {
    animation: fadeInUp 0.4s ease-out forwards;
}
.form-card {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.form-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 12px 24px rgba(0,0,0,0.08);
}
.animate-shimmer {
    animation: shimmer 2s infinite;
}
</style>

<!-- Alertas mejoradas -->
<?php if (!empty($alerts)): ?>
    <div class="mb-4 space-y-2 card-anim">
        <?php foreach ($alerts as $alert): ?>
            <div class="relative flex items-center gap-3 p-3 rounded-lg shadow-md backdrop-blur-sm <?= $alert['type'] === 'success' ? 'bg-gradient-to-r from-green-500/10 to-emerald-500/10 border border-green-500/30' : 'bg-gradient-to-r from-red-500/10 to-rose-500/10 border border-red-500/30' ?>">
                <div class="flex-shrink-0 w-8 h-8 rounded-lg <?= $alert['type'] === 'success' ? 'bg-green-500/20' : 'bg-red-500/20' ?> flex items-center justify-center">
                    <i class="fas <?= $alert['type'] === 'success' ? 'fa-check-circle text-green-600' : 'fa-exclamation-circle text-red-600' ?> text-sm"></i>
                </div>
                <p class="font-bold text-sm <?= $alert['type'] === 'success' ? 'text-green-800' : 'text-red-800' ?>">
                    <?= htmlspecialchars($alert['text'], ENT_QUOTES, 'UTF-8') ?>
                </p>
            </div>
        <?php endforeach; ?>
    </div>
<?php endif; ?>

<!-- Errores mejorados -->
<?php if (!empty($errors)): ?>
    <div class="mb-4 p-3 rounded-lg bg-gradient-to-r from-red-500/10 to-rose-500/10 border border-red-500/30 shadow-md backdrop-blur-sm card-anim">
        <div class="flex items-start gap-3">
            <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-red-500/20 flex items-center justify-center">
                <i class="fas fa-exclamation-triangle text-red-600 text-sm"></i>
            </div>
            <div class="flex-1">
                <p class="font-bold text-red-800 text-sm mb-2">Errores encontrados:</p>
                <ul class="space-y-1">
                    <?php foreach ($errors as $error): ?>
                        <li class="flex items-start gap-2 text-red-700 text-xs">
                            <span class="text-red-500 mt-0.5">•</span>
                            <span><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></span>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif; ?>

<!-- Grid de Formularios -->
<section class="grid grid-cols-1 xl:grid-cols-2 gap-3">
    
    <!-- Formulario Beneficiario -->
    <div class="form-card group relative bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100/50 card-anim" style="animation-delay: 0.1s">
        <!-- Header con gradiente mejorado -->
        <div class="relative bg-gradient-to-br from-amber-500 via-orange-500 to-amber-600 p-3 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer" style="background-size: 200% 100%;"></div>
            <div class="relative flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-lg bg-white/25 backdrop-blur-md flex items-center justify-center shadow-lg border border-white/30">
                    <i class="fas fa-user-friends text-white text-base"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-0.5">Registrar Beneficiario</h3>
                    <p class="text-white/90 text-xs font-medium">Agregar nuevo estudiante beneficiario</p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" id="formBeneficiario" class="p-3 space-y-3 bg-gradient-to-b from-white via-gray-50/30 to-white">
            <input type="hidden" name="accion" value="agregar_beneficiario">
            
            <!-- DNI con búsqueda mejorada -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-id-card text-amber-600 text-xs"></i>
                    <span>DNI del Estudiante</span>
                    <span class="text-red-500 text-xs">*</span>
                </label>
                <div class="flex gap-1.5">
                    <div class="flex-1 relative">
                        <input 
                            type="text" 
                            name="dni" 
                            id="dni" 
                            maxlength="8" 
                            required
                            value="<?= $oldValue('dni') ?>" 
                            class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:ring-1 focus:ring-amber-200/50 transition-all text-xs font-medium placeholder-gray-400" 
                            placeholder="Ingrese DNI (8 dígitos)">
                    </div>
                    <button 
                        type="button" 
                        id="btnBuscarDNI"
                        class="px-2.5 py-1.5 bg-gradient-to-r from-blue-500 via-blue-600 to-indigo-600 hover:from-blue-600 hover:via-indigo-600 hover:to-blue-700 text-white rounded-lg font-bold shadow-md hover:shadow-lg transition-all transform hover:scale-105 flex items-center gap-1 text-xs">
                        <i class="fas fa-search text-xs"></i>
                        <span class="hidden sm:inline">Buscar</span>
                    </button>
                </div>
                <div id="infoEstudiante" class="mt-1.5 hidden p-2 bg-gradient-to-r from-blue-50 via-indigo-50 to-blue-50 rounded-lg border-2 border-blue-300/50 shadow-sm animate-fade-in">
                    <div class="flex items-start gap-1.5">
                        <i class="fas fa-check-circle text-blue-600 text-xs mt-0.5"></i>
                        <div class="flex-1">
                            <p class="text-[10px] font-bold text-blue-900 mb-0.5">Estudiante encontrado:</p>
                            <p class="text-xs text-blue-800 font-semibold" id="nombreEstudiante"></p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Información del estudiante (solo lectura) mejorada -->
            <div class="grid grid-cols-3 gap-1.5">
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-md p-1.5 border border-gray-200">
                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5 uppercase tracking-wide">Programa</label>
                    <input 
                        type="text" 
                        id="programa_estudios" 
                        readonly
                        class="w-full px-1.5 py-1 bg-white border-2 border-gray-200 rounded-md text-[10px] font-semibold text-gray-800" 
                        placeholder="Auto">
                </div>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-md p-1.5 border border-gray-200">
                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5 uppercase tracking-wide">Ciclo</label>
                    <input 
                        type="text" 
                        id="ciclo" 
                        readonly
                        class="w-full px-1.5 py-1 bg-white border-2 border-gray-200 rounded-md text-[10px] font-semibold text-gray-800" 
                        placeholder="Auto">
                </div>
                <div class="bg-gradient-to-br from-gray-50 to-gray-100 rounded-md p-1.5 border border-gray-200">
                    <label class="block text-[10px] font-bold text-gray-600 mb-0.5 uppercase tracking-wide">Turno</label>
                    <input 
                        type="text" 
                        id="turno" 
                        readonly
                        class="w-full px-1.5 py-1 bg-white border-2 border-gray-200 rounded-md text-[10px] font-semibold text-gray-800" 
                        placeholder="Auto">
                </div>
            </div>

            <!-- Resolución mejorada -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-file-contract text-amber-600 text-xs"></i>
                    <span>Resolución</span>
                    <span class="text-red-500 text-xs">*</span>
                </label>
                <div class="relative">
                    <select 
                        name="resolucion_id" 
                        required
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:ring-1 focus:ring-amber-200/50 transition-all appearance-none bg-white text-xs font-medium text-gray-800 cursor-pointer">
                        <option value="">Seleccionar resolución</option>
                        <?php foreach ($resoluciones as $res): ?>
                            <option value="<?= $res['id'] ?>" <?= $oldValue('resolucion_id') == $res['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($res['numero_resolucion'] . ' - ' . $res['titulo'], ENT_QUOTES, 'UTF-8') ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none">
                        <i class="fas fa-chevron-down text-gray-400 text-[10px]"></i>
                    </div>
                </div>
            </div>

            <!-- Porcentaje de descuento mejorado -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-percent text-amber-600 text-xs"></i>
                    <span>Porcentaje de Descuento (%)</span>
                    <span class="text-red-500 text-xs">*</span>
                </label>
                <div class="relative">
                    <input 
                        type="number" 
                        name="porcentaje_descuento" 
                        step="0.01" 
                        min="0" 
                        max="100" 
                        required
                        value="<?= $oldValue('porcentaje_descuento') ?>" 
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-amber-500 focus:ring-1 focus:ring-amber-200/50 transition-all text-xs font-medium placeholder-gray-400" 
                        placeholder="Ej: 50.00">
                    <div class="absolute right-2 top-1/2 -translate-y-1/2 pointer-events-none">
                        <span class="text-gray-400 font-semibold text-xs">%</span>
                    </div>
                </div>
            </div>

            <!-- Fechas mejoradas -->
            <div class="grid grid-cols-2 gap-1.5">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5 uppercase tracking-wide">Fecha Inicio</label>
                    <input 
                        type="date" 
                        name="fecha_inicio" 
                        value="<?= $oldValue('fecha_inicio') ?>" 
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-amber-500 focus:ring-1 focus:ring-amber-200/50 transition-all text-xs font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5 uppercase tracking-wide">Fecha Fin</label>
                    <input 
                        type="date" 
                        name="fecha_fin" 
                        value="<?= $oldValue('fecha_fin') ?>" 
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-amber-500 focus:ring-1 focus:ring-amber-200/50 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Botón submit mejorado -->
            <button 
                type="submit"
                class="w-full mt-2.5 py-2 bg-gradient-to-r from-amber-500 via-orange-500 to-amber-600 hover:from-amber-600 hover:via-orange-600 hover:to-amber-700 text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all transform hover:scale-[1.01] flex items-center justify-center gap-1.5">
                <i class="fas fa-user-plus text-xs"></i>
                <span>Agregar Beneficiario</span>
            </button>
        </form>
    </div>

    <!-- Formulario Resolución -->
    <div class="form-card group relative bg-white rounded-xl shadow-lg overflow-hidden border border-gray-100/50 card-anim" style="animation-delay: 0.2s">
        <!-- Header con gradiente mejorado -->
        <div class="relative bg-gradient-to-br from-blue-500 via-indigo-500 to-blue-600 p-3 overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent animate-shimmer" style="background-size: 200% 100%;"></div>
            <div class="relative flex items-center gap-2.5">
                <div class="w-10 h-10 rounded-lg bg-white/25 backdrop-blur-md flex items-center justify-center shadow-lg border border-white/30">
                    <i class="fas fa-file-signature text-white text-base"></i>
                </div>
                <div>
                    <h3 class="text-white font-bold text-base mb-0.5">Nueva Resolución</h3>
                    <p class="text-white/90 text-xs font-medium">Registrar resolución institucional</p>
                </div>
            </div>
        </div>

        <!-- Formulario -->
        <form method="POST" enctype="multipart/form-data" id="formResolucion" class="p-3 space-y-3 bg-gradient-to-b from-white via-gray-50/30 to-white">
            <input type="hidden" name="accion" value="agregar_resolucion">
            
            <!-- Número de resolución mejorado -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-hashtag text-blue-600 text-xs"></i>
                    <span>N° Resolución</span>
                    <span class="text-red-500 text-xs">*</span>
                </label>
                <input 
                    type="text" 
                    name="numero_resolucion" 
                    required
                    value="<?= $oldValue('numero_resolucion') ?>" 
                    class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-200/50 transition-all text-xs font-medium placeholder-gray-400" 
                    placeholder="Ej: RES-2025-001">
            </div>

            <!-- Título mejorado -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-heading text-blue-600 text-xs"></i>
                    <span>Título</span>
                    <span class="text-red-500 text-xs">*</span>
                </label>
                <input 
                    type="text" 
                    name="titulo" 
                    required
                    value="<?= $oldValue('titulo') ?>" 
                    class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-200/50 transition-all text-xs font-medium placeholder-gray-400" 
                    placeholder="Título de la resolución">
            </div>

            <!-- Fechas mejoradas -->
            <div class="grid grid-cols-2 gap-1.5">
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5 uppercase tracking-wide flex items-center gap-1">
                        <i class="fas fa-calendar-alt text-blue-600 text-[10px]"></i>
                        <span>Fecha Resolución</span>
                        <span class="text-red-500 text-[10px]">*</span>
                    </label>
                    <input 
                        type="date" 
                        name="fecha_resolucion" 
                        required
                        value="<?= $oldValue('fecha_resolucion') ?>" 
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-200/50 transition-all text-xs font-medium">
                </div>
                <div>
                    <label class="block text-[10px] font-bold text-gray-700 mb-0.5 uppercase tracking-wide flex items-center gap-1">
                        <i class="fas fa-calendar-times text-blue-600 text-[10px]"></i>
                        <span>Fecha Fin</span>
                        <span class="text-[10px] text-gray-500">(Opcional)</span>
                    </label>
                    <input 
                        type="date" 
                        name="fecha_fin" 
                        value="<?= $oldValue('fecha_fin') ?>" 
                        class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-md focus:border-blue-500 focus:ring-1 focus:ring-blue-200/50 transition-all text-xs font-medium">
                </div>
            </div>

            <!-- Descripción mejorada -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-align-left text-blue-600 text-xs"></i>
                    <span>Descripción / Observaciones</span>
                </label>
                <textarea 
                    rows="3" 
                    name="descripcion" 
                    class="w-full px-2 py-1.5 border-2 border-gray-200 rounded-lg focus:border-blue-500 focus:ring-1 focus:ring-blue-200/50 transition-all resize-none text-xs font-medium placeholder-gray-400" 
                    placeholder="Detalles adicionales o comentarios sobre la resolución"><?= $oldValue('descripcion') ?></textarea>
            </div>

            <!-- Documento mejorado -->
            <div>
                <label class="block text-[10px] font-bold text-gray-800 mb-1 flex items-center gap-1.5">
                    <i class="fas fa-file-upload text-blue-600 text-xs"></i>
                    <span>Documento</span>
                    <span class="text-[10px] text-gray-500 font-normal">(PDF, DOC, DOCX)</span>
                </label>
                <div class="relative">
                    <div class="flex items-center justify-center w-full px-2 py-2.5 border-2 border-dashed border-gray-300 rounded-lg hover:border-blue-400 transition-all cursor-pointer bg-gray-50 hover:bg-blue-50/50 group">
                        <input 
                            type="file" 
                            name="documento" 
                            accept=".pdf,.doc,.docx"
                            class="absolute inset-0 w-full h-full opacity-0 cursor-pointer">
                        <div class="text-center">
                            <i class="fas fa-cloud-upload-alt text-lg text-gray-400 group-hover:text-blue-500 mb-1 transition-colors"></i>
                            <p class="text-[10px] font-semibold text-gray-600 group-hover:text-blue-600">
                                <span class="text-blue-600 underline">Click para subir</span> o arrastra el archivo
                            </p>
                            <p class="text-[10px] text-gray-500 mt-0.5">PDF, DOC, DOCX (máx. 10MB)</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Botón submit mejorado -->
            <button 
                type="submit"
                class="w-full mt-2.5 py-2 bg-gradient-to-r from-blue-500 via-indigo-500 to-blue-600 hover:from-blue-600 hover:via-indigo-600 hover:to-blue-700 text-white font-bold text-xs rounded-lg shadow-md hover:shadow-lg transition-all transform hover:scale-[1.01] flex items-center justify-center gap-1.5">
                <i class="fas fa-file-plus text-xs"></i>
                <span>Agregar Resolución</span>
            </button>
        </form>
    </div>

</section>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const btnBuscarDNI = document.getElementById('btnBuscarDNI');
    const dniInput = document.getElementById('dni');
    const infoEstudiante = document.getElementById('infoEstudiante');
    const nombreEstudiante = document.getElementById('nombreEstudiante');
    const programaEstudios = document.getElementById('programa_estudios');
    const ciclo = document.getElementById('ciclo');
    const turno = document.getElementById('turno');

    // Validación en tiempo real del DNI
    dniInput.addEventListener('input', function() {
        const dni = this.value.trim();
        if (dni.length === 8 && /^\d+$/.test(dni)) {
            this.classList.remove('border-red-300');
            this.classList.add('border-green-300');
        } else if (dni.length > 0) {
            this.classList.remove('border-green-300');
            this.classList.add('border-red-300');
        } else {
            this.classList.remove('border-red-300', 'border-green-300');
        }
    });

    // Buscar estudiante al presionar Enter
    dniInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            btnBuscarDNI.click();
        }
    });

    // Función para mostrar notificación toast
    function showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-50 p-4 rounded-2xl shadow-2xl animate-slide-in ${
            type === 'success' ? 'bg-green-500 text-white' : 
            type === 'error' ? 'bg-red-500 text-white' : 
            'bg-blue-500 text-white'
        }`;
        toast.innerHTML = `
            <div class="flex items-center gap-3">
                <i class="fas ${type === 'success' ? 'fa-check-circle' : type === 'error' ? 'fa-exclamation-circle' : 'fa-info-circle'} text-xl"></i>
                <span class="font-semibold">${message}</span>
            </div>
        `;
        document.body.appendChild(toast);
        setTimeout(() => {
            toast.style.opacity = '0';
            toast.style.transform = 'translateX(100%)';
            setTimeout(() => toast.remove(), 300);
        }, 3000);
    }

    btnBuscarDNI.addEventListener('click', function() {
        const dni = dniInput.value.trim();
        
        if (dni.length !== 8) {
            showToast('El DNI debe tener exactamente 8 dígitos', 'error');
            dniInput.focus();
            return;
        }

        if (!/^\d+$/.test(dni)) {
            showToast('El DNI solo debe contener números', 'error');
            dniInput.focus();
            return;
        }

        // Mostrar loading mejorado
        const originalHTML = btnBuscarDNI.innerHTML;
        btnBuscarDNI.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i><span class="hidden sm:inline">Buscando...</span>';
        btnBuscarDNI.disabled = true;
        btnBuscarDNI.classList.add('opacity-75', 'cursor-not-allowed');

        fetch('<?= $basePath ?>/controller/buscarEstudiante.php?dni=' + dni)
            .then(response => {
                if (!response.ok) {
                    throw new Error('HTTP error! status: ' + response.status);
                }
                return response.json();
            })
            .then(data => {
                if (data.success && data.estudiante) {
                    const est = data.estudiante;
                    nombreEstudiante.textContent = est.nombre_completo || 'N/A';
                    programaEstudios.value = est.programa_nombre || 'N/A';
                    ciclo.value = est.ciclo || 'N/A';
                    const turnoValue = est.turno || '';
                    turno.value = turnoValue === 'D' || turnoValue === 'Diurno' ? 'Diurno' : 
                                  (turnoValue === 'V' || turnoValue === 'Vespertino' ? 'Vespertino' : turnoValue || 'N/A');
                    
                    // Mostrar información con animación
                    infoEstudiante.classList.remove('hidden');
                    infoEstudiante.style.animation = 'fadeIn 0.5s ease-out';
                    showToast('Estudiante encontrado correctamente', 'success');
                    
                    // Resaltar campos llenados
                    [programaEstudios, ciclo, turno].forEach(field => {
                        field.style.transition = 'all 0.3s ease';
                        field.style.backgroundColor = '#dbeafe';
                        setTimeout(() => {
                            field.style.backgroundColor = '';
                        }, 2000);
                    });
                } else {
                    showToast(data.message || 'No se encontró el estudiante con ese DNI', 'error');
                    infoEstudiante.classList.add('hidden');
                    programaEstudios.value = '';
                    ciclo.value = '';
                    turno.value = '';
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showToast('Error al buscar el estudiante. Intenta nuevamente.', 'error');
            })
            .finally(() => {
                btnBuscarDNI.innerHTML = originalHTML;
                btnBuscarDNI.disabled = false;
                btnBuscarDNI.classList.remove('opacity-75', 'cursor-not-allowed');
            });
    });

    // Validación en tiempo real del porcentaje
    const porcentajeInput = document.querySelector('input[name="porcentaje_descuento"]');
    if (porcentajeInput) {
        porcentajeInput.addEventListener('input', function() {
            const value = parseFloat(this.value);
            if (value < 0 || value > 100) {
                this.classList.add('border-red-300');
                showToast('El porcentaje debe estar entre 0 y 100', 'error');
            } else {
                this.classList.remove('border-red-300');
            }
        });
    }

    // Mejorar experiencia de carga de archivos
    const fileInput = document.querySelector('input[type="file"][name="documento"]');
    if (fileInput) {
        fileInput.addEventListener('change', function() {
            const file = this.files[0];
            if (file) {
                const maxSize = 10 * 1024 * 1024; // 10MB
                if (file.size > maxSize) {
                    showToast('El archivo es demasiado grande. Máximo 10MB', 'error');
                    this.value = '';
                    return;
                }
                const fileName = file.name;
                const fileSize = (file.size / 1024 / 1024).toFixed(2);
                showToast(`Archivo seleccionado: ${fileName} (${fileSize} MB)`, 'success');
            }
        });
    }

    // Animación de entrada para los formularios
    const forms = document.querySelectorAll('form');
    forms.forEach((form, index) => {
        form.style.opacity = '0';
        form.style.transform = 'translateY(20px)';
        setTimeout(() => {
            form.style.transition = 'all 0.6s ease-out';
            form.style.opacity = '1';
            form.style.transform = 'translateY(0)';
        }, index * 200);
    });
});
</script>
