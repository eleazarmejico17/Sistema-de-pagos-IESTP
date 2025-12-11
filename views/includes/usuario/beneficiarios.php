<?php
require_once __DIR__ . '/../../../models/bienestar-beneficiariosModel.php';
$beneficiarioModel = new BeneficiarioModel();
$conceptosPago = $beneficiarioModel->listarConceptosPago();

// Si no hay conceptos, mostrar datos de ejemplo
if (empty($conceptosPago)) {
    $conceptosPago = [
        ['id' => 1, 'nombre' => '1.1', 'descripcion' => 'Carnet de Medio Pasaje', 'uit' => 18.00, 'estado_uit' => 'Activo'],
        ['id' => 2, 'nombre' => '1.2', 'descripcion' => 'Duplicado de carnet', 'uit' => 18.00, 'estado_uit' => 'Activo'],
        ['id' => 3, 'nombre' => '3.1', 'descripcion' => 'Inscripción del postulante modalidad ordinario', 'uit' => 205.00, 'estado_uit' => 'Activo'],
        ['id' => 4, 'nombre' => '3.2', 'descripcion' => 'Inscripción del postulante modalidad exonerados', 'uit' => 205.00, 'estado_uit' => 'Activo'],
        ['id' => 5, 'nombre' => '5.1', 'descripcion' => 'Ratificación de matrícula', 'uit' => 172.00, 'estado_uit' => 'Activo'],
        ['id' => 6, 'nombre' => '5.2', 'descripcion' => 'Matrícula Ingresantes', 'uit' => 220.00, 'estado_uit' => 'Activo'],
        ['id' => 7, 'nombre' => '6.1', 'descripcion' => 'Trámite de matrícula extemporánea', 'uit' => 8.00, 'estado_uit' => 'Activo'],
        ['id' => 8, 'nombre' => '7.1', 'descripcion' => 'Convalidación interna por semestre', 'uit' => 61.00, 'estado_uit' => 'Activo']
    ];
}
?>

<div class="bg-white rounded-2xl shadow-xl overflow-hidden border border-gray-200">
  <!-- Header -->
  <div class="bg-gradient-to-r from-green-600 to-green-800 text-white px-6 py-5 flex items-center justify-between rounded-t-2xl shadow-sm">
    <div class="flex items-center gap-3">
      <div class="p-2 bg-green-500 rounded-lg">
        <i class="fas fa-credit-card text-xl"></i>
      </div>
      <div>
        <h2 class="text-xl font-bold">REALIZAR PAGO</h2>
        <p class="text-green-100 text-sm">Seleccione los conceptos que desea pagar</p>
      </div>
    </div>
    <div class="flex items-center gap-2">
      <span class="px-3 py-1 bg-green-500 bg-opacity-20 rounded-full text-sm font-medium">
        <?= count($conceptosPago) ?> conceptos
      </span>
      <span id="totalSeleccionado" class="px-3 py-1 bg-yellow-400 bg-opacity-30 rounded-full text-sm font-medium hidden">
        Total: S/. 0.00
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
          placeholder="Buscar por concepto o descripción..." 
          class="pl-10 pr-4 py-2.5 w-full border border-gray-200 rounded-xl focus:ring-2 focus:ring-green-500 focus:border-green-500 transition-all duration-200"
        >
      </div>
      <button 
        id="btnLimpiarFiltro" 
        class="px-4 py-2.5 bg-gray-50 text-gray-600 rounded-xl hover:bg-gray-100 transition-colors flex items-center gap-2 border border-gray-200"
      >
        <i class="fas fa-sync-alt text-sm"></i> Limpiar
      </button>
      <button 
        id="btnSeleccionarTodo" 
        class="px-4 py-2.5 bg-green-50 text-green-600 rounded-xl hover:bg-green-100 transition-colors flex items-center gap-2 border border-green-200"
      >
        <i class="fas fa-check-square text-sm"></i> Seleccionar todo
      </button>
    </div>
  </div>

  <!-- Tabla de conceptos de pago con selección -->
  <div class="overflow-x-auto">
    <table class="min-w-full divide-y divide-gray-200">
      <thead class="bg-gray-50">
        <tr>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <input type="checkbox" id="selectAll" class="rounded border-gray-300 text-green-600 focus:ring-green-500">
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>N°</span>
            </div>
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>CONCEPTO</span>
              <button class="text-gray-400 hover:text-gray-600 sortable" data-sort="concepto">
                <i class="fas fa-sort text-xs"></i>
              </button>
            </div>
          </th>
          <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
            <div class="flex items-center gap-1">
              <span>UIT</span>
              <button class="text-gray-400 hover:text-gray-600 sortable" data-sort="uit">
                <i class="fas fa-sort text-xs"></i>
              </button>
            </div>
          </th>
          <th class="px-6 py-3 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
            ACCIONES
          </th>
        </tr>
      </thead>
      <tbody id="tablaConceptos" class="bg-white divide-y divide-gray-100">
        <?php if (!empty($conceptosPago)): ?>
          <?php foreach ($conceptosPago as $index => $concepto): ?>
            <tr class="hover:bg-green-50 transition-colors concepto-item" 
                data-concepto="<?= strtolower($concepto['nombre'] . ' ' . $concepto['descripcion']) ?>" 
                data-uit="<?= $concepto['uit'] ?>"
                data-estado="<?= htmlspecialchars($concepto['estado_uit']) ?>"
                data-id="<?= $concepto['id'] ?>"
                style="animation-delay: <?= $index * 0.05 ?>s">
              <td class="px-6 py-4">
                <input type="checkbox" class="concepto-checkbox rounded border-gray-300 text-green-600 focus:ring-green-500" 
                       value="<?= $concepto['id'] ?>" data-uit="<?= $concepto['uit'] ?>" data-nombre="<?= htmlspecialchars($concepto['nombre']) ?>">
              </td>
              <td class="px-6 py-4 text-sm text-gray-900 font-medium">
                <?= $index + 1 ?>
              </td>
              <td class="px-6 py-4">
                <div>
                  <div class="text-sm font-medium text-gray-900"><?= htmlspecialchars($concepto['nombre']) ?></div>
                  <div class="text-xs text-gray-500"><?= htmlspecialchars($concepto['descripcion']) ?></div>
                </div>
              </td>
              <td class="px-6 py-4">
                <div class="flex items-center gap-2">
                  <span class="text-lg font-bold text-gray-900">
                    S/. <?= number_format($concepto['uit'], 2, '.', ',') ?>
                  </span>
                  <span class="px-2 py-1 text-xs font-medium rounded-full <?= 
                    $concepto['estado_uit'] === 'Activo' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'
                  ?>">
                    <?= htmlspecialchars($concepto['estado_uit']) ?>
                  </span>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                <div class="flex items-center justify-end gap-2">
                  <button onclick="verDetallesConcepto(<?= $concepto['id'] ?>)"
                          class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors"
                          title="Ver detalles">
                    <i class="fas fa-info-circle"></i>
                  </button>
                  <button onclick="agregarAlCarrito(<?= $concepto['id'] ?>)"
                          class="p-2 text-green-600 hover:bg-green-50 rounded-lg transition-colors"
                          title="Agregar al pago">
                    <i class="fas fa-shopping-cart"></i>
                  </button>
                </div>
              </td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr>
            <td colspan="5" class="px-6 py-12 text-center">
              <div class="text-gray-400">
                <i class="fas fa-credit-card text-4xl mb-2"></i>
                <p class="text-sm font-medium text-gray-500">No se encontraron conceptos de pago</p>
                <p class="text-xs text-gray-400 mt-1">Contacte al administrador para configurar los conceptos</p>
              </div>
            </td>
          </tr>
        <?php endif; ?>
      </tbody>
    </table>
  </div>

  <!-- Resumen y acciones de pago -->
  <div id="resumenPago" class="hidden border-t border-gray-200 bg-gray-50 p-6">
    <div class="flex flex-col md:flex-row items-center justify-between gap-4">
      <div class="flex-1">
        <h3 class="text-lg font-semibold text-gray-800 mb-2">Resumen del Pago</h3>
        <div class="space-y-1">
          <div class="flex justify-between text-sm">
            <span class="text-gray-600">Conceptos seleccionados:</span>
            <span id="cantidadConceptos" class="font-medium text-gray-900">0</span>
          </div>
          <div class="flex justify-between text-lg font-bold">
            <span class="text-gray-800">Total a pagar:</span>
            <span id="totalPagar" class="text-green-600">S/. 0.00</span>
          </div>
        </div>
      </div>
      <div class="flex gap-3">
        <button onclick="cancelarSeleccion()" 
                class="px-6 py-3 bg-gray-200 text-gray-700 rounded-xl hover:bg-gray-300 transition-colors font-medium">
          <i class="fas fa-times mr-2"></i> Cancelar
        </button>
        <button onclick="procesarPago()" 
                class="px-6 py-3 bg-green-600 text-white rounded-xl hover:bg-green-700 transition-colors font-medium shadow-lg">
          <i class="fas fa-credit-card mr-2"></i> Procesar Pago
        </button>
      </div>
    </div>
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
          <span class="font-medium"><?= count($conceptosPago) ?></span>
          resultados
        </p>
      </div>
      <div>
        <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
          <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-l-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
            <span class="sr-only">Anterior</span>
            <i class="fas fa-chevron-left h-5 w-5"></i>
          </a>
          <a href="#" aria-current="page" class="z-10 bg-green-50 border-green-500 text-green-600 relative inline-flex items-center px-4 py-2 border text-sm font-medium">
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
          <a href="#" class="relative inline-flex items-center px-2 py-2 rounded-r-md border border-gray-300 bg-white text-sm font-medium text-gray-500 hover:bg-gray-50">
            <span class="sr-only">Siguiente</span>
            <i class="fas fa-chevron-right h-5 w-5"></i>
          </a>
        </nav>
      </div>
    </div>
  </div>
</div>

<script>
let conceptosSeleccionados = [];

// Funciones JavaScript para la interacción
function verDetallesConcepto(id) {
  const fila = document.querySelector(`tr[data-id="${id}"]`);
  const nombre = fila.querySelector('td:nth-child(3) .text-sm').textContent;
  const descripcion = fila.querySelector('td:nth-child(3) .text-xs').textContent;
  const uit = fila.querySelector('td:nth-child(4) .text-lg').textContent;
  
  alert(`Concepto: ${nombre}\nDescripción: ${descripcion}\nMonto: ${uit}`);
}

function agregarAlCarrito(id) {
  const checkbox = document.querySelector(`input[value="${id}"]`);
  checkbox.checked = true;
  actualizarSeleccion();
  
  // Animación de feedback
  const button = event.target.closest('button');
  button.innerHTML = '<i class="fas fa-check text-green-600"></i>';
  setTimeout(() => {
    button.innerHTML = '<i class="fas fa-shopping-cart"></i>';
  }, 1000);
}

function actualizarSeleccion() {
  const checkboxes = document.querySelectorAll('.concepto-checkbox:checked');
  conceptosSeleccionados = [];
  let total = 0;
  
  checkboxes.forEach(checkbox => {
    const id = checkbox.value;
    const uit = parseFloat(checkbox.dataset.uit);
    const nombre = checkbox.dataset.nombre;
    
    conceptosSeleccionados.push({ id, uit, nombre });
    total += uit;
  });
  
  // Actualizar UI
  const totalElement = document.getElementById('totalSeleccionado');
  const resumenPago = document.getElementById('resumenPago');
  const totalPagar = document.getElementById('totalPagar');
  const cantidadConceptos = document.getElementById('cantidadConceptos');
  
  if (conceptosSeleccionados.length > 0) {
    totalElement.classList.remove('hidden');
    totalElement.textContent = `Total: S/. ${total.toFixed(2)}`;
    resumenPago.classList.remove('hidden');
    totalPagar.textContent = `S/. ${total.toFixed(2)}`;
    cantidadConceptos.textContent = conceptosSeleccionados.length;
  } else {
    totalElement.classList.add('hidden');
    resumenPago.classList.add('hidden');
  }
  
  // Actualizar checkbox principal
  const selectAll = document.getElementById('selectAll');
  const allCheckboxes = document.querySelectorAll('.concepto-checkbox');
  selectAll.checked = checkboxes.length === allCheckboxes.length && allCheckboxes.length > 0;
}

function cancelarSeleccion() {
  document.querySelectorAll('.concepto-checkbox').forEach(cb => cb.checked = false);
  conceptosSeleccionados = [];
  actualizarSeleccion();
}

function procesarPago() {
  if (conceptosSeleccionados.length === 0) {
    alert('Por favor seleccione al menos un concepto para pagar');
    return;
  }
  
  const total = conceptosSeleccionados.reduce((sum, c) => sum + c.uit, 0);
  const conceptosNombres = conceptosSeleccionados.map(c => c.nombre).join(', ');
  
  if (confirm(`¿Confirmar pago de los siguientes conceptos?\n\n${conceptosNombres}\n\nTotal: S/. ${total.toFixed(2)}`)) {
    // Aquí iría la lógica para procesar el pago
    alert('Procesando pago... (Función por implementar)');
    console.log('Conceptos a pagar:', conceptosSeleccionados);
  }
}

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
  const filtroBusqueda = document.getElementById('filtroBusqueda');
  const btnLimpiar = document.getElementById('btnLimpiarFiltro');
  const btnSeleccionarTodo = document.getElementById('btnSeleccionarTodo');
  const selectAll = document.getElementById('selectAll');
  const filas = document.querySelectorAll('#tablaConceptos tr.concepto-item');

  // Filtro de búsqueda
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

  // Seleccionar todo
  if (btnSeleccionarTodo) {
    btnSeleccionarTodo.addEventListener('click', function() {
      const allCheckboxes = document.querySelectorAll('.concepto-checkbox');
      const anyUnchecked = Array.from(allCheckboxes).some(cb => !cb.checked);
      
      allCheckboxes.forEach(cb => cb.checked = anyUnchecked);
      actualizarSeleccion();
    });
  }

  // Checkbox principal
  if (selectAll) {
    selectAll.addEventListener('change', function() {
      const allCheckboxes = document.querySelectorAll('.concepto-checkbox');
      allCheckboxes.forEach(cb => cb.checked = this.checked);
      actualizarSeleccion();
    });
  }

  // Checkboxes individuales
  document.querySelectorAll('.concepto-checkbox').forEach(checkbox => {
    checkbox.addEventListener('change', actualizarSeleccion);
  });

  // Ordenación de columnas
  document.querySelectorAll('.sortable').forEach(header => {
    header.addEventListener('click', function() {
      const tipo = this.dataset.sort;
      const tbody = document.querySelector('#tablaConceptos');
      const filas = Array.from(tbody.querySelectorAll('tr.concepto-item'));
      
      filas.sort((a, b) => {
        let aVal, bVal;
        
        if (tipo === 'concepto') {
          aVal = a.querySelector('td:nth-child(3)').textContent.toLowerCase();
          bVal = b.querySelector('td:nth-child(3)').textContent.toLowerCase();
        } else if (tipo === 'uit') {
          aVal = parseFloat(a.dataset.uit) || 0;
          bVal = parseFloat(b.dataset.uit) || 0;
        }
        
        if (tipo === 'uit') {
          return aVal - bVal;
        } else {
          return aVal.localeCompare(bVal);
        }
      });

      // Alternar entre orden ascendente y descendente
      if (this.classList.contains('asc')) {
        filas.reverse();
        this.classList.remove('asc');
        this.classList.add('desc');
        this.querySelector('i').className = 'fas fa-sort-down text-xs';
      } else {
        this.classList.remove('desc');
        this.classList.add('asc');
        this.querySelector('i').className = 'fas fa-sort-up text-xs';
      }

      // Limpiar clases de ordenación de otros encabezados
      document.querySelectorAll('.sortable').forEach(h => {
        if (h !== this) {
          h.classList.remove('asc', 'desc');
          h.querySelector('i').className = 'fas fa-sort text-xs';
        }
      });

      // Reinsertar filas ordenadas
      filas.forEach(fila => tbody.appendChild(fila));
    });
  });
});
</script>