<?php
// ==========================================
// CONTROLADOR DE PRODUCCIÓN Y LOTES
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

require_once __DIR__ . '/../models/ProduccionModel.php';

class ProduccionController {
    private $model;

    public function __construct() {
        $this->model = new ProduccionModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';

        switch ($accion) {
            case 'listar':
                $this->listar();
                break;
            default:
                $this->listar();
                break;
        }
    }

    public function listar() {
        // Obtenemos los lotes reales desde la base de datos a través del modelo
        $lotes = $this->model->obtenerLotes();
        
        // Cargamos la vista de lotes usando ruta absoluta basada en __DIR__
        require_once __DIR__ . '/../views/lotes.php';
    }
}

$controller = new ProduccionController();
$controller->procesarAccion();
?>