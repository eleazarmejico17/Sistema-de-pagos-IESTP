<?php
require_once "../models/bienestarBeneficiariosModel.php";
require_once "../models/bienestarResolucionesModel.php";

class BienestarRegistroController {
    private $beneficiarioModel;
    private $resolucionModel;

    public function __construct() {
        $this->beneficiarioModel = new BeneficiarioModel();
        $this->resolucionModel = new ResolucionModel();
    }

    public function buscarEstudiante($dni) {
        return $this->beneficiarioModel->buscarEstudiantePorDNI($dni);
    }

    
    public function crearBeneficiario($data) {
        return $this->beneficiarioModel->crear($data);
    }

    public function crearResolucion($data, $files = []) {
        return $this->resolucionModel->crear($data, $files);
    }

    public function listarBeneficiarios() {
        return $this->beneficiarioModel->listar();
    }

    public function listarResoluciones() {
        return $this->resolucionModel->listar();
    }
}

