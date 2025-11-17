<?php
// Permitir solicitudes de cualquier origen (CORS) - Ajusta en producción
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST, OPTIONS");
header("Access-Control-Max-Age: 3600");
header("Access-Control-Allow-Headers: Content-Type, Access-Control-Allow-Headers, Authorization, X-Requested-With");

// Manejar la solicitud 'preflight' de CORS (método OPTIONS)
if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Asegurarse que es un método POST
if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    http_response_code(405); // Method Not Allowed
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

// 1. Incluir el modelo y la conexión
require_once __DIR__ . '/../../models/ModelLogin.php';
require_once __DIR__ . '/../../config/conexion.php'; // Asegúrate que esta ruta es correcta

// 2. Leer el JSON enviado por el cliente
$data = json_decode(file_get_contents("php://input"));

// 3. Validar que los datos llegaron
if (empty($data->usuario) || empty($data->contrasena)) {
    http_response_code(400); // Bad Request
    echo json_encode([
        "success" => false,
        "message" => "Datos de 'usuario' y 'contrasena' son requeridos",
        "data" => null,
        "token" => null
    ]);
    exit;
}

// 4. Usar el Modelo
$model = new ModelLogin();
$resultado = $model->login($data->usuario, $data->contrasena);

// 5. Devolver la respuesta (éxito o error)
if ($resultado['success']) {
    // 200 OK - Login exitoso
    http_response_code(200);
} else {
    // 401 Unauthorized - Credenciales incorrectas o formato inválido
    // (El mensaje de error ya viene del modelo)
    http_response_code(401);
}

echo json_encode($resultado);

?>