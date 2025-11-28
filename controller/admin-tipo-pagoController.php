<?php
require_once __DIR__ . '/../models/admin-tipo-pagoModel.php';

class TipoPagoController
{
    private $model;

    public function __construct()
    {
        // modelo ya tiene conexión interna
        $this->model = new TipoPagoModel();
    }

    public function crear()
    {
        if ($_SERVER["REQUEST_METHOD"] !== "POST") {
            include "views/includes/admin/admin-tipo-pago.php";
            return;
        }

        $nombre = trim($_POST["nombre"] ?? "");
        $descripcion = trim($_POST["descripcion"] ?? "");

        if ($nombre === "") {
            $error = "El campo nombre es obligatorio.";
            include "views/includes/admin/admin-tipo-pago.php";
            return;
        }

        $ok = $this->model->insert($nombre, $descripcion);

        if ($ok) {
            $msg = "Tipo de pago registrado correctamente.";
        } else {
            $error = "No se pudo guardar el tipo de pago.";
        }

        include "views/includes/admin/admin-tipo-pago.php";
    }
}
