<?php
// Simula una respuesta de conexión exitosa sin usar base de datos
header('Content-Type: application/json');
echo json_encode(['success' => true]);
?>