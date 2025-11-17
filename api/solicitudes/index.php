<?php
// api/solicitudes/index.php
header("Content-Type: application/json"); // 1. Siempre define el tipo de contenido

// 2. SEGURIDAD: Incluye el guardián.
// Si el token no es válido, el script 'verificar_token.php' morirá (exit) aquí.
include_once '../verificar_token.php';
// Si el script continúa, significa que $usuario_valido (de verificar_token.php) existe.

// 3. CONEXIÓN: Incluye la base de datos
include_once '../../config/database.php';

$database = new Database();
$db = $database->getConnection();

$method = $_SERVER['REQUEST_METHOD']; // Obtiene el método (GET, POST, PUT, DELETE)

try {
    switch ($method) {
        case 'GET':
            // --- LISTAR SOLICITUDES ---
            
            // Queremos las solicitudes de un estudiante: /api/solicitudes/?estudiante=5
            if (empty($_GET['estudiante'])) {
                http_response_code(400);
                echo json_encode(["error" => "ID de estudiante requerido"]);
                exit;
            }
            
            $id_estudiante = htmlspecialchars(strip_tags($_GET['estudiante']));

            // Consulta SQL (¡usando '?' para prevenir inyección SQL!)
            $query = "SELECT 
                        s.id, s.tipo_solicitud, s.descripcion, s.estado, s.fecha_solicitud,
                        r.numero_resolucion 
                      FROM 
                        solicitudes s
                      LEFT JOIN 
                        resoluciones r ON s.resoluciones = r.id
                      WHERE 
                        s.estudiante = ?";
            
            $stmt = $db->prepare($query);
            $stmt->execute([$id_estudiante]); // PDO se encarga de la seguridad
            
            $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            http_response_code(200);
            echo json_encode(["data" => $solicitudes]);
            break;

        case 'POST':
            // --- CREAR UNA NUEVA SOLICITUD ---
            $data = json_decode(file_get_contents("php://input"));

            // Validación simple
            if (empty($data->estudiante) || empty($data->tipo_solicitud)) {
                http_response_code(400);
                echo json_encode(["error" => "Datos incompletos"]);
                exit;
            }

            $query = "INSERT INTO solicitudes 
                        (estudiante, tipo_solicitud, descripcion, fecha_solicitud, estado) 
                      VALUES 
                        (:est, :tipo, :desc, NOW(), 'pendiente')";
            
            $stmt = $db->prepare($query);
            
            // Limpieza de datos
            $data->estudiante = htmlspecialchars(strip_tags($data->estudiante));
            $data->tipo_solicitud = htmlspecialchars(strip_tags($data->tipo_solicitud));
            $data->descripcion = htmlspecialchars(strip_tags($data->descripcion));

            // Bind de parámetros
            $stmt->bindParam(':est', $data->estudiante);
            $stmt->bindParam(':tipo', $data->tipo_solicitud);
            $stmt->bindParam(':desc', $data->descripcion);
            
            if ($stmt->execute()) {
                http_response_code(201); // 201 Created
                echo json_encode(["mensaje" => "Solicitud creada exitosamente"]);
            } else {
                http_response_code(500);
                echo json_encode(["error" => "No se pudo crear la solicitud"]);
            }
            break;
            
        // Aquí podrías añadir 'PUT' (para actualizar) y 'DELETE' (para borrar)
        
        default:
            http_response_code(405); // Method Not Allowed
            echo json_encode(["error" => "Método no permitido"]);
            break;
    }
} catch (Exception $e) {
    http_response_code(500); // Internal Server Error
    echo json_encode(["error" => $e->getMessage()]);
}

?>