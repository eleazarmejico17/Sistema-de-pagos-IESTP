<?php
class Database {
    private static $instance = null;
    private $con;

    // Constructor privado: evita instanciación directa
    private function __construct() {
        // Cargar variables de entorno (si existen)
        $host = getenv('DB_HOST') ?: '50.31.174.34';
        $dbname = getenv('DB_NAME') ?: 'wxwdrnht_integrado_db';
        $user = getenv('DB_USER') ?: 'wxwdrnht_wxwdrnht_integrado_db'; 
        $pass = getenv('DB_PASS') ?: 'integrado_db2025';     

        $dsn = "mysql:host={$host};dbname={$dbname};charset=utf8mb4";

        try {
            $this->con = new PDO($dsn, $user, $pass);
            $this->con->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $this->con->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $this->con->setAttribute(PDO::ATTR_EMULATE_PREPARES, false);
        } catch (PDOException $e) {
            error_log("❌ Error de conexión: " . $e->getMessage());
            throw new Exception("Error de conexión a la base de datos. Por favor, inténtelo más tarde.");
        }
    }

    // Patrón Singleton: obtener única instancia
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Obtener conexión PDO
    public function getConnection() {
        return $this->con;
    }

    // Sanitizar entrada de usuario
    public static function sanitizeInput($data) {
        return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
    }

    // Ejecutar consultas con parámetros preparados
    public function executeQuery($sql, $params = []) {
        try {
            $stmt = $this->con->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("❌ Error en la consulta: " . $e->getMessage());
            throw new Exception("Error al procesar la solicitud en la base de datos.");
        }
    }
}
?>
