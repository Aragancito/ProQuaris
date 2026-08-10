<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_nombre']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../views/login.php");
    exit();
}

require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    private $model;

    public function __construct() {
        $this->model = new ProductoModel();
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';
        switch ($accion) {
            case 'listar': $this->listar(); break;
            case 'crear': $this->crear(); break;
            default: $this->listar(); break;
        }
    }

    public function listar() {
        $productos = $this->model->obtenerTodos();
        require_once __DIR__ . '/../views/productos.php';
    }

    public function crear() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'nombre' => $_POST['nombre'],
                'descripcion' => $_POST['descripcion'],
                'precioVenta' => $_POST['precioVenta']
            ];
            $idProducto = $this->model->crear($datos);

            // Guardamos los insumos asociados (la receta)
            if ($idProducto && isset($_POST['insumos']) && is_array($_POST['insumos'])) {
                foreach ($_POST['insumos'] as $idinventario => $cantidad) {
                    if ($cantidad > 0) {
                        $this->model->guardarReceta($idProducto, $idinventario, $cantidad);
                    }
                }
            }
            header("Location: /ProQuaris/controllers/ProductoController.php?accion=listar");
            exit();
        }
        $insumos = $this->model->obtenerInsumos();
        require_once __DIR__ . '/../views/producto_form.php';
    }
}

$controller = new ProductoController();
$controller->procesarAccion();
?>