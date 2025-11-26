<?php
header('Content-Type: application/json');

try {

    require_once '../config/conexion.php';

    $data = json_decode(file_get_contents("php://input"), true);

    if (!$data) throw new Exception("JSON inválido");

    $id     = $data['id'] ?? null;
    $estado = $data['estado'] ?? null;
    $motivo = $data['motivo'] ?? '';

    if (!$id || !$estado) {
        throw new Exception("ID y estado son obligatorios");
    }

    $db = Database::getInstance();
    $conn = $db->getConnection();

    // VALIDAMOS ID
    $check = $conn->prepare("SELECT id FROM solicitud WHERE id = :id");
    $check->execute([':id' => $id]);

    if ($check->rowCount() === 0) {
        throw new Exception("Solicitud no encontrada");
    }

    // ACTUALIZAMOS
    $sql = "UPDATE solicitud 
            SET 
              estado = :estado,
              motivo_respuesta = :motivo,
              fecha_respuesta = NOW(),
              notificacion_enviada = 1
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute([
        ":estado" => $estado,
        ":motivo" => $motivo,
        ":id" => $id
    ]);

    echo json_encode([
        "success" => true,
        "message" => "Solicitud actualizada correctamente"
    ]);

} catch (Exception $e) {

    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
