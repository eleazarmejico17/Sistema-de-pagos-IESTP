






<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Registrar Tipo de Pago</title>
</head>

<body class="min-h-screen bg-gray-100 flex items-center justify-center p-6">

    <div class="w-full max-w-lg bg-white shadow-lg rounded-2xl p-8">
        
        <h2 class="text-2xl font-bold text-gray-800 mb-4">
            Registrar Tipo de Pago
        </h2>

        <p class="text-gray-600 text-sm mb-6">
            Complete la información para agregar un nuevo tipo de pago al sistema.
        </p>

        <form action="?pagina=admin-tipo-pago" method="POST" class="space-y-5">

            <div>
                <label class="block text-gray-700 font-medium mb-1">Nombre *</label>
                <input 
                    type="text" 
                    name="nombre"
                    required
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                    placeholder="Ej: Depósito, Transferencia"
                >
            </div>

            <div>
                <label class="block text-gray-700 font-medium mb-1">Descripción</label>
                <textarea 
                    name="descripcion" 
                    rows="3"
                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-emerald-400 focus:outline-none"
                    placeholder="Descripción opcional del tipo de pago"
                ></textarea>
            </div>

            <div class="pt-3">
                <button 
                    type="submit"
                    class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-semibold py-2 rounded-lg transition shadow-md">
                    Guardar
                </button>
            </div>

        </form>
    </div>

</body>
</html>
