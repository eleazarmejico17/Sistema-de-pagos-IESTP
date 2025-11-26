<?php
// controller/actualizarEstadoSolicitud.php

// SOLO si no se ha enviado output
if (!headers_sent()) {
    header('Content-Type: application/json');
}

try {
    // Incluir la configuración de la base de datos
    require_once '../config/conexion.php';
    
    // Obtener los datos del POST
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if (!$data) {
        throw new Exception("No se recibieron datos JSON válidos");
    }
    
    $id = $data['id'] ?? null;
    $estado = $data['estado'] ?? null;
    $motivo = $data['motivo'] ?? '';

    // Validar datos requeridos
    if (!$id || !$estado) {
        throw new Exception("Faltan datos requeridos: id o estado");
    }

    // Conectar a la base de datos
    $db = Database::getInstance();
    $conn = $db->getConnection();
    
    // Verificar que existe la solicitud
    $sqlCheck = "SELECT id FROM solicitudes WHERE id = :id";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->execute([':id' => $id]);
    
    if ($stmtCheck->rowCount() === 0) {
        throw new Exception("Solicitud no encontrada con ID: " . $id);
    }
    
    // Actualizar estado
    $sql = "UPDATE solicitudes 
            SET estado = :estado, 
                fecha_respuesta = NOW(),
                motivo_respuesta = :motivo,
                notificacion_enviada = 1
            WHERE id = :id";
    
    $stmt = $conn->prepare($sql);
    $result = $stmt->execute([
        ':estado' => $estado,
        ':motivo' => $motivo,
        ':id' => $id
    ]);

    if ($result) {
        $response = [
            'success' => true,
            'message' => 'Estado actualizado y notificación generada exitosamente'
        ];
    } else {
        throw new Exception("No se pudo actualizar la solicitud");
    }

    echo json_encode($response);

} catch (Exception $e) {
    error_log("❌ Error en actualizarEstadoSolicitud: " . $e->getMessage());
    
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>