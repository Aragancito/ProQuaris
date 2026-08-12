<?php
if (session_status() === PHP_SESSION_NONE) { 
    session_start(); 
}
if (!isset($_SESSION['usuario_nombre'])) {
    header("Location: /ProQuaris/views/login.php");
    exit();
}

require_once __DIR__ . '/../models/InventarioModel.php';

class InventarioController {
    private $model;

    public function __construct() {
        $this->model = new InventarioModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';
        switch ($accion) {
            case 'listar': 
                $this->listar(); 
                break;
            case 'crear': 
                $this->crear(); 
                break;
            default: 
                $this->listar(); 
                break;
        }
    }

    public function listar() {
        $insumos = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/inventario.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'idinventario' => $_POST['idinventario'] ?? null,
                'insumo' => $_POST['insumo'] ?? '',
                'stockActual' => $_POST['stockActual'] ?? 0,
                'ubicacion' => $_POST['ubicacion'] ?? 'Almacén Principal',
                'costoUnitario' => $_POST['costoUnitario'] ?? 0,
                'unidadMedida' => $_POST['unidadMedida'] ?? ''
            ];
            $this->model->insertar($datos);
            header("Location: /ProQuaris/controllers/InventarioController.php?accion=listar");
            exit();
        }
    }
}

$controller = new InventarioController();
$controller->procesarAccion();
?>