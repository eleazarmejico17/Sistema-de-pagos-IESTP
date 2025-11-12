<?php
require_once __DIR__ . '/../config/conexion.php';

class ModelLogin {
	private $db;

	public function __construct() {
		// Usar la clase Database (PDO) definida en config/conexion.php
		$this->db = Database::getInstance()->getConnection();
	}

	/**
	 * Intenta autenticar un usuario por nombre de usuario o correo.
	 * Retorna un array con keys: success (bool), message (string), data (array|null), redirect (string|null)
	 */
	public function login(string $usuario, string $contrasena): array {
		// Sanitizar entrada
		$usuario = Database::sanitizeInput($usuario);
		$contrasena = trim($contrasena);

		// Buscar por usuario o correo
		$sql = "SELECT u.id, u.usuario, u.nombre_completo, u.correo, u.contrasena_hash, r.nombre AS rol_nombre, r.id AS rol_id
				FROM usuarios u
				LEFT JOIN roles r ON u.rol_id = r.id
				WHERE u.usuario = :usuario OR u.correo = :usuario
				LIMIT 1";

		try {
			$stmt = $this->db->prepare($sql);
			$stmt->execute([':usuario' => $usuario]);
			$row = $stmt->fetch(PDO::FETCH_ASSOC);

			if (!$row) {
				return [
					'success' => false,
					'message' => 'Usuario o correo no encontrado',
					'data' => null,
					'redirect' => null
				];
			}

			// Verificar la contraseña (se asume que en la BD está el hash con password_hash)
			if (!password_verify($contrasena, $row['contrasena_hash'])) {
				return [
					'success' => false,
					'message' => 'Contraseña incorrecta',
					'data' => null,
					'redirect' => null
				];
			}

			// Preparar datos de sesión
			$userData = [
				'id' => $row['id'],
				'usuario' => $row['usuario'],
				'nombre_completo' => $row['nombre_completo'],
				'correo' => $row['correo'],
				'rol_id' => $row['rol_id'],
				'rol_nombre' => $row['rol_nombre'] ?? 'usuario'
			];

			// Iniciar sesión
			$this->iniciarSesion($userData);

			// Decidir ruta de redirección según rol (nombres en minúscula)
			$rol = strtolower($userData['rol_nombre']);
			$redirect = 'views/dashboard-usuario.php';
			if (strpos($rol, 'admin') !== false || $rol === 'superadmin') {
				$redirect = 'views/dashboard-admin.php';
			} elseif (strpos($rol, 'direccion') !== false) {
				$redirect = 'views/dashboard-direccion.php';
			} elseif (strpos($rol, 'bienestar') !== false) {
				$redirect = 'views/dashboard-bienestar.php';
			}

			return [
				'success' => true,
				'message' => 'Autenticación correcta',
				'data' => $userData,
				'redirect' => $redirect
			];
		} catch (PDOException $e) {
			error_log('Login query error: ' . $e->getMessage());
			return [
				'success' => false,
				'message' => 'Error en el servidor al autenticar',
				'data' => null,
				'redirect' => null
			];
		}
	}

	/**
	 * Inicia la sesión PHP y guarda datos del usuario.
	 */
	private function iniciarSesion(array $userData): void {
		if (session_status() === PHP_SESSION_NONE) {
			session_start();
		}

		// Guardar datos mínimos
		$_SESSION['user'] = [
			'id' => $userData['id'],
			'usuario' => $userData['usuario'],
			'nombre_completo' => $userData['nombre_completo'],
			'correo' => $userData['correo'],
			'rol_id' => $userData['rol_id'],
			'rol_nombre' => $userData['rol_nombre']
		];
	}
}

/**
 * Ejemplo de uso desde un controlador (simplificado):
 *
 * require_once __DIR__ . '/../models/model_login.php';
 * $model = new ModelLogin();
 * $res = $model->login($_POST['usuario'], $_POST['contrasena']);
 * if ($res['success']) {
 *     header('Location: ' . $res['redirect']);
 *     exit;
 * } else {
 *     // mostrar error
 * }
 */

?>
