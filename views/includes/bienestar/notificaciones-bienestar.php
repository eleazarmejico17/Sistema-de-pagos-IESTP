<?php
require_once __DIR__ . "/../../../config/conexion.php";
$notificaciones = []; $errorConsulta = null;
try { $db = Conexion::getInstance()->getConnection();
$sql = "SELECT s.id, CONCAT(est.nom_est, \" \", est.ap_est, \" \", est.am_est) AS nombre, est.cel_est AS telefono, est.mailp_est AS correo, s.tipo_solicitud, s.descripcion, s.foto AS archivos, s.fecha_solicitud AS fecha, CASE WHEN s.estado = \"aprobado\" THEN \"Aprobado\" WHEN s.estado = \"rechazado\" THEN \"Rechazado\" ELSE \"Pendiente\" END AS estado, s.observaciones AS motivo_respuesta, s.fecha_revision AS fecha_respuesta, s.fecha_solicitud AS fecha_registro FROM solicitudes s LEFT JOIN estudiante est ON est.id = s.estudiante WHERE s.estado IN (\"aprobado\", \"rechazado\") ORDER BY COALESCE(s.fecha_revision, s.fecha_solicitud, NOW()) DESC LIMIT 50";
$stmt = $db->prepare($sql); $stmt->execute(); $notificaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) { $errorConsulta = "Error de base de datos: " . $e->getMessage(); } catch (Exception $e) { $errorConsulta = "Error: " . $e->getMessage(); } ?>
<section class="w-full space-y-4"><h1 class="text-2xl font-bold mb-6 text-gray-800">Notificaciones</h1>
<?php if ($errorConsulta): ?>
<div class="bg-red-50 border border-red-200 rounded-lg p-4 text-red-700"><p class="font-semibold">Error al cargar notificaciones:</p><p><?= htmlspecialchars($errorConsulta, ENT_QUOTES, "UTF-8") ?></p></div>
<?php elseif (empty($notificaciones)): ?>
