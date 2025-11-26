<?php
session_start();
require_once 'db.php'; // <- conexión a tu BD

if (!isset($_SESSION['id_estudiante'])) {
    die("Acceso denegado.");
}

$estudiante_id = (int)$_POST['estudiante_id'];
$nombre = trim($_POST['nombre']);
$telefono = trim($_POST['telefono']);
$tipo = trim($_POST['tipo_solicitud']);
$fecha = $_POST['fecha_solicitud'] . ' ' . date('H:i:s');
$descripcion = trim($_POST['descripcion']);
$foto = null;

if (!empty($_FILES['foto']['name'])) {
    $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
    $filename = uniqid() . ".$ext";
    $path = "uploads/$filename";
    if (move_uploaded_file($_FILES['foto']['tmp_name'], $path)) {
        $foto = $path;
    }
}

$stmt = $conn->prepare("INSERT INTO solicitudes 
    (estudiante, tipo_solicitud, descripcion, fecha_solicitud, foto, estado) 
    VALUES (?, ?, ?, ?, ?, 'pendiente')");
$stmt->bind_param("issss", $estudiante_id, $tipo, $descripcion, $fecha, $foto);

if ($stmt->execute()) {
    echo "<script>alert('Solicitud enviada con éxito.'); location.href='dashboard-usuario.php?pagina=usuario-solicitud';</script>";
} else {
    echo "<script>alert('Error al guardar la solicitud.'); history.back();</script>";
}