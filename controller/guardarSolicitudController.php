<?php
require_once '../config/conexion.php';

class GuardarSolicitudController {

    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    public function guardarSolicitud($datos) {

        try {

            // VALIDAR CAMPOS
            if (
                empty($datos['nombre']) ||
                empty($datos['telefono']) ||
                empty($datos['tipo']) ||
                empty($datos['fecha']) ||
                empty($datos['descripcion'])
            ) {
                throw new Exception("Campos incompletos");
            }

            // SUBIR ARCHIVOS
            $archivos = $this->subirArchivos();

            // SQL CORREGIDO (TABLA REAL: solicitud)
            $sql = "INSERT INTO solicitud 
                    (nombre, telefono, tipo_solicitud, fecha, descripcion, archivos, fecha_registro)
                    VALUES (:nombre, :telefono, :tipo, :fecha, :descripcion, :archivos, NOW())";

            $params = [
                ':nombre'      => trim($datos['nombre']),
                ':telefono'    => trim($datos['telefono']),
                ':tipo'        => trim($datos['tipo']),
                ':fecha'       => $datos['fecha'],
                ':descripcion' => trim($datos['descripcion']),
                ':archivos'    => $archivos
            ];

            $stmt = $this->db->executeQuery($sql, $params);
            
            // Obtener el ID de la solicitud recién creada
            $solicitudId = $this->db->getConnection()->lastInsertId();
            
            // Crear notificación para usuarios de bienestar
            if ($solicitudId) {
                $this->crearNotificacionBienestar($solicitudId, $datos);
            }

            return [
                'success' => true,
                'message' => '✅ Solicitud enviada correctamente'
            ];

        } catch (Exception $e) {
            return [
                'success' => false,
                'error'   => $e->getMessage()
            ];
        }
    }

    private function subirArchivos() {

        if (empty($_FILES['archivo']['name'][0])) {
            return '';
        }

        $carpeta = "../uploads/solicitudes/";
        if (!file_exists($carpeta)) {
            mkdir($carpeta, 0777, true);
        }

        $lista = [];

        foreach ($_FILES['archivo']['name'] as $i => $nombre) {

            if ($_FILES['archivo']['error'][$i] === 0) {

                $nuevoNombre = uniqid() . "_" . basename($nombre);
                $destino = $carpeta . $nuevoNombre;

                if (move_uploaded_file($_FILES['archivo']['tmp_name'][$i], $destino)) {
                    $lista[] = $nuevoNombre;
                }
            }
        }

        return implode(",", $lista);
    }

    private function crearNotificacionBienestar($solicitudId, $datos) {
        try {
            $conn = $this->db->getConnection();
            
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
            
            // Obtener todos los usuarios con rol de bienestar
            $sqlUsuarios = "SELECT id FROM usuarios u 
                           INNER JOIN roles r ON r.id = u.rol_id 
                           WHERE r.nombre = 'bienestar' AND u.estado = 'activo'";
            $stmtUsuarios = $conn->prepare($sqlUsuarios);
            $stmtUsuarios->execute();
            $usuariosBienestar = $stmtUsuarios->fetchAll(PDO::FETCH_ASSOC);
            
            // Crear notificación para cada usuario de bienestar
            $mensaje = "Nueva solicitud de " . htmlspecialchars($datos['nombre'], ENT_QUOTES, 'UTF-8') . 
                      " - Tipo: " . htmlspecialchars($datos['tipo'], ENT_QUOTES, 'UTF-8');
            
            $sqlNotificacion = "INSERT INTO notificaciones 
                               (solicitud_id, usuario_id, mensaje, tipo, leido, creado_en) 
                               VALUES (:solicitud_id, :usuario_id, :mensaje, 'Aviso', 0, NOW())";
            $stmtNotificacion = $conn->prepare($sqlNotificacion);
            
            foreach ($usuariosBienestar as $usuario) {
                $stmtNotificacion->execute([
                    ':solicitud_id' => $solicitudId,
                    ':usuario_id' => $usuario['id'],
                    ':mensaje' => $mensaje
                ]);
            }
        } catch (Exception $e) {
            // No fallar si la notificación no se puede crear
            error_log('Error al crear notificación: ' . $e->getMessage());
        }
    }
}


// EJECUTAR SI ES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header("Content-Type: application/json");

    $controlador = new GuardarSolicitudController();
    echo json_encode($controlador->guardarSolicitud($_POST));
    exit;
}
