<?php
// api/resoluciones/index.php
header("Content-Type: application/json");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
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

// --- SOLO EMPLEADOS ---
if ($USUARIO_TIPO_ACTUAL != 1) {
    http_response_code(403);
    echo json_encode(["error" => "Acceso denegado. Se requiere personal autorizado."]);
    exit;
}

try {
    switch ($method) {
        case 'GET':
            // --- LISTAR TODAS LAS RESOLUCIONES ---
            $query = "SELECT id, numero_resolucion, titulo, fecha_inicio, fecha_fin 
                      FROM resoluciones 
                      ORDER BY fecha_inicio DESC";
            $stmt = $db->query($query);
            $resoluciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode(["data" => $resoluciones]);
            break;

        case 'POST':
            // --- CREAR UNA NUEVA RESOLUCIÓN ---
            $data = json_decode(file_get_contents("php://input"));
            
            if (empty($data->numero_resolucion) || empty($data->titulo) || empty($data->fecha_inicio)) {
                http_response_code(400);
                echo json_encode(["error" => "numero_resolucion, titulo y fecha_inicio son requeridos"]);
                exit;
            }

            $query = "INSERT INTO resoluciones 
                        (numero_resolucion, titulo, texto_respaldo, ruta_documento, 
                         fecha_inicio, fecha_fin, creado_por, creado_en)
                      VALUES
                        (:num, :titulo, :texto, :ruta, :f_ini, :f_fin, :creador, NOW())";
            
            $stmt = $db->prepare($query);
            $stmt->execute([
                ':num' => $data->numero_resolucion,
                ':titulo' => $data->titulo,
                ':texto' => $data->texto_respaldo ?? null,
                ':ruta' => $data->ruta_documento ?? null,
                ':f_ini' => $data->fecha_inicio,
                ':f_fin' => $data->fecha_fin ?? null,
                ':creador' => $ID_EST_EMP_ACTUAL // ID del empleado logueado
            ]);

            http_response_code(201);
            echo json_encode(["mensaje" => "Resolución creada exitosamente", "id" => $db->lastInsertId()]);
            break;
            
        default:
            http_response_code(405);
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["error" => $e->getMessage()]);
}
?>