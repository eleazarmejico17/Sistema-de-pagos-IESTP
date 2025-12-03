<?php
require_once __DIR__ . '/../config/conexion-local.php';

class Estudiantes {
    private $db;

    public function __construct() {
        $this->db = Conexion::getInstance()->getConnection();
    }

    // Obtener todos los estudiantes
    public function getAll() {
        $sql = $this->db->prepare("
            SELECT 
                e.id,
                CONCAT(e.ap_est, ' ', e.am_est, ' ', e.nom_est) AS nombre_completo,
                TIMESTAMPDIFF(YEAR, e.fecnac_est, CURDATE()) AS edad,
                e.sex_est AS sexo,
                e.dni_est,
                e.estado
            FROM estudiante e
            ORDER BY e.id DESC
        ");

        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    // Crear estudiante
    public function create($data) {

        $sql = $this->db->prepare("
            INSERT INTO estudiante (
                ubdistrito, dni_est, ap_est, am_est, nom_est, sex_est, cel_est,
                ubigeodir_est, ubigeonac_est, dir_est, mailp_est, maili_est,
                fecnac_est, foto_est, estado
            ) VALUES (
                :ubdistrito, :dni_est, :ap_est, :am_est, :nom_est, :sex_est, :cel_est,
                :ubigeodir_est, :ubigeonac_est, :dir_est, :mailp_est, :maili_est,
                :fecnac_est, :foto_est, 1
            )
        ");

        return $sql->execute([
            ':ubdistrito'     => $data['ubdistrito'],
            ':dni_est'        => $data['dni_est'],
            ':ap_est'         => $data['ap_est'],
            ':am_est'         => $data['am_est'],
            ':nom_est'        => $data['nom_est'],
            ':sex_est'        => $data['sex_est'],
            ':cel_est'        => $data['cel_est'],
            ':ubigeodir_est'  => $data['ubigeodir_est'],
            ':ubigeonac_est'  => $data['ubigeonac_est'],
            ':dir_est'        => $data['dir_est'],
            ':mailp_est'      => $data['mailp_est'],
            ':maili_est'      => $data['maili_est'],
            ':fecnac_est'     => $data['fecnac_est'],
            ':foto_est'       => $data['foto_est']
        ]);
    }

    // Eliminar estudiante
    public function delete($id) {
        $sql = $this->db->prepare("DELETE FROM estudiante WHERE id = :id");
        return $sql->execute([':id' => $id]);
    }
}
