<?php
class Database {
    private static $instance = null;
    private $con = null;

    private function __construct() {
        $dbHost = getenv('DB_HOST') ?: 'localhost';
        $dbName = getenv('DB_NAME') ?: 'wxwdrnht_integrado_db';
        $dbUser = getenv('DB_USER') ?: 'root';
        $dbPass = getenv('DB_PASS') ?: '';

        $dsn = "mysql:host={$dbHost};dbname={$dbName};charset=utf8mb4";

        try {
            $this->con = new PDO($dsn, $dbUser, $dbPass);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (Throwable $e) {
            error_log("❌ Database Connection Error: " . $e->getMessage());
            // No exponer el error real al usuario:
            die("Error de conexión. Inténtelo más tarde.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new static();
        }
        return self::$instance;
    }

    public function getConnection() {
        if ($this->con === null) {
            throw new Exception("No hay conexión disponible.");
        }
        return $this->con;
    }

    public static function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    public function executeQuery($sql, $params = []) {
        try {
            $pdo = $this->getConnection();
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (Throwable $e) {
            error_log("⚠ SQL Query Error: " . $e->getMessage());
            throw new Exception("Error al ejecutar la consulta.");
        }
    }

    /** Método útil para probar si la BD conecta correctamente */
    public function testConnection() {
        try {
            $this->getConnection();
            return true;
        } catch (Exception $e) {
            return false;
        }
    }
}
?>
