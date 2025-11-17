<?php
// api/pagos/index.php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

// 1. SEGURIDAD
include_once '../verificar_token.php';

// 2. CONEXIÓN
include_once '../../config/conexion.php';
$db = Database::getInstance()->getConnection();

$method = $_SERVER['REQUEST_METHOD'];
if ($method == "OPTIONS") {
    http_response_code(200);
    exit();
}

// Esta API solo soporta GET
if ($method != 'GET') {
    http_response_code(405);
    echo json_encode(["error" => "Método no permitido"]);
    exit;
}

try {
    // Los estudiantes ven sus pagos. Los empleados (aún) no ven nada aquí.
    if ($USUARIO_TIPO_ACTUAL == 2) { 
        $query = "SELECT 
                    p.id, p.monto_original, p.monto_descuento, p.monto_final, 
                    p.fecha_pago, tp.nombre as tipo_pago_nombre
                  FROM pagos p
                  JOIN tipo_pago tp ON p.tipo_pago = tp.id
                  WHERE p.estudiante = :id_estudiante
                  ORDER BY p.fecha_pago DESC, p.id DESC";
        
        $stmt = $db->prepare($query);
        $stmt->execute([':id_estudiante' => $ID_EST_EMP_ACTUAL]);
        $pagos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        http_response_code(200);
        echo json_encode(["data" => $pagos]);
        
    } else {
        http_response_code(403);
        echo json_encode(["error" => "Acción no permitida para este tipo de usuario"]);
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>
