<?php
header("Content-Type: application/json");
require_once __DIR__ . "/../config/conexion.php";

try {
    $data = json_decode(file_get_contents("php://input"), true) ?? $_POST;

    if (!isset($data['dni'])) {
        throw new Exception("DNI no proporcionado");
    }

    $dni = trim($data['dni']);

    if (!preg_match('/^\d{8}$/', $dni)) {
        throw new Exception("DNI inválido");
    }

    $db = Conexion::getInstance()->getConnection();

    $sql = "SELECT nom_est, ap_est, am_est, cel_est 
            FROM estudiante 
            WHERE dni_est = :dni 
            AND estado = 1 
            LIMIT 1";
            
    $stmt = $db->prepare($sql);
    $stmt->execute([':dni' => $dni]);
    $estudiante = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$estudiante) {
        echo json_encode([
            "success" => false,
            "message" => "Estudiante no encontrado"
        ]);
        exit;
    }

    echo json_encode([
        "success" => true,
        "data" => $estudiante
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
