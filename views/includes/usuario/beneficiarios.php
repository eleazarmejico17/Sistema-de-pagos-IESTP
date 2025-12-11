<?php
require_once __DIR__ . '/../../../models/bienestar-beneficiariosModel.php';
$beneficiarioModel = new BeneficiarioModel();
$beneficiarios = $beneficiarioModel->listarBeneficiarios();
?>

<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
  <!-- Header -->
  <div class="bg-gradient-to-r from-blue-600 to-blue-800 text-white px-6 py-5 flex items-center justify-between rounded-t-2xl shadow-sm">
    <div class="flex items-center gap-3">
      <div class="p-2 bg-blue-500 rounded-lg">
        <i class="fas fa-user-check text-xl"></i>
      </div>
      <div>
        <h2 class="text-xl font-bold">BENEFICIARIOS</h2>
        <p class="text-blue-100 text-sm">Lista de beneficiarios registrados</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-3 py-1 bg-blue-500 bg-opacity-20 rounded-full text-sm font-medium">
        <?= count($beneficiarios) ?> registros
      </span>
    </div>
  </div>

  <!-- Filtro de búsqueda -->
  <div class="p-5 border-b border-gray-100">
    <div class="flex flex-col md:flex-row gap-3">
      <div class="relative flex-1">
        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
          <i class="fas fa-search text-gray-400"></i>
        </div>
        <input 
          type="text" 
          id="filtroBusqueda" 
          placeholder="Buscar por nombre, DNI o programa..." 
          class="pl-10 pr-4 py-2.5 w-full border border-gray-200 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-all duration-200"
        >
      </div>
      <button 
        id="btnLimpiarFiltro" 
        class="px-4 py-2.5 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 transition-colors flex items-center gap-2 border border-gray-200"
      >
        <i class="fas fa-sync-alt text-sm"></i> Limpiar
      </button>
    </div>
  </div>

  <!-- Tabla de beneficiarios -->
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>NOMBRE COMPLETO</span>
              <button class="text-gray-400 hover:text-gray-600 sortable" data-sort="nombre">
                <i class="fas fa-sort text-xs"></i>
              </button>
            </div>
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>SOLICITUD</span>
              <button class="text-gray-400 hover:text-gray-600 sortable" data-sort="programa">
                <i class="fas fa-sort text-xs"></i>
              </button>
            </div>
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>ESTADO</span>
              <button class="text-gray-400 hover:text-gray-600 sortable" data-sort="ciclo">
                <i class="fas fa-sort text-xs"></i>
              </button>
            </div>
          </th>
          <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
            ACCIONES
          </th>
        </tr>
      </thead>
      <tbody id="tablaBeneficiarios" class="bg-white divide-y divide-gray-100">
        <?php if (!empty($beneficiarios)): ?>
          <?php foreach ($beneficiarios as $beneficiario): 
            $nombreCompleto = trim($beneficiario['nombre'] ?? '');
            $dni = $beneficiario['dni_est'] ?? '';
            $programa = $beneficiario['tipo_solicitud'] ?? 'No especificado';
            $fecha = $beneficiario['fecha'] ?? $beneficiario['fecha_registro'] ?? 'Sin fecha';
            $estado = $beneficiario['estado'] ?? 'Pendiente';
            $descripcion = $beneficiario['descripcion'] ?? '';
            $archivos = $beneficiario['archivos'] ?? '';
            $telefono = $beneficiario['telefono'] ?? '';
            if ($estado !== 'Aprobado') { continue; }
          ?>
            <tr class="hover:bg-blue-50 transition-colors" 
                data-nombre="<?= strtolower($nombreCompleto) ?>" 
                data-dni="<?= htmlspecialchars($dni) ?>" 
                data-programa="<?= strtolower($programa) ?>"
                data-ciclo="<?= strtolower($estado) ?>"
                data-estado="<?= htmlspecialchars($estado) ?>"
                data-descripcion='<?= htmlspecialchars($descripcion, ENT_QUOTES, "UTF-8") ?>'
                data-archivos='<?= htmlspecialchars($archivos, ENT_QUOTES, "UTF-8") ?>'
                data-nombre-completo='<?= htmlspecialchars($nombreCompleto, ENT_QUOTES, "UTF-8") ?>'
                data-telefono='<?= htmlspecialchars($telefono, ENT_QUOTES, "UTF-8") ?>'
                data-tipo='<?= htmlspecialchars($programa, ENT_QUOTES, "UTF-8") ?>'
                data-fecha='<?= htmlspecialchars($fecha, ENT_QUOTES, "UTF-8") ?>'>
              <td class="px-6 py-4">
                <div class="flex items-center gap-3">
                  <div class="flex-shrink-0 h-10 w-10 rounded-full bg-blue-100 flex items-center justify-center">
                    <i class="fas fa-user text-blue-600"></i>
                  </div>
                  <div>
                    <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($nombreCompleto) ?></div>
                    <div class="text-xs text-gray-500"><?= htmlspecialchars($beneficiario['correo'] ?? '') ?></div>
                  </div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-900 font-medium"><?= htmlspecialchars($programa) ?></div>
                <div class="text-xs text-gray-500"><?= date('d/m/Y', strtotime($fecha)) ?></div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <span class="px-2.5 py-1 text-xs font-medium rounded-full <?= 
                  $estado === 'Aprobado' ? 'bg-green-100 text-green-800' : 
                  ($estado === 'Rechazado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')
                ?>">
                  <?= htmlspecialchars($estado) ?>
                </span>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <button onclick="verDetalle(this)"
                          class="p-2 text-gray-500 hover:bg-gray-50 rounded-lg transition-colors"
                          title="Ver detalles">
                    <i class="fas fa-eye"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="4" class="px-6 py-12 text-center">
              <div class="text-gray-400">
                <i class="fas fa-inbox text-4xl mb-2"></i>
                <p class="text-sm font-medium text-gray-500">No se encontraron beneficiarios</p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Paginación -->
  <div class="bg-white px-4 py-3 flex items-center justify-between border-t border-gray-200 sm:px-6">
    <div class="flex-1 flex justify-between sm:hidden">
      <a href="#" class="relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Anterior
      </a>
      <a href="#" class="ml-3 relative inline-flex items-center px-4 py-2 border border-gray-300 text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50">
        Siguiente
      </a>
    </div>
    <div class="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
      <div>
        <p class="text-sm text-gray-700">
          Mostrando
          <span class="font-medium">1</span>
          a
          <span class="font-medium">10</span>
          de
          <span class="font-medium">20</span>
          resultados
        </p>
      </div>
      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
            <span class="sr-only">Anterior</span>
            <i class="fas fa-chevron-left h-5 w-5"></i>
          </a>
          <!-- Current: "z-10 bg-blue-50 border-blue-500 text-blue-600", Default: "bg-white border-gray-300 text-gray-500 hover:bg-gray-50" -->
          <a href="#" aria-current="page" class="z-10 bg-blue-50 border-blue-500 text-blue-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
            1
          </a>
          <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
            2
          </a>
          <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
            3
          </a>
          <span class="relative inline-flex items-center px-4 py-2 border border-gray-300 bg-white text-sm font-medium text-gray-700">
            ...
          </span>
          <a href="#" class="bg-white border-gray-300 text-gray-500 hover:bg-gray-50 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
            8
          </a>
          <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
            <span class="sr-only">Siguiente</span>
            <i class="fas fa-chevron-right h-5 w-5"></i>
          </a>
        </nav>
      </div>
    </div>
  </div>
</div>

<!-- Modal para ver detalles -->
<div id="modalDetalle" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
  <div class="relative top-10 mx-auto p-0 border w-11/12 md:w-2/3 lg:w-1/2 shadow-2xl rounded-xl bg-white overflow-hidden">
    <div class="px-6 py-4 bg-gradient-to-r from-blue-600 to-blue-800 text-white">
      <div class="flex items-center justify-between">
        <h3 class="text-lg font-semibold">Detalles del Beneficiario</h3>
        <button onclick="cerrarModal()" class="text-white hover:text-gray-200">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>
    <div class="p-6">
      <div class="space-y-4" id="detalleContenido">
        <!-- Los detalles se cargarán aquí dinámicamente -->
      </div>
    </div>
    <div class="px-6 py-4 bg-gray-50 text-right border-t">
      <button onclick="cerrarModal()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
        Cerrar
      </button>
    </div>
  </div>
</div>

<script>
// Funciones JavaScript para la interacción
function seleccionarBeneficiario(dni) {
  // Implementar lógica para seleccionar beneficiario
  console.log('Seleccionado DNI:', dni);
  alert('Beneficiario con DNI ' + dni + ' seleccionado');
}

function verDetalle(button) {
  const fila = button.closest('tr');
  const dni = fila.dataset.dni || '';
  const estado = fila.dataset.estado || 'Pendiente';
  const descripcion = fila.dataset.descripcion || 'Sin descripción';
  const archivosStr = fila.dataset.archivos || '';
  const nombreCompleto = fila.dataset.nombreCompleto || '';
  const telefono = fila.dataset.telefono || '';
  const tipo = fila.dataset.tipo || '';
  const fecha = fila.dataset.fecha || '';

  const archivos = archivosStr
    .split(',')
    .map(a => a.trim())
    .filter(a => a.length > 0);

  document.getElementById('modalDetalle').classList.remove('hidden');
  document.body.style.overflow = 'hidden';

  let evidenciasHtml = '';
  if (archivos.length > 0) {
    evidenciasHtml = `
      <div class="pt-4 border-t">
        <p class="text-sm font-medium text-gray-500 mb-2">Evidencias</p>
        <div class="grid grid-cols-2 md:grid-cols-3 gap-4">
          ${archivos.map(archivo => `
            <a href="../uploads/solicitudes/${archivo}" target="_blank" class="block group">
              <div class="w-full h-28 bg-gray-100 rounded-lg overflow-hidden flex items-center justify-center border border-gray-200 group-hover:border-blue-400 transition-colors">
                <img src="../uploads/solicitudes/${archivo}" alt="Evidencia" class="max-h-full max-w-full object-contain" onerror="this.src='https://via.placeholder.com/150?text=Archivo';">
              </div>
              <p class="mt-1 text-xs text-gray-500 truncate">${archivo}</p>
            </a>
          `).join('')}
        </div>
      </div>
    `;
  }

  document.getElementById('detalleContenido').innerHTML = `
    <div class="grid grid-cols-2 gap-4">
      <div>
        <p class="text-sm font-medium text-gray-500">DNI del estudiante</p>
        <p class="mt-1 text-sm text-gray-900">${dni}</p>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500">Nombre completo</p>
        <p class="mt-1 text-sm text-gray-900">${nombreCompleto}</p>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500">Teléfono</p>
        <p class="mt-1 text-sm text-gray-900">${telefono}</p>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500">Tipo de solicitud</p>
        <p class="mt-1 text-sm text-gray-900">${tipo}</p>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500">Fecha de solicitud</p>
        <p class="mt-1 text-sm text-gray-900">${fecha}</p>
      </div>
      <div>
        <p class="text-sm font-medium text-gray-500">Estado</p>
        <p class="mt-1">
          <span class="px-2.5 py-1 text-xs font-medium rounded-full ${estado === 'Aprobado' ? 'bg-green-100 text-green-800' : (estado === 'Rechazado' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800')}">
            ${estado}
          </span>
        </p>
      </div>
    </div>
    <div class="pt-4 border-t">
      <p class="text-sm font-medium text-gray-500">Descripción de la solicitud</p>
      <p class="mt-1 text-sm text-gray-900 whitespace-pre-line">${descripcion}</p>
    </div>

    ${evidenciasHtml}
  `;
}

function cerrarModal() {
  document.getElementById('modalDetalle').classList.add('hidden');
  document.body.style.overflow = 'auto';
}

// Filtro de búsqueda
document.addEventListener('DOMContentLoaded', function() {
  const filtroBusqueda = document.getElementById('filtroBusqueda');
  const btnLimpiar = document.getElementById('btnLimpiarFiltro');
  const filas = document.querySelectorAll('#tablaBeneficiarios tr');

  function filtrarTabla() {
    const texto = filtroBusqueda.value.toLowerCase();
    filas.forEach(fila => {
      const textoFila = fila.textContent.toLowerCase();
      fila.style.display = textoFila.includes(texto) ? '' : 'none';
    });
  }

  if (filtroBusqueda) {
    filtroBusqueda.addEventListener('input', filtrarTabla);
  }

  if (btnLimpiar) {
    btnLimpiar.addEventListener('click', function() {
      filtroBusqueda.value = '';
      filas.forEach(fila => fila.style.display = '');
    });
  }

  // Ordenación de columnas
  document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', function() {
      const tipo = this.dataset.sort;
      const tbody = document.querySelector('#tablaBeneficiarios');
      const filas = Array.from(tbody.querySelectorAll('tr'));
      
      filas.sort((a, b) => {
        const aVal = a.querySelector(`td[data-${tipo}]`) ? 
                    a.querySelector(`td[data-${tipo}]`).dataset[tipo] : 
                    a.dataset[tipo] || '';
        const bVal = b.querySelector(`td[data-${tipo}]`) ? 
                    b.querySelector(`td[data-${tipo}]`).dataset[tipo] : 
                    b.dataset[tipo] || '';
        
        return aVal.localeCompare(bVal);
      });

      // Alternar entre orden ascendente y descendente
      if (this.classList.contains('asc')) {
        filas.reverse();
        this.classList.remove('asc');
        this.classList.add('desc');
        this.querySelector('i').className = 'fas fa-sort-down ml-1';
      } else {
        this.classList.remove('desc');
        this.classList.add('asc');
        this.querySelector('i').className = 'fas fa-sort-up ml-1';
      }

      // Limpiar clases de ordenación de otros encabezados
      document.querySelectorAll('.sortable').forEach(h => {
        if (h !== this) {
          h.classList.remove('asc', 'desc');
          h.querySelector('i').className = 'fas fa-sort ml-1';
        }
      });

      // Reinsertar filas ordenadas
      filas.forEach(fila => tbody.appendChild(fila));
    });
  });
});
</script>