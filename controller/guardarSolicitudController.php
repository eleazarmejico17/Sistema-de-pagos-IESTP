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

            $this->db->executeQuery($sql, $params);

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
}


// EJECUTAR SI ES POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    header("Content-Type: application/json");

    $controlador = new GuardarSolicitudController();
    echo json_encode($controlador->guardarSolicitud($_POST));
    exit;
}
