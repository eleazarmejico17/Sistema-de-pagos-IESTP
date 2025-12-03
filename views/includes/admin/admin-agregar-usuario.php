<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Registrar Usuario</title>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-white shadow-lg rounded-2xl p-8">
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            Registrar Usuario
        </h2>

        <p class="text-gray-600 text-sm mb-6">
            Complete la información para agregar un nuevo usuario al sistema.
        </p>

        <form action="" method="POST" class="space-y-5">

            <!-- Usuario -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Usuario *</label>
                <input 
                    type="text" 
                    name="usuario"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    placeholder="Nuevo usuario"
                >
            </div>

            <!-- Password -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Contraseña *</label>
                <input 
                    type="password" 
                    name="password"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    placeholder="Contraseña segura"
                >
            </div>

            <!-- Tipo -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Tipo de Usuario *</label>
                <select 
                    name="tipo"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg bg-white focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                >
                    <option value="">Seleccione...</option>
                    <option value="1">Empleado</option>
                    <option value="2">Estudiante</option>
                    <option value="3">Empresa</option>
                </select>
            </div>

            <!-- estuempleado -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">ID Estudiante/Empleado</label>
                <input 
                    type="number" 
                    name="estuempleado"
                    min="1"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    placeholder="ID asociado"
                >
            </div>

            <!-- Token -->
            <div>
                <label class="block text-gray-700 font-medium mb-1">Token (Opcional)</label>
                <textarea 
                    name="token" 
                    rows="2"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-indigo-400 focus:outline-none"
                    placeholder="Token asignado al usuario"
                ></textarea>
            </div>

            <!-- Botón -->
            <div class="pt-3">
                <button 
                    type="submit"
                    class="w-full bg-indigo-500 hover:bg-indigo-600 text-white font-semibold py-2 rounded-lg transition shadow-md"
                >
                    Guardar Usuario
                </button>
            </div>

        </form>
    </div>

</body>
</html>
