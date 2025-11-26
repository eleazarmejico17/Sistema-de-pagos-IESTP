<?php
require_once '../config/conexion-local.php';

session_start();

// Recibir datos del formulario
$usuario = $_POST['usuario'] ?? '';
$password = $_POST['password'] ?? '';

// Validar entrada
if (empty($usuario) || empty($password)) {
    header("Location: login.html");
    exit();
}

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();

    // Buscar usuario
    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE usuario = ? LIMIT 1");
    $stmt->execute([$usuario]);
    $user = $stmt->fetch();

    if (!$user || !password_verify($password, $user['password'])) {
        // Credenciales incorrectas
        header("Location: login.html");
        exit();
    }

    // Guardar datos en sesión
    $_SESSION['usuario_id'] = $user['id'];
    $_SESSION['tipo_usuario'] = $user['tipo']; // 1 = empleado, 2 = estudiante
    $_SESSION['estuempleado_id'] = $user['estuempleado'];

    // Redirigir según tipo
    if ($user['tipo'] == 2) {
        // Estudiante
        header("Location: views/dashboard-usuario.php");
        exit();
    } elseif ($user['tipo'] == 1) {
        // Empleado
        $empleadoId = $user['estuempleado'];

        // Obtener prog_estudios del empleado
        $stmt = $pdo->prepare("SELECT prog_estudios FROM empleado WHERE id = ? LIMIT 1");
        $stmt->execute([$empleadoId]);
        $empleado = $stmt->fetch();

        if (!$empleado) {
            header("Location: login.html");
            exit();
        }

        $progEstudiosId = $empleado['prog_estudios'];

        // Obtener nombre del programa
        $stmt = $pdo->prepare("SELECT nom_progest FROM prog_estudios WHERE id = ? LIMIT 1");
        $stmt->execute([$progEstudiosId]);
        $prog = $stmt->fetch();

        if (!$prog) {
            header("Location: login.html");
            exit();
        }

        $nomProgest = strtoupper(trim($prog['nom_progest']));

        // Redirigir según el nombre del programa
        switch ($nomProgest) {
            case 'ADMINSISPAGOS':
                header("Location: views/dashboard-admin.php");
                break;
            case 'BIENESTARSISPAGOS':
                header("Location: views/dashboard-bienestar.php");
                break;
            case 'DIRECIONSISPAGOS':
                header("Location: views/dashboard-direcion.php");
                break;
            default:
                header("Location: login.html");
                exit();
        }
        exit();
    } else {
        // Tipo no válido
        header("Location: login.html");
        exit();
    }

} catch (Exception $e) {
    // Error de conexión o consulta
    error_log("Error en login: " . $e->getMessage());
    header("Location: login.html");
    exit();
}
?>