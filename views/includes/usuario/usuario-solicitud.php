
<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Nuevo Registro de Solicitud</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"/>
</head>
<body class="min-h-screen bg-gray-100 font-sans">

  <main class="w-full max-w-5xl mx-auto p-6">
    <div class="bg-white rounded-2xl shadow-xl border border-gray-200 overflow-hidden">

      <!-- Header -->
      <div class="bg-gradient-to-r from-blue-700 to-blue-500 text-white px-6 py-4 flex items-center gap-3 rounded-t-2xl shadow-md">
        <i class="fas fa-file-alt text-2xl"></i>
        <h2 class="text-xl font-semibold tracking-wide">Nuevo Registro de Solicitud</h2>
      </div>

      <!-- FORMULARIO -->
      <form id="formSolicitud" enctype="multipart/form-data">
        
        <!-- Contenido -->
        <div class="p-8 space-y-6">

          <!-- Mensaje de éxito (se mostrará arriba de todo) -->
          <div id="mensajeExito" class="hidden p-4 bg-green-100 border border-green-400 text-green-700 rounded-lg mb-6">
            <div class="flex items-center">
              <i class="fas fa-check-circle text-green-500 text-xl mr-3"></i>
              <div>
                <h3 class="font-semibold">¡Solicitud Enviada!</h3>
                <p id="textoExito" class="text-sm mt-1">Tu solicitud ha sido registrada exitosamente.</p>
              </div>
              <button type="button" onclick="ocultarMensaje()" class="ml-auto text-green-500 hover:text-green-700">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Mensaje de error -->
          <div id="mensajeError" class="hidden p-4 bg-red-100 border border-red-400 text-red-700 rounded-lg mb-6">
            <div class="flex items-center">
              <i class="fas fa-exclamation-triangle text-red-500 text-xl mr-3"></i>
              <div>
                <h3 class="font-semibold">Error</h3>
                <p id="textoError" class="text-sm mt-1"></p>
              </div>
              <button type="button" onclick="ocultarMensaje()" class="ml-auto text-red-500 hover:text-red-700">
                <i class="fas fa-times"></i>
              </button>
            </div>
          </div>

          <!-- Datos del usuario -->
          <div class="grid grid-cols-1 md:grid-cols-2 gap-6">

            <div class="flex flex-col gap-2">
              <label class="font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-user text-blue-500"></i> Nombre completo
              </label>
              <input 
                type="text" 
                name="nombre"
                id="nombre"
                placeholder="Nombre del estudiante" 
                class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-blue-400 focus:outline-none transition"
                required>
            </div>

            <div class="flex flex-col gap-2">
              <label class="font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-phone text-green-500"></i> Teléfono
              </label>
              <input 
                type="tel"
                name="telefono"
                id="telefono"
                placeholder="999-999-999" 
                class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-green-400 focus:outline-none transition"
                required>
            </div>

            <div class="flex flex-col gap-2">
              <label class="font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-list text-yellow-500"></i> Tipo de solicitud
              </label>
              <select 
                name="tipo"
                id="tipo"
                class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-yellow-400 focus:outline-none transition"
                required>
                <option value="">Seleccionar tipo</option>
                <option value="Deportista">Deportista</option>
                <option value="Académica">Académica</option>
                <option value="Bienestar general">Bienestar general</option>
                <option value="Otro">Otro</option>
              </select>
            </div>

            <div class="flex flex-col gap-2">
              <label class="font-medium text-gray-700 flex items-center gap-2">
                <i class="fas fa-calendar-alt text-red-500"></i> Fecha de solicitud
              </label>
              <input 
                type="date"
                name="fecha"
                id="fecha"
                class="border border-gray-300 rounded-lg px-4 py-2 w-full focus:ring-2 focus:ring-red-400 focus:outline-none transition"
                required>
            </div>

          </div>

          <!-- Redactar solicitud -->
          <div class="flex flex-col gap-2">
            <label class="font-medium text-gray-700 flex items-center gap-2">
              <i class="fas fa-comment-alt text-blue-500"></i> Descripción de la solicitud
            </label>
            <textarea 
              name="descripcion"
              id="descripcion"
              rows="4" 
              placeholder="Describe tu solicitud..." 
              class="w-full border border-gray-300 rounded-xl p-4 focus:ring-2 focus:ring-blue-400 focus:outline-none transition resize-none"
              required></textarea>
          </div>

          <!-- Archivos -->
          <div class="flex flex-col gap-2">
            <label class="font-medium text-gray-700 flex items-center gap-2">
              <i class="fas fa-paperclip text-blue-500"></i> Evidencias
            </label>
            <p class="text-gray-500 text-sm mb-2">Agrega imágenes o documentos (máximo 5 archivos, 5MB cada uno)</p>
            <input 
              type="file" 
              name="archivo[]" 
              id="archivo"
              multiple 
              accept=".jpg,.jpeg,.png,.pdf,.doc,.docx"
              class="border border-dashed border-gray-300 rounded-xl p-4 cursor-pointer hover:border-blue-400 transition file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              onchange="validarArchivos(this)">
            <p id="errorArchivos" class="text-red-500 text-sm hidden"></p>
          </div>

          <!-- Botón enviar -->
          <div class="flex justify-start pt-4">
            <button 
              type="submit"
              id="btnEnviar"
              class="bg-blue-600 hover:bg-blue-700 text-white font-semibold px-8 py-3 rounded-xl shadow-md transition transform hover:-translate-y-1 hover:shadow-xl flex items-center gap-2">
              <i class="fas fa-paper-plane"></i> Enviar Solicitud
            </button>
            
            <button 
              type="button"
              id="btnLoading"
              class="hidden bg-blue-400 text-white font-semibold px-8 py-3 rounded-xl shadow-md flex items-center gap-2 cursor-not-allowed">
              <i class="fas fa-spinner fa-spin"></i> Enviando...
            </button>
          </div>

        </div>

      </form>
    </div>
  </main>

  <script>
    // Función para ocultar mensajes
    function ocultarMensaje() {
      document.getElementById('mensajeExito').classList.add('hidden');
      document.getElementById('mensajeError').classList.add('hidden');
    }

    // Validar archivos antes de subir
    function validarArchivos(input) {
      const errorElement = document.getElementById('errorArchivos');
      const archivos = input.files;
      let errores = [];

      // Validar cantidad de archivos
      if (archivos.length > 5) {
        errores.push('Máximo 5 archivos permitidos');
        input.value = '';
      }

      // Validar tamaño y tipo de cada archivo
      for (let i = 0; i < archivos.length; i++) {
        const archivo = archivos[i];
        
        // Validar tamaño (5MB)
        if (archivo.size > 5 * 1024 * 1024) {
          errores.push(`El archivo "${archivo.name}" excede el tamaño máximo de 5MB`);
        }
        
        // Validar tipo
        const tiposPermitidos = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!tiposPermitidos.includes(archivo.type)) {
          errores.push(`El archivo "${archivo.name}" no es un tipo válido`);
        }
      }

      if (errores.length > 0) {
        errorElement.textContent = errores.join(', ');
        errorElement.classList.remove('hidden');
        input.value = '';
      } else {
        errorElement.classList.add('hidden');
      }
    }

    // Manejar envío del formulario
    document.getElementById('formSolicitud').addEventListener('submit', function(e) {
      e.preventDefault();
      
      const btnEnviar = document.getElementById('btnEnviar');
      const btnLoading = document.getElementById('btnLoading');
      const mensajeExito = document.getElementById('mensajeExito');
      const mensajeError = document.getElementById('mensajeError');
      
      // Ocultar mensajes anteriores
      ocultarMensaje();
      
      // Mostrar loading
      btnEnviar.classList.add('hidden');
      btnLoading.classList.remove('hidden');
      
      // Crear FormData
      const formData = new FormData(this);
      
      // Enviar via AJAX
      fetch('../controller/guardarSolicitudController.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {
        if (data.success) {
          // Mostrar mensaje de éxito
          mensajeExito.classList.remove('hidden');
          document.getElementById('textoExito').textContent = data.message;
          
          // Limpiar formulario completamente
          document.getElementById('formSolicitud').reset();
          
          // Restablecer fecha actual
          const fechaInput = document.getElementById('fecha');
          const hoy = new Date().toISOString().split('T')[0];
          fechaInput.value = hoy;
          
          // Ocultar mensaje después de 5 segundos
          setTimeout(() => {
            mensajeExito.classList.add('hidden');
          }, 5000);
          
        } else {
          // Mostrar mensaje de error
          mensajeError.classList.remove('hidden');
          document.getElementById('textoError').textContent = data.error;
        }
      })
      .catch(error => {
        // Mostrar mensaje de error de conexión
        mensajeError.classList.remove('hidden');
        document.getElementById('textoError').textContent = 'Error de conexión: ' + error.message;
      })
      .finally(() => {
        // Ocultar loading
        btnEnviar.classList.remove('hidden');
        btnLoading.classList.add('hidden');
      });
    });

    // Establecer fecha actual por defecto al cargar la página
    document.addEventListener('DOMContentLoaded', function() {
      const fechaInput = document.getElementById('fecha');
      const hoy = new Date().toISOString().split('T')[0];
      fechaInput.value = hoy;
      
      // Enfocar en el primer campo
      document.getElementById('nombre').focus();
    });

    // Función para limpiar formulario manualmente (si la necesitas)
    function limpiarFormulario() {
      document.getElementById('formSolicitud').reset();
      
      // Restablecer fecha actual
      const fechaInput = document.getElementById('fecha');
      const hoy = new Date().toISOString().split('T')[0];
      fechaInput.value = hoy;
      
      // Ocultar mensajes
      ocultarMensaje();
      
      // Enfocar en el primer campo
      document.getElementById('nombre').focus();
    }
  </script>

</body>
</html>
