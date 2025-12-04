<?php
require_once __DIR__ . '/../config/conexion.php';

class BeneficiarioModel {
    private $db;

    public function __construct() {
        // ✅ CORREGIDO
        $this->db = Conexion::getInstance()->getConnection();
        
    }

    public function buscarEstudiantePorDNI($dni) {
        $sql = $this->db->prepare("
            SELECT 
                e.id,
                e.dni_est,
                CONCAT(e.ap_est, ' ', e.am_est, ' ', e.nom_est) AS nombre_completo,
                e.cel_est,
                e.mailp_est,
                e.maili_est,
                m.prog_estudios,
                pe.nom_progest AS programa_nombre,
                m.per_acad AS ciclo,
                m.turno
            FROM estudiante e
            LEFT JOIN matricula m ON m.estudiante_id = e.id
            LEFT JOIN prog_estudios pe ON pe.id = m.prog_estudios
            WHERE e.dni_est = :dni
            ORDER BY m.id DESC
            LIMIT 1
        ");
        $sql->execute([':dni' => $dni]);
        return $sql->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($data) {
        $registradoPor = $data['registrado_por'] ?? null;

        $campos = [
            'estudiante_id',
            'resolucion_id',
            'porcentaje_descuento',
            'fecha_inicio',
            'fecha_fin',
            'activo'
        ];
        $valores = [
            ':estudiante_id',
            ':resolucion_id',
            ':porcentaje_descuento',
            ':fecha_inicio',
            ':fecha_fin',
            ':activo'
        ];
        
        $params = [
            ':estudiante_id' => $data['estudiante_id'],
            ':resolucion_id' => $data['resolucion_id'],
            ':porcentaje_descuento' => $data['porcentaje_descuento'],
            ':fecha_inicio' => $data['fecha_inicio'] ?? null,
            ':fecha_fin' => $data['fecha_fin'] ?? null,
            ':activo' => $data['activo'] ?? 1
        ];
        
        if ($registradoPor !== null && $registradoPor > 0) {
            $campos[] = 'registrado_por';
            $valores[] = ':registrado_por';
            $params[':registrado_por'] = $registradoPor;
        }
        
        $sql = $this->db->prepare("
            INSERT INTO beneficiarios (" . implode(', ', $campos) . ")
            VALUES (" . implode(', ', $valores) . ")
        ");
        
        return $sql->execute($params);
    }

    public function listar() {
        $sql = $this->db->prepare("
            SELECT 
                b.id,
                b.porcentaje_descuento,
                b.fecha_inicio,
                b.fecha_fin,
                b.activo,
                CONCAT(e.ap_est, ' ', e.am_est, ' ', e.nom_est) AS nombre_estudiante,
                e.dni_est,
                r.numero_resolucion,
                r.titulo AS resolucion_titulo
            FROM beneficiarios b
            INNER JOIN estudiante e ON e.id = b.estudiante_id
            INNER JOIN resoluciones r ON r.id = b.resolucion_id
            ORDER BY b.registrado_en DESC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}
