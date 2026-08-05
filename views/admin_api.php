<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Opcional pero recomendado: Asegurar que hay una sesión activa para usar la API
if (!isset($_SESSION['usuario_nombre'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'error' => 'No autorizado. Por favor inicie sesión.']);
    exit();
}

include '../config/conexion.php'; 

// Establecemos la cabecera JSON para toda la respuesta de la API de una vez
header('Content-Type: application/json');

try {
    $db = Conexion::conectar();
} catch (Exception $e) {
    echo json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

// 1. OBTENER LOTES (GET)
if ($action === 'obtener_lotes') {
    try {
        $stmt = $db->query("SELECT idLote, FK_ordenId, cantidad, fechaCreacion, estado FROM lote");
        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lotes);
    } catch (Exception $e) {
        echo json_encode(['success' => false, 'error' => $e->getMessage()]);
    }
    exit();
}

// 2. CREAR LOTE (POST)
if ($action === 'crear_lote') {
    $ordenId = $_POST['FK_ordenId'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;
    $estado = $_POST['estado'] ?? 'Aprobado';

    if ($ordenId && $cantidad) {
        try {
            $sql = "INSERT INTO lote (FK_ordenId, cantidad, fechaCreacion, estado) VALUES (?, ?, CURDATE(), ?)";
            $stmt = $db->prepare($sql);
            $resultado = $stmt->execute([$ordenId, $cantidad, $estado]);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Faltan datos obligatorios']);
    }
    exit();
}

// 3. EDITAR LOTE (POST)
if ($action === 'editar_lote') {
    $idLote = $_POST['idLote'] ?? null;
    $ordenId = $_POST['FK_ordenId'] ?? null;
    $cantidad = $_POST['cantidad'] ?? null;
    $estado = $_POST['estado'] ?? 'Aprobado';

    if ($idLote && $ordenId && $cantidad) {
        try {
            $sql = "UPDATE lote SET FK_ordenId = ?, cantidad = ?, estado = ? WHERE idLote = ?";
            $stmt = $db->prepare($sql);
            $resultado = $stmt->execute([$ordenId, $cantidad, $estado, $idLote]);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'Faltan datos para actualizar']);
    }
    exit();
}

// 4. ELIMINAR LOTE (POST)
if ($action === 'eliminar_lote') {
    $idLote = $_POST['idLote'] ?? null;

    if ($idLote) {
        try {
            $sql = "DELETE FROM lote WHERE idLote = ?";
            $stmt = $db->prepare($sql);
            $resultado = $stmt->execute([$idLote]);

            echo json_encode(['success' => $resultado]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'error' => $e->getMessage()]);
        }
    } else {
        echo json_encode(['success' => false, 'error' => 'ID de lote no proporcionado']);
    }
    exit();
}

// Si ninguna acción coincide
echo json_encode(['success' => false, 'error' => 'Acción no válida']);
exit();
?>