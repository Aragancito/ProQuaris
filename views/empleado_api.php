<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario_nombre'])) {
    echo json_encode(['error' => 'No autorizado']);
    exit();
}

try {
    $conn = new PDO("mysql:host=localhost;dbname=proquaris_bd", "root", "");
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch(PDOException $e) {
    echo json_encode(['error' => 'Error BD: ' . $e->getMessage()]);
    exit();
}

$action = $_GET['action'] ?? $_POST['action'] ?? '';

if ($action === 'panel') {
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC LIMIT 5");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $stmt2 = $conn->query("SELECT COUNT(*) as total FROM defecto");
    $defectos = $stmt2->fetch(PDO::FETCH_ASSOC);
    
    $lotes_recientes = [];
    foreach ($lotes as $l) {
        $lotes_recientes[] = [
            'id' => $l['idLote'],
            'codigo' => 'LOT-' . $l['idLote'],
            'producto' => 'Producto',
            'cantidad' => $l['cantidad'],
            'estado' => isset($l['estado']) ? $l['estado'] : 'pendiente'
        ];
    }
    
    echo json_encode([
        'lotes_asignados' => count($lotes),
        'defectos_registrados' => $defectos['total'],
        'tareas_pendientes' => 3,
        'lotes_recientes' => $lotes_recientes
    ]);
    
} elseif ($action === 'lotes' || $action === 'mislotes') {
    $stmt = $conn->query("SELECT * FROM lote ORDER BY idLote DESC");
    $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    $resultado = [];
    foreach ($lotes as $l) {
        $resultado[] = [
            'id' => $l['idLote'],
            'codigo' => 'LOT-' . $l['idLote'],
            'producto' => 'Producto',
            'cantidad' => $l['cantidad'],
            'fecha' => isset($l['fechaCreacion']) ? $l['fechaCreacion'] : date('Y-m-d'),
            'estado' => isset($l['estado']) ? $l['estado'] : 'pendiente'
        ];
    }
    echo json_encode($resultado);
    
} elseif ($action === 'inspecciones') {
    $stmt = $conn->query("SELECT i.*, l.idLote as lote_codigo FROM registroinspeccion i LEFT JOIN lote l ON i.FK_loteld = l.idLote ORDER BY i.idRI DESC");
    $inspecciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($inspecciones);
    
} elseif ($action === 'detalle_lote') {
    $id = isset($_GET['id']) ? $_GET['id'] : 0;
    $stmt = $conn->prepare("SELECT * FROM lote WHERE idLote = ?");
    $stmt->execute([$id]);
    $lote = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($lote) {
        echo json_encode([
            'id' => $lote['idLote'],
            'codigo' => 'LOT-' . $lote['idLote'],
            'producto' => 'Producto',
            'cantidad' => $lote['cantidad'],
            'fecha' => $lote['fechaCreacion'],
            'estado' => $lote['estado']
        ]);
    } else {
        echo json_encode(['error' => 'Lote no encontrado']);
    }
    
} elseif ($action === 'registrar_defecto') {
    $lote_id = isset($_POST['lote_id']) ? $_POST['lote_id'] : 0;
    $tipo = isset($_POST['tipo']) ? $_POST['tipo'] : '';
    $severidad = isset($_POST['severidad']) ? $_POST['severidad'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    
    $stmt = $conn->prepare("INSERT INTO defecto (FK_loteld, tipo, severidad, descripcion) VALUES (?, ?, ?, ?)");
    $resultado = $stmt->execute([$lote_id, $tipo, $severidad, $descripcion]);
    
    echo json_encode(['success' => $resultado]);
    
} else {
    echo json_encode(['error' => 'Acción no válida: ' . $action]);
}
?>