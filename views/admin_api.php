<?php
include 'conexion.php'; // Tu conexión a base de datos

$action = $_GET['action'] ?? '';

if ($action === 'obtener_lotes') {
    $sql = "SELECT idLote, FK_ordenId, cantidad, fechaCreacion, estado FROM lote";
    $resultado = $conexion->query($sql);

    $lotes = [];
    while ($row = $resultado->fetch_assoc()) {
        $lotes[] = $row;
    }

    header('Content-Type: application/json');
    echo json_encode($lotes);
    exit();
}
?>