<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../config/conexion.php';

class GuardarSolicitudController {

    private $db;

    public function __construct() {
        $this->db = Conexion::getInstance()->getConnection();
    }

    public function guardarSolicitud($datos) {

        try {

            if (
                empty($datos['nombre']) ||
                empty($datos['telefono']) ||
                empty($datos['tipo']) ||
                empty($datos['fecha']) ||
                empty($datos['descripcion'])
            ) {
                throw new Exception("Campos incompletos");
            }

            $archivos = $this->subirArchivos();

            $sql = "INSERT INTO solicitud 
                    (nombre, telefono, tipo_solicitud, descripcion, archivos, fecha, fecha_registro)
                    VALUES (:nombre, :telefono, :tipo, :descripcion, :archivos, :fecha, NOW())";

            $params = [
                ':nombre' => trim($datos['nombre']),
                ':telefono' => trim($datos['telefono']),
                ':tipo' => trim($datos['tipo']),
                ':descripcion' => trim($datos['descripcion']),
                ':archivos' => $archivos,
                ':fecha' => $datos['fecha']
            ];

            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);

            return ['success'=>true,'message'=>'✅ Solicitud registrada correctamente'];

        } catch(Exception $e){
            return ['success'=>false,'error'=>$e->getMessage()];
        }
    }

    private function subirArchivos(){

        if (empty($_FILES['archivo']['name'][0])) return '';

        $carpeta = "../uploads/solicitudes/";
        if (!file_exists($carpeta)) mkdir($carpeta,0777,true);

        $lista = [];

        foreach ($_FILES['archivo']['name'] as $i=>$nombre){

            if ($_FILES['archivo']['error'][$i]==0){

                $nuevo = uniqid()."_".basename($nombre);
                $destino = $carpeta.$nuevo;

                if(move_uploaded_file($_FILES['archivo']['tmp_name'][$i],$destino)){
                    $lista[] = $nuevo;
                }
            }
        }

        return implode(",",$lista);
    }
}

if ($_SERVER['REQUEST_METHOD']==='POST'){
    header('Content-Type: application/json');
    $ctrl = new GuardarSolicitudController();
    echo json_encode($ctrl->guardarSolicitud($_POST));
    exit;
}

echo json_encode(['success'=>false,'error'=>'Método no permitido']);
