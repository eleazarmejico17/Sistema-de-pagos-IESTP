<?php
class Conexion {
    private $host = "localhost";
    private $usuario = "root";
    private $clave = "";
    // Base de datos definida en el script SQL adjunto
    private $db = "pago_descuentos";
    private $conexion;

    public function __construct() {
        $this->conexion = new mysqli(
            $this->host,
            $this->usuario,
            $this->clave,
            $this->db
        );

        if ($this->conexion->connect_error) {
            die("Error de conexión: " . $this->conexion->connect_error);
        }

        $this->conexion->set_charset("utf8");
    }

    public function getConexion() {
        return $this->conexion;
    }

    public function cerrarConexion() {
        if ($this->conexion) {
            $this->conexion->close();
        }
    }
}
?>
