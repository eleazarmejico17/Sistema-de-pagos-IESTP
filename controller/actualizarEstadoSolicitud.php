<?php
header('Content-Type: application/json');
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
require_once __DIR__ . '/../config/conexion.php';

try {
    $input = file_get_contents("php://input");
    $data = json_decode($input, true);

    if (!$data) {
        throw new Exception("JSON inválido");
    }

    $id     = $data['id'] ?? null;
    $estado = $data['estado'] ?? null;
    $motivo = $data['motivo'] ?? '';

    if (!$id || !$estado) {
        throw new Exception("ID y estado son obligatorios");
    }

    // ✅ CONEXIÓN CORRECTA
    $db = Conexion::getInstance()->getConnection();

    // VALIDAMOS ID
    $check = $db->prepare("SELECT id FROM solicitud WHERE id = :id");
    $check->execute([':id' => $id]);
    if ($check->rowCount() === 0) {
        throw new Exception("Solicitud no encontrada");
    }

    // BUSCAR EMPLEADO SEGÚN SESIÓN
    $empleadoId = null;
    if (isset($_SESSION['usuario']['correo'])) {
        $stmtEmp = $db->prepare("
            SELECT id FROM empleado 
            WHERE (maili_emp = :correo OR mailp_emp = :correo) 
              AND estado = 1
            LIMIT 1
        ");
        $stmtEmp->execute([':correo' => $_SESSION['usuario']['correo']]);
        $emp = $stmtEmp->fetch(PDO::FETCH_ASSOC);
        if ($emp) {
            $empleadoId = (int)$emp['id'];
        }
    }

    // CONSTRUIR CAMPOS
    $set = [
        'estado = :estado',
        'motivo_respuesta = :motivo',
        'fecha_respuesta = NOW()',
        'notificacion_enviada = 1'
    ];

    $params = [
        ':estado' => $estado,
        ':motivo' => $motivo,
        ':id'     => $id
    ];

    if ($empleadoId !== null) {
        $set[] = 'empleado_id = :empleado_id';
        $params[':empleado_id'] = $empleadoId;
    }

    // ACTUALIZAR
    $sql = "UPDATE solicitud SET " . implode(', ', $set) . " WHERE id = :id";
    $stmt = $db->prepare($sql);
    $stmt->execute($params);

    // INSERTAR HISTORIAL SI EXISTE EMPLEADO
    if ($empleadoId !== null) {
        $historial = $db->prepare("
            INSERT INTO historial_solicitudes 
            (solicitud_id, estado, empleado_id, comentarios)
            VALUES (:sid, :estado, :emp, :coment)
        ");
        $historial->execute([
            ':sid'   => $id,
            ':estado'=> $estado,
            ':emp'   => $empleadoId,
            ':coment'=> $motivo
        ]);
    }

    // CREAR TABLA NOTIFICACIONES SI NO EXISTE
    $db->exec("
        CREATE TABLE IF NOT EXISTS notificaciones (
            id INT AUTO_INCREMENT PRIMARY KEY,
            solicitud_id INT NOT NULL,
            usuario_id INT,
            mensaje TEXT NOT NULL,
            tipo ENUM('Aprobado','Rechazado','Aviso') DEFAULT 'Aviso',
            leido TINYINT(1) DEFAULT 0,
            creado_en DATETIME DEFAULT CURRENT_TIMESTAMP
        )
    ");

    // OBTENER SOLICITUD
    $stmt = $db->prepare("SELECT correo FROM solicitud WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $sol = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($sol && !empty($sol['correo'])) {
        // BUSCAR ESTUDIANTE
        $stmt = $db->prepare("
            SELECT u.id FROM usuarios u
            INNER JOIN roles r ON r.id = u.rol_id
            WHERE u.correo = :correo AND r.nombre = 'usuario'
            LIMIT 1
        ");
        $stmt->execute([':correo' => $sol['correo']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            $mensaje = "Su solicitud ha sido " . strtolower($estado);
            if ($estado === 'Rechazado' && $motivo) {
                $mensaje .= ". Motivo: " . $motivo;
            }

            $tipo = $estado === 'Aprobado' ? 'Aprobado' : 'Rechazado';

            $stmt = $db->prepare("
                INSERT INTO notificaciones 
                (solicitud_id, usuario_id, mensaje, tipo)
                VALUES (:sid, :uid, :msg, :tipo)
            ");

            $stmt->execute([
                ':sid'  => $id,
                ':uid'  => $user['id'],
                ':msg'  => $mensaje,
                ':tipo' => $tipo
            ]);
        }
    }

    echo json_encode([
        "success" => true,
        "message" => "Solicitud actualizada correctamente"
    ]);

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        "success" => false,
        "error" => $e->getMessage()
    ]);
}
