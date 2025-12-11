<?php
require_once __DIR__ . '/../../../config/conexion.php';
$pdo = Conexion::getInstance()->getConnection();

// --------------------------------------------------
// NOTIFICACIONES MEJORADAS
// --------------------------------------------------
function showMessage($type) {
    $messages = [
        "creado"     => ["Registro creado correctamente", "bg-emerald-50 border-emerald-200 text-emerald-800", "fa-check-circle"],
        "actualizado"=> ["Registro actualizado correctamente", "bg-blue-50 border-blue-200 text-blue-800", "fa-check-circle"],
        "eliminado"  => ["Registro eliminado correctamente", "bg-red-50 border-red-200 text-red-800", "fa-trash-alt"],
        "error"      => ["Ha ocurrido un error", "bg-red-50 border-red-200 text-red-800", "fa-exclamation-circle"]
    ];

    if (!isset($messages[$type])) return;

    [$text, $color, $icon] = $messages[$type];

    echo "
        <div class='alert-auto p-4 mb-6 border-l-4 rounded-lg shadow-lg animate-slideDown $color flex items-center gap-3'>
            <i class='fas $icon text-xl'></i>
            <p class='font-semibold'>$text</p>
        </div>
        <style>
            @keyframes slideDown { 
                from { opacity:0; transform: translateY(-20px); } 
                to { opacity:1; transform: translateY(0); } 
            }
            .animate-slideDown { animation: slideDown .4s ease-out; }
        </style>
    ";
}

// ---------------------------------------------------------------------
// NOTA: Las acciones (crear, editar, eliminar) se procesan en 
// dashboard-admin.php ANTES de cualquier output para evitar 
// errores de "headers already sent"
// ---------------------------------------------------------------------

// ---------------------------------------------------------------------
// FORMULARIO DE EDICIÓN
// ---------------------------------------------------------------------
$editData = null;

if (isset($_GET['editar'])) {
    $stmt = $pdo->prepare("SELECT * FROM tipo_pago WHERE id = :id LIMIT 1");
    $stmt->execute([":id" => $_GET['editar']]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);
}

// ---------------------------------------------------------------------
// LISTADO
// ---------------------------------------------------------------------
$stmt = $pdo->query("SELECT * FROM tipo_pago ORDER BY id DESC");
$lista = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Lista de conceptos que ve el usuario (numerados tipo 1.1, 1.2, etc.)
try {
    $stmtConceptos = $pdo->query("\n        SELECT id, nombre, descripcion, COALESCE(uit, 0.00) AS uit\n        FROM tipo_pago\n        WHERE nombre REGEXP '^[0-9]+\\.[0-9]+$'\n        ORDER BY CAST(SUBSTRING_INDEX(nombre, '.', 1) AS UNSIGNED) ASC,\n                 CAST(SUBSTRING_INDEX(nombre, '.', -1) AS UNSIGNED) ASC\n    ");
    $conceptosUsuario = $stmtConceptos->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Fallback simple si REGEXP no está disponible
    $stmtConceptos = $pdo->query("\n        SELECT id, nombre, descripcion, COALESCE(uit, 0.00) AS uit\n        FROM tipo_pago\n        WHERE nombre LIKE '%.%'\n        ORDER BY nombre ASC\n    ");
    $conceptosUsuario = $stmtConceptos->fetchAll(PDO::FETCH_ASSOC);
}

?>

<div class="max-w-6xl mx-auto space-y-6">

    <!-- ALERTAS -->
    <?php if (isset($_GET['msg'])) showMessage($_GET['msg']); ?>

    <div class="grid lg:grid-cols-[minmax(0,1.1fr)_minmax(0,1.2fr)] gap-6 items-start">

      <!-- FORMULARIO MEJORADO (COLUMNA IZQUIERDA) -->
      <div class="bg-gradient-to-br from-white to-gray-50 p-8 rounded-3xl shadow-xl border border-gray-200 hover:shadow-2xl transition-all duration-300">
        <div class="flex flex-col md:flex-row md:items-center gap-4 mb-6">
            <div class="flex items-center gap-3 flex-1">
                <div class="p-3 bg-gradient-to-br from-emerald-500 to-emerald-600 rounded-2xl shadow-lg">
                    <i class="fas <?= $editData ? 'fa-edit' : 'fa-plus-circle' ?> text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl md:text-3xl font-bold text-gray-800 tracking-tight">
                        <?= $editData ? "Editar Tipo de Pago" : "Registrar Tipo de Pago" ?>
                    </h2>
                    <p class="text-gray-500 text-xs md:text-sm mt-1">
                        <?= $editData 
                            ? "Actualiza el nombre, descripción y monto que verán los usuarios al pagar."
                            : "Configura los conceptos y métodos de pago que estarán disponibles para los usuarios." ?>
                    </p>
                </div>
            </div>
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-100">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span><?= $editData ? "Modo edición" : "Nuevo registro" ?></span>
                </span>
                <?php if ($editData): ?>
                    <a href="dashboard-admin.php?pagina=admin-tipo-pago" 
                       class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 rounded-xl text-xs md:text-sm transition-all flex items-center gap-2 border border-gray-200">
                        <i class="fas fa-times"></i>
                        <span>Cancelar</span>
                    </a>
                <?php endif; ?>
            </div>
        </div>

        <form method="POST" class="space-y-6">
            <input type="hidden" name="accion" value="<?= $editData ? "editar" : "crear" ?>">
            <?php if ($editData): ?>
                <input type="hidden" name="id" value="<?= htmlspecialchars($editData['id'], ENT_QUOTES, 'UTF-8'); ?>">
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="space-y-2">
                    <label class="flex items-center gap-2 font-semibold text-gray-700">
                        <i class="fas fa-tag text-emerald-600"></i>
                        <span>Nombre del Tipo de Pago <span class="text-red-500">*</span></span>
                    </label>
                    <input 
                        required
                        type="text"
                        name="nombre"
                        value="<?= $editData ? htmlspecialchars($editData['nombre'], ENT_QUOTES, 'UTF-8') : "" ?>"
                        placeholder="Ej: 1.1, 1.2 o nombre del método (Yape, Efectivo)"
                        class="w-full px-5 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-gray-700 placeholder-gray-400"
                    >
                    <p class="text-[11px] text-gray-400">Para conceptos de pago usa códigos como <span class="font-semibold">1.1, 1.2, 3.1</span>. Para métodos de pago usa un nombre descriptivo.</p>
                </div>

                <div class="space-y-2">
                    <label class="flex items-center gap-2 font-semibold text-gray-700">
                        <i class="fas fa-coins text-emerald-600"></i>
                        <span>UIT / Monto</span>
                    </label>
                    <input
                        type="number"
                        name="uit"
                        step="0.01"
                        min="0"
                        value="<?= $editData ? htmlspecialchars($editData['uit'] ?? '0.00', ENT_QUOTES, 'UTF-8') : '' ?>"
                        placeholder="Ej: 18.00"
                        class="w-full px-5 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-gray-700 placeholder-gray-400"
                    >
                    <p class="text-[11px] text-gray-400">Este valor es el que verá el usuario como costo del concepto en la pantalla de pagos.</p>
                </div>
            </div>

            <div class="space-y-2">
                <label class="flex items-center gap-2 font-semibold text-gray-700">
                    <i class="fas fa-align-left text-emerald-600"></i>
                    <span>Descripción</span>
                </label>
                <textarea 
                    name="descripcion"
                    placeholder="Describe brevemente este concepto o método de pago..."
                    rows="3"
                    class="w-full px-5 py-3 border-2 border-gray-200 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-all text-gray-700 placeholder-gray-400 resize-none"><?= $editData ? htmlspecialchars($editData['descripcion'], ENT_QUOTES, 'UTF-8') : "" ?></textarea>
            </div>

            <button 
                type="submit"
                class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-semibold py-3.5 rounded-2xl shadow-lg hover:shadow-xl transition-all duration-300 flex items-center justify-center gap-3 transform hover:scale-[1.02]">
                <i class="fas <?= $editData ? 'fa-save' : 'fa-plus-circle' ?> text-lg"></i>
                <span class="text-sm md:text-base"><?= $editData ? "Guardar Cambios" : "Registrar Tipo de Pago" ?></span>
            </button>
        </form>
      </div>

      <!-- COLUMNA DERECHA: TABLAS -->
      <div class="space-y-4">

        <!-- Botones tipo pestañas -->
        <div class="inline-flex bg-gray-100 rounded-full p-1 border border-gray-200">
          <button id="tab-tipos" type="button"
                  class="tab-btn inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold rounded-full bg-white text-blue-600 shadow-sm border border-blue-100">
            <i class="fas fa-list"></i>
            <span>Tipos de Pago</span>
          </button>
          <button id="tab-conceptos" type="button"
                  class="tab-btn inline-flex items-center gap-2 px-5 py-2 text-xs font-semibold rounded-full text-gray-500 hover:text-blue-600">
            <i class="fas fa-credit-card"></i>
            <span>Conceptos (Usuario)</span>
          </button>
        </div>

        <!-- TABLA MEJORADA -->
        <div id="panel-tipos" class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg">
                    <i class="fas fa-list text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Lista de Tipos de Pago</h2>
                    <p class="text-gray-500 text-sm mt-1">Gestiona todos los métodos de pago disponibles</p>
                </div>
            </div>
            <div class="flex items-center gap-2 px-4 py-2 bg-blue-50 rounded-lg">
                <i class="fas fa-calculator text-blue-600"></i>
                <span class="text-blue-700 font-semibold"><?= count($lista) ?> registros</span>
            </div>
        </div>

        <?php if (empty($lista)): ?>
            <div class="text-center py-12">
                <div class="inline-block p-6 bg-gray-100 rounded-full mb-4">
                    <i class="fas fa-inbox text-5xl text-gray-400"></i>
                </div>
                <h3 class="text-xl font-semibold text-gray-600 mb-2">No hay tipos de pago registrados</h3>
                <p class="text-gray-500">Comienza agregando tu primer método de pago usando el formulario de arriba</p>
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="w-full">
                    <thead>
                        <tr class="bg-gradient-to-r from-gray-50 to-gray-100 border-b-2 border-gray-200">
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-hashtag text-gray-500"></i>
                                    <span>ID</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-tag text-gray-500"></i>
                                    <span>Nombre</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-align-left text-gray-500"></i>
                                    <span>Descripción</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center justify-end gap-2">
                                    <i class="fas fa-coins text-gray-500"></i>
                                    <span>UIT</span>
                                </div>
                            </th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                <div class="flex items-center justify-center gap-2">
                                    <i class="fas fa-cog text-gray-500"></i>
                                    <span>Acciones</span>
                                </div>
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach ($lista as $index => $item): ?>
                            <tr class="hover:bg-gradient-to-r hover:from-blue-50 hover:to-indigo-50 transition-all duration-200 <?= $editData && $editData['id'] == $item['id'] ? 'bg-blue-50 border-l-4 border-blue-500' : '' ?>">
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="flex items-center">
                                        <span class="inline-flex items-center justify-center w-10 h-10 rounded-full bg-gradient-to-br from-gray-100 to-gray-200 text-gray-700 font-bold text-sm">
                                            <?= htmlspecialchars($item['id'], ENT_QUOTES, 'UTF-8') ?>
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm font-semibold text-gray-900">
                                        <?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-600 max-w-md">
                                        <?= htmlspecialchars($item['descripcion'] ?: 'Sin descripción', ENT_QUOTES, 'UTF-8') ?>
                                    </div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right">
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-blue-50 text-blue-700 border border-blue-100">
                                        <?= isset($item['uit']) ? number_format((float)$item['uit'], 2, '.', '') : '0.00' ?>
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-center">
                                    <div class="flex items-center justify-center gap-2">
                                        <a href="dashboard-admin.php?pagina=admin-tipo-pago&editar=<?= $item['id'] ?>"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                            <i class="fas fa-edit"></i>
                                            <span>Editar</span>
                                        </a>
                                        <a href="dashboard-admin.php?pagina=admin-tipo-pago&eliminar=<?= $item['id'] ?>"
                                           onclick="return confirmarEliminacion('<?= htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8') ?>')"
                                           class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 text-white rounded-lg shadow-md hover:shadow-lg transition-all duration-300 transform hover:scale-105">
                                            <i class="fas fa-trash-alt"></i>
                                            <span>Eliminar</span>
                                        </a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>

        <!-- VISTA COMO USUARIO (CONCEPTOS DISPONIBLES) -->
        <div id="panel-conceptos" class="bg-white p-8 rounded-2xl shadow-xl border border-gray-200 hidden">
        <div class="flex items-center justify-between mb-6">
            <div class="flex items-center gap-3">
                <div class="p-3 bg-gradient-to-br from-indigo-500 to-blue-600 rounded-xl shadow-lg">
                    <i class="fas fa-credit-card text-white text-2xl"></i>
                </div>
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Vista de Conceptos (como usuario)</h2>
                    <p class="text-gray-500 text-sm mt-1">Así se muestran los conceptos de pago en el módulo de PAGOS del usuario.</p>
                </div>
            </div>
            <div class="text-xs px-4 py-2 bg-blue-50 text-blue-700 rounded-full font-semibold border border-blue-100">
                <?= count($conceptosUsuario) ?> conceptos
            </div>
        </div>

        <?php if (empty($conceptosUsuario)): ?>
            <div class="text-center py-10 text-gray-500 text-sm">
                No hay conceptos numerados (1.1, 1.2, etc.) registrados todavía.
            </div>
        <?php else: ?>
            <div class="overflow-x-auto rounded-xl border border-gray-200">
                <table class="min-w-full bg-white border-collapse">
                    <thead>
                        <tr class="bg-gray-100 border-b border-gray-200">
                            <th class="py-3 px-4 border-r border-gray-200 text-left text-xs font-semibold text-gray-600">N°</th>
                            <th class="py-3 px-4 border-r border-gray-200 text-left text-xs font-semibold text-gray-600">CONCEPTO</th>
                            <th class="py-3 px-4 border-r border-gray-200 text-right text-xs font-semibold text-gray-600">UIT</th>
                            <th class="py-3 px-4 text-center text-xs font-semibold text-gray-600">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($conceptosUsuario as $item): 
                            $numero = htmlspecialchars($item['nombre'], ENT_QUOTES, 'UTF-8');
                            $concepto = htmlspecialchars($item['descripcion'], ENT_QUOTES, 'UTF-8');
                            $uit = isset($item['uit']) ? number_format((float)$item['uit'], 2, '.', '') : number_format(0.00, 2, '.', '');
                            $idConcepto = (int)$item['id'];
                        ?>
                            <tr class="hover:bg-gray-50 border-t border-gray-100">
                                <td class="py-3 px-4 text-gray-700 border-r border-gray-100 align-middle">
                                    <?= $numero ?>
                                </td>
                                <td class="py-3 px-4 text-gray-700 border-r border-gray-100 align-middle">
                                    <?= $concepto ?>
                                </td>
                                <td class="py-3 px-4 text-gray-700 text-right font-medium border-r border-gray-100 align-middle">
                                    <?= $uit ?>
                                </td>
                                <td class="py-3 px-4 text-center align-middle">
                                    <a href="dashboard-admin.php?pagina=admin-tipo-pago&editar=<?= $idConcepto ?>"
                                       class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-500 hover:bg-blue-600 text-white text-xs font-semibold rounded-lg shadow-sm">
                                        <i class="fas fa-pen"></i>
                                        <span>Editar</span>
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
        </div>

      </div> <!-- fin columna derecha -->

    </div> <!-- fin grid principal -->
</div>

<script>
// Auto-ocultar alertas después de 4 segundos
setTimeout(() => {
    const alert = document.querySelector('.alert-auto');
    if (alert) {
        alert.style.transition = 'opacity 0.5s ease-out';
        alert.style.opacity = '0';
        setTimeout(() => alert.remove(), 500);
    }
}, 4000);

// Función mejorada para confirmar eliminación
function confirmarEliminacion(nombre) {
    return confirm(`¿Estás seguro de que deseas eliminar el tipo de pago "${nombre}"?\n\nEsta acción no se puede deshacer.`);
}

// Scroll suave al formulario cuando se está editando
<?php if ($editData): ?>
document.addEventListener('DOMContentLoaded', function() {
    const formCard = document.querySelector('.bg-gradient-to-br.from-white');
    if (formCard) {
        setTimeout(() => {
            formCard.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }, 300);
    }
});
<?php endif; ?>

// Tabs para alternar tablas en columna derecha
document.addEventListener('DOMContentLoaded', function() {
    const tabTipos = document.getElementById('tab-tipos');
    const tabConceptos = document.getElementById('tab-conceptos');
    const panelTipos = document.getElementById('panel-tipos');
    const panelConceptos = document.getElementById('panel-conceptos');

    if (!tabTipos || !tabConceptos || !panelTipos || !panelConceptos) return;

    function activarTab(t) {
        const esTipos = t === 'tipos';

        panelTipos.classList.toggle('hidden', !esTipos);
        panelConceptos.classList.toggle('hidden', esTipos);

        if (esTipos) {
            tabTipos.classList.add('bg-white','text-blue-600','shadow-sm','border','border-blue-100');
            tabConceptos.classList.remove('bg-white','text-blue-600','shadow-sm','border','border-blue-100');
            tabConceptos.classList.add('text-gray-500');
        } else {
            tabConceptos.classList.add('bg-white','text-blue-600','shadow-sm','border','border-blue-100');
            tabTipos.classList.remove('bg-white','text-blue-600','shadow-sm','border','border-blue-100');
            tabTipos.classList.add('text-gray-500');
        }
    }

    tabTipos.addEventListener('click', () => activarTab('tipos'));
    tabConceptos.addEventListener('click', () => activarTab('conceptos'));

    // Estado inicial: mostrar tipos de pago
    activarTab('tipos');
});
</script>

<style>
/* Animación suave para las filas de la tabla */
@keyframes fadeInRow {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

tbody tr {
    animation: fadeInRow 0.3s ease-out;
    animation-fill-mode: both;
}

tbody tr:nth-child(1) { animation-delay: 0.05s; }
tbody tr:nth-child(2) { animation-delay: 0.1s; }
tbody tr:nth-child(3) { animation-delay: 0.15s; }
tbody tr:nth-child(4) { animation-delay: 0.2s; }
tbody tr:nth-child(5) { animation-delay: 0.25s; }
tbody tr:nth-child(n+6) { animation-delay: 0.3s; }
</style>
