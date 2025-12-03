<?php
header('Content-Type: application/json');

try {
    session_start();
    require_once __DIR__ . '/../config/conexion.php';

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

    // Buscar el ID del empleado por el correo del usuario en sesión
    $empleadoId = null;
    if (isset($_SESSION['usuario']['correo'])) {
        try {
            $stmtEmp = $conn->prepare("SELECT id FROM empleado WHERE (maili_emp = :correo OR mailp_emp = :correo) AND estado = 1 LIMIT 1");
            $stmtEmp->execute([':correo' => $_SESSION['usuario']['correo']]);
            $empleado = $stmtEmp->fetch(PDO::FETCH_ASSOC);
            if ($empleado) {
                $empleadoId = (int)$empleado['id'];
            }
        } catch (Exception $e) {
            error_log('Error al buscar empleado: ' . $e->getMessage());
        }
    }

    // Construir la consulta dinámicamente
    $campos = [
        'estado = :estado',
        'motivo_respuesta = :motivo',
        'fecha_respuesta = NOW()',
        'notificacion_enviada = 1'
    ];
    
    $params = [
        ":estado" => $estado,
        ":motivo" => $motivo,
        ":id" => $id
    ];
    
    // Solo incluir empleado_id si tiene un valor válido
    if ($empleadoId !== null && $empleadoId > 0) {
        $campos[] = 'empleado_id = :empleado_id';
        $params[':empleado_id'] = $empleadoId;
    }

    // ACTUALIZAMOS
    $sql = "UPDATE solicitud 
            SET " . implode(', ', $campos) . "
            WHERE id = :id";

    $stmt = $conn->prepare($sql);
    $stmt->execute($params);

    // Registrar en historial_solicitudes
    try {
        $historialSql = "INSERT INTO historial_solicitudes (solicitud_id, estado, empleado_id, comentarios) 
                         VALUES (:solicitud_id, :estado, :empleado_id, :comentarios)";
        $historialStmt = $conn->prepare($historialSql);
        $historialStmt->execute([
            ':solicitud_id' => $id,
            ':estado' => $estado,
            ':empleado_id' => $empleadoId,
            ':comentarios' => $motivo
        ]);
    } catch (Exception $e) {
        error_log('Error al registrar en historial: ' . $e->getMessage());
        // No fallar si el historial falla
    }

    // Crear notificación para el estudiante (rol usuario)
    try {
        // Verificar si la tabla existe, si no, crearla
        $checkTable = $conn->query("SHOW TABLES LIKE 'notificaciones'");
        if ($checkTable->rowCount() === 0) {
            $createTable = "CREATE TABLE IF NOT EXISTS notificaciones (
                id INT AUTO_INCREMENT PRIMARY KEY,
                solicitud_id INT NOT NULL,
                usuario_id INT,
                mensaje TEXT NOT NULL,
                tipo ENUM('Aprobado','Rechazado','Aviso') DEFAULT 'Aviso',
                leido TINYINT(1) DEFAULT 0,
                creado_en DATETIME DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (solicitud_id) REFERENCES solicitud(id) ON DELETE CASCADE,
                FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
                INDEX idx_usuario_leido (usuario_id, leido),
                INDEX idx_solicitud (solicitud_id)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $conn->exec($createTable);
        }
        
        // Obtener información de la solicitud
        $sqlSolicitud = "SELECT nombre, correo FROM solicitud WHERE id = :id";
        $stmtSolicitud = $conn->prepare($sqlSolicitud);
        $stmtSolicitud->execute([':id' => $id]);
        $solicitud = $stmtSolicitud->fetch(PDO::FETCH_ASSOC);
        
        if ($solicitud && !empty($solicitud['correo'])) {
            // Buscar usuario con rol "usuario" (estudiante) por correo
            $sqlUsuario = "SELECT u.id FROM usuarios u 
                          INNER JOIN roles r ON r.id = u.rol_id 
                          WHERE u.correo = :correo AND r.nombre = 'usuario' AND u.estado = 'activo' 
                          LIMIT 1";
            $stmtUsuario = $conn->prepare($sqlUsuario);
            $stmtUsuario->execute([':correo' => $solicitud['correo']]);
            $usuarioEstudiante = $stmtUsuario->fetch(PDO::FETCH_ASSOC);
            
            if ($usuarioEstudiante) {
                $tipoNotificacion = $estado === 'Aprobado' ? 'Aprobado' : 'Rechazado';
                $mensaje = "Su solicitud ha sido " . strtolower($estado);
                if ($estado === 'Rechazado' && !empty($motivo)) {
                    $mensaje .= ". Motivo: " . htmlspecialchars($motivo, ENT_QUOTES, 'UTF-8');
                }
                
                $sqlNotificacion = "INSERT INTO notificaciones 
                                   (solicitud_id, usuario_id, mensaje, tipo, leido, creado_en) 
                                   VALUES (:solicitud_id, :usuario_id, :mensaje, :tipo, 0, NOW())";
                $stmtNotificacion = $conn->prepare($sqlNotificacion);
                $stmtNotificacion->execute([
                    ':solicitud_id' => $id,
                    ':usuario_id' => $usuarioEstudiante['id'],
                    ':mensaje' => $mensaje,
                    ':tipo' => $tipoNotificacion
                ]);
            }
        }
    } catch (Exception $e) {
        error_log('Error al crear notificación para estudiante: ' . $e->getMessage());
        // No fallar si la notificación no se puede crear
    }

    echo json_encode([
        "success" => true,
        "message" => "Solicitud actualizada correctamente"
    ]);

} catch (PDOException $e) {
    error_log('Error PDO en actualizarEstadoSolicitud: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        "success" => false,
        "error" => "Error de base de datos. Por favor, contacta al administrador."
    ]);
} catch (Exception $e) {
    error_log('Error en actualizarEstadoSolicitud: ' . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
