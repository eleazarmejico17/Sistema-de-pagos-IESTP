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

    // VALIDAMOS ID en tabla 'solicitudes'
    $check = $db->prepare("SELECT id FROM solicitudes WHERE id = :id");
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

    // CONSTRUIR CAMPOS para tabla 'solicitudes'
    $set = [
        'estado = :estado',
        'observaciones = :motivo',
        'fecha_revision = NOW()'
    ];

    $params = [
        ':estado' => $estado,
        ':motivo' => $motivo,
        ':id'     => $id
    ];

    if ($empleadoId !== null) {
        $set[] = 'empleado = :empleado_id';
        $params[':empleado_id'] = $empleadoId;
    }

    // ACTUALIZAR en tabla 'solicitudes'
    $sql = "UPDATE solicitudes SET " . implode(', ', $set) . " WHERE id = :id";
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

    // Nota: se omite lógica de notificaciones por correo/tabla 'notificaciones'

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
