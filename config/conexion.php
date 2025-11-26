<?php
class Database {
    private static $instance = null;
    private $con;

    // Datos de conexión (ajusta si cambias de servidor)
    private const HOST = '50.31.174.34';
    private const DB   = 'wxwdrnht_integrado_db';
    private const USER = 'wxwdrnht_wxwdrnht_integrado_db';
    private const PASS = 'integrado_db2025.';

    private function __construct() {
        $dsn = "mysql:host=" . self::HOST . ";dbname=" . self::DB . ";charset=utf8mb4";
        try {
            $this->con = new PDO($dsn, self::USER, self::PASS);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            // Muestra el error real (solo en desarrollo)
            die("❌ Error de conexión: " . $e->getMessage());
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->con;
    }

    public static function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query error: " . $e->getMessage());
            die("❌ Error en consulta: " . $e->getMessage());
        }
    }
}
?>