<?php
// api/verificar_token.php

// Esta función obtiene el token del "Header" de la solicitud
function getBearerToken() {
    $headers = getallheaders();
    if (isset($headers['Authorization'])) {
        if (preg_match('/Bearer\s(\S+)/', $headers['Authorization'], $matches)) {
            return $matches[1];
        }
    }
    return null;
}

$token = getBearerToken();
if ($token === null) {
    http_response_code(401);
    echo json_encode(["error" => "Token no proporcionado"]);
    exit;
}

// Verificar el token en la BD
include_once __DIR__ . '/../config/database.php'; // Ajusta la ruta si es necesario
$database = new Database();
$db = $database->getConnection();

$query = "SELECT id, tipo, estuempleado FROM usuarios WHERE token = :token LIMIT 1";
$stmt = $db->prepare($query);
$stmt->bindParam(':token', $token);
$stmt->execute();

$usuario_valido = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$usuario_valido) {
    http_response_code(401);
    echo json_encode(["error" => "Token inválido o expirado"]);
    exit;
}

// Si el token es válido, guardamos los datos del usuario para usarlos en el endpoint
// $usuario_id = $usuario_valido['id'];
// $usuario_tipo = $usuario_valido['tipo'];
// $id_est_emp = $usuario_valido['estuempleado'];
?>