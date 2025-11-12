<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../models/model_login.php';

// Permitir solicitudes desde el mismo origen (ajustar CORS si es necesario)
// header('Access-Control-Allow-Origin: *');
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Health check: probar conexión a la BD
    try {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->query('SELECT 1');
        echo json_encode(['success' => true, 'message' => 'Conexión a la base de datos OK']);
        exit;
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'No se pudo conectar a la base de datos']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = json_decode(file_get_contents('php://input'), true);
    $usuario = $input['usuario'] ?? '';
    $contrasena = $input['contrasena'] ?? '';

    if (empty($usuario) || empty($contrasena)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Usuario y contraseña son requeridos']);
        exit;
    }

    try {
        $model = new ModelLogin();
        $res = $model->login($usuario, $contrasena);
        if ($res['success']) {
            echo json_encode(['success' => true, 'message' => $res['message'], 'redirect' => $res['redirect']]);
            exit;
        } else {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => $res['message']]);
            exit;
        }
    } catch (Exception $e) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Error del servidor']);
        exit;
    }
}

// Método no permitido
http_response_code(405);
echo json_encode(['success' => false, 'message' => 'Método no permitido']);
