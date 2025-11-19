<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Administrativo</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <style>
    @keyframes fadeInUp {
      from { opacity: 0; transform: translateY(30px); }
      to { opacity: 1; transform: translateY(0); }
    }

    @keyframes slideInLeft {
      from { opacity: 0; transform: translateX(-30px); }
      to { opacity: 1; transform: translateX(0); }
    }

    @keyframes scaleIn {
      from { opacity: 0; transform: scale(0.9); }
      to { opacity: 1; transform: scale(1); }
    }

    @keyframes shimmer {
      0% { background-position: -1000px 0; }
      100% { background-position: 1000px 0; }
    }

    @keyframes bounce {
      0%, 100% { transform: translateY(0); }
      50% { transform: translateY(-10px); }
    }

    .card-animate {
      animation: fadeInUp 0.6s ease forwards;
    }

    .slide-in {
      animation: slideInLeft 0.5s ease forwards;
    }

    .scale-in {
      animation: scaleIn 0.4s ease forwards;
    }

    .hover-lift {
      transition: all 0.3s ease;
    }

    .hover-lift:hover {
      transform: translateY(-8px) scale(1.02);
      box-shadow: 0 20px 40px rgba(0, 0, 0, 0.15);
    }

    .shimmer {
      background: linear-gradient(90deg, transparent, rgba(255,255,255,0.8), transparent);
      background-size: 1000px 100%;
      animation: shimmer 2s infinite;
    }

    .icon-bounce:hover {
      animation: bounce 0.6s ease;
    }

    .glass-effect {
      backdrop-filter: blur(10px);
      background: rgba(255, 255, 255, 0.95);
    }

    .gradient-text {
      background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
      -webkit-background-clip: text;
      -webkit-text-fill-color: transparent;
      background-clip: text;
    }

    .table-row {
      transition: all 0.3s ease;
    }

    .table-row:hover {
      background: linear-gradient(90deg, rgba(59, 130, 246, 0.05), rgba(147, 51, 234, 0.05));
      transform: scale(1.01);
    }

    .btn-action {
      transition: all 0.3s ease;
      position: relative;
      overflow: hidden;
    }

    .btn-action::before {
      content: '';
      position: absolute;
      top: 50%;
      left: 50%;
      width: 0;
      height: 0;
      border-radius: 50%;
      background: rgba(255, 255, 255, 0.5);
      transform: translate(-50%, -50%);
      transition: width 0.6s, height 0.6s;
    }

    .btn-action:hover::before {
      width: 300px;
      height: 300px;
    }
  </style>
</head>

<body class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50 font-sans">


  <!-- Main Content -->
  <main>
    <!-- Header -->
    <header class="flex justify-between items-center mb-8 slide-in">

    </header>

    <!-- Content Sections -->
    <div id="inicio-section" class="section-content">
      <!-- Module Cards -->
      <section class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Bienestar Card -->
        <button type="button"
          class="card-animate hover-lift bg-gradient-to-br from-lime-400 to-emerald-500 rounded-3xl p-8 text-white shadow-xl cursor-pointer relative overflow-hidden group w-full text-left">
          
          <!-- Efecto shimmer -->
          <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

          <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
              <div>
                <i class="fas fa-heart text-5xl mb-3 icon-bounce"></i>
                <h3 class="text-2xl font-bold">BIENESTAR</h3>
              </div>
            </div>

            <p class="text-lime-100 mb-4">Gestión de bienestar estudiantil</p>

            <div class="flex items-center gap-2 text-sm">
              <i class="fas fa-users"></i>
              <span>2 usuarios registrados</span>
            </div>
          </div>

        </button>


        <button type="button"
          class="card-animate hover-lift bg-gradient-to-br from-sky-400 to-blue-600 rounded-3xl p-8 text-white shadow-xl cursor-pointer relative overflow-hidden group delay-100 w-full text-left">

          <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

          <div class="relative z-10">
            <div class="flex justify-between items-start mb-4">
              <div>
                <i class="fas fa-building text-5xl mb-3 icon-bounce"></i>
                <h3 class="text-2xl font-bold">DIRECCIÓN</h3>
              </div>
            </div>

            <p class="text-sky-100 mb-4">Gestión de dirección académica</p>

            <div class="flex items-center gap-2 text-sm">
              <i class="fas fa-users"></i>
              <span>2 usuarios registrados</span>
            </div>
          </div>

        </button>


        <form method="get">
          <button type="submit" name="modulo" value="admin-usuarios"
            class="card-animate hover-lift bg-gradient-to-br from-blue-700 to-indigo-900 rounded-3xl p-8 text-white shadow-xl cursor-pointer relative overflow-hidden group delay-200 w-full text-left">

            <div class="absolute inset-0 shimmer opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>

            <div class="relative z-10">
              <div class="flex justify-between items-start mb-4">
                <div>
                  <i class="fas fa-users-cog text-5xl mb-3 icon-bounce"></i>
                  <h3 class="text-2xl font-bold">USUARIOS</h3>
                </div>
              </div>

              <p class="text-blue-200 mb-4">Gestión general de usuarios</p>

              <div class="flex items-center gap-2 text-sm">
                <i class="fas fa-users"></i>
                <span>2 usuarios registrados</span>
              </div>
            </div>

          </button>
        </form>



      </section>

      <!-- Welcome Message -->
    <div id="contenido-modulo" class="glass-effect rounded-3xl p-12 text-center shadow-xl scale-in">

      <?php
      $modulo = $_GET['modulo'] ?? null;

      // Archivos válidos
      $archivos_validos = ['admin-usuarios', 'admin-direccion', 'admin-bienestar'];

      if ($modulo && in_array($modulo, $archivos_validos)) {
          // Si hay módulo seleccionado, se carga y se oculta el mensaje
          include __DIR__ . "/$modulo.php";
      } else {
          // Mensaje de bienvenida solo si no hay módulo seleccionado
          echo '
              <i class="fas fa-rocket text-6xl text-blue-600 mb-4"></i>
              <h2 class="text-3xl font-bold text-gray-800 mb-4">Bienvenido al Sistema</h2>
              <p class="text-gray-600 text-lg">Selecciona un módulo para comenzar a gestionar los usuarios</p>
          ';
      }
      ?>

    </div>

    </div>


  </main>
</body>
</html>














    <!-- Add/Edit Modal -->
    <div id="modal" class="fixed inset-0 bg-black bg-opacity-50 hidden items-center justify-center z-50">
      <div class="bg-white rounded-3xl p-8 max-w-md w-full mx-4 shadow-2xl scale-in">
        <div class="flex items-center justify-between mb-6">
          <h3 class="text-2xl font-bold gradient-text" id="modal-title">Agregar Usuario</h3>
          <button onclick="closeModal()" class="p-2 hover:bg-gray-100 rounded-full transition-all duration-300">
            <i class="fas fa-times text-2xl text-gray-700"></i>
          </button>
        </div>

        <form id="user-form" class="space-y-4">
          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-user mr-2"></i>Usuario
            </label>
            <input type="text" id="input-usuario" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-lock mr-2"></i>Password
            </label>
            <input type="password" id="input-password" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-envelope mr-2"></i>Correo
            </label>
            <input type="email" id="input-correo" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
          </div>

          <div>
            <label class="block text-sm font-semibold text-gray-700 mb-2">
              <i class="fas fa-tag mr-2"></i>Rol
            </label>
            <input type="text" id="input-rol" required class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all duration-300">
          </div>

          <div class="flex gap-3 pt-4">
            <button type="button" onclick="closeModal()" class="flex-1 px-6 py-3 border-2 border-gray-300 text-gray-700 rounded-xl hover:bg-gray-50 transition-all duration-300 font-semibold">
              Cancelar
            </button>
            <button type="submit" class="flex-1 px-6 py-3 bg-gradient-to-r from-blue-500 to-purple-600 text-white rounded-xl hover:from-blue-600 hover:to-purple-700 transition-all duration-300 font-semibold shadow-lg">
              Guardar
            </button>
          </div>
        </form>
      </div>
    </div>