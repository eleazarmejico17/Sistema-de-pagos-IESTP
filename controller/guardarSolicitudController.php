<?php
// controller/guardarSolicitudController.php

// Incluir la configuración de la base de datos
require_once '../config/conexion.php';

class GuardarSolicitudController {
    private $db;
    
    public function __construct() {
        $this->db = Database::getInstance();
    }
    
    public function guardarSolicitud($datos) {
        try {
            // Validar datos requeridos
            if (empty($datos['nombre']) || empty($datos['telefono']) || 
                empty($datos['tipo']) || empty($datos['fecha']) || 
                empty($datos['descripcion'])) {
                throw new Exception("Todos los campos marcados como requeridos deben ser completados");
            }
            
            // Procesar archivos subidos
            $nombresArchivos = $this->procesarArchivos();
            
            // Preparar la consulta SQL
            $sql = "INSERT INTO solicitudes (
                nombre_completo, 
                telefono, 
                tipo_solicitud, 
                fecha_solicitud, 
                descripcion, 
                archivos
            ) VALUES (
                :nombre, 
                :telefono, 
                :tipo, 
                :fecha, 
                :descripcion, 
                :archivos
            )";
            
            $params = [
                ':nombre' => trim($datos['nombre']),
                ':telefono' => trim($datos['telefono']),
                ':tipo' => trim($datos['tipo']),
                ':fecha' => $datos['fecha'],
                ':descripcion' => trim($datos['descripcion']),
                ':archivos' => $nombresArchivos
            ];
            
            // Ejecutar la consulta
            $stmt = $this->db->executeQuery($sql, $params);
            $idInsertado = $this->db->getConnection()->lastInsertId();
            
            return [
                'success' => true,
                'message' => '✅ Solicitud enviada correctamente',
                'id' => $idInsertado
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage()
            ];
        }
    }
    
    private function procesarArchivos() {
        $nombresArchivos = [];
        
        if (isset($_FILES['archivo']) && is_array($_FILES['archivo']['name'])) {
            $carpetaDestino = '../uploads/solicitudes/';
            
            // Crear carpeta si no existe
            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }
            
            for ($i = 0; $i < count($_FILES['archivo']['name']); $i++) {
                if ($_FILES['archivo']['error'][$i] === UPLOAD_ERR_OK) {
                    $nombreArchivo = uniqid() . '_' . basename($_FILES['archivo']['name'][$i]);
                    $rutaCompleta = $carpetaDestino . $nombreArchivo;
                    
                    if (move_uploaded_file($_FILES['archivo']['tmp_name'][$i], $rutaCompleta)) {
                        $nombresArchivos[] = $nombreArchivo;
                    }
                }
            }
        }
        
        return empty($nombresArchivos) ? '' : implode(',', $nombresArchivos);
    }
}

// Procesar el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    header('Content-Type: application/json');
    
    try {
        $controller = new GuardarSolicitudController();
        $resultado = $controller->guardarSolicitud($_POST);
        
        echo json_encode($resultado);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'error' => 'Error interno del servidor: ' . $e->getMessage()
        ]);
    }
    
    exit;
}
?>