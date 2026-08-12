<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: ../views/login.php");
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
            case 'eliminar':
                $this->eliminar();
                break;
            default:
                $this->listar();
                break;
        }
    }

    public function listar() {
        $lotes = $this->model->obtenerLotes();
        require_once __DIR__ . '/../views/lotes.php';
    }

    public function eliminar() {
        $id = $_GET['id'] ?? null;
        if ($id) {
            $this->model->eliminarLote($id);
        }
        header("Location: ProduccionController.php?accion=listar");
        exit();
    }
}

$controller = new ProduccionController();
$controller->procesarAccion();
?>