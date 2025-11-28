<?php
require_once __DIR__ . '/../config/conexion.php';

class TipoPagoModel
{
    private $db;

    public function __construct()
    {
        
        $this->db = Database::getInstance()->getConnection();
    }

    public function insert($nombre, $descripcion)
    {
        $sql = "INSERT INTO tipo_pago (nombre, descripcion) VALUES (:nombre, :descripcion)";
        $query = $this->db->prepare($sql);

        $query->bindParam(':nombre', $nombre);
        $query->bindParam(':descripcion', $descripcion);

        return $query->execute();
    }
}

