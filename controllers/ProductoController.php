<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

if (!isset($_SESSION['usuario_nombre']) || !in_array($_SESSION['usuario_rol'], ['Administrador', 'Empleado'])) {
    header("Location: ../views/login.php");
    exit();
}

// Validación de planta y aprobación
if ($_SESSION['usuario_rol'] === 'Empleado') {
    if (empty($_SESSION['admin_id']) || ($_SESSION['estado'] ?? '') !== 'Activo') {
        header("Location: ../views/usuarios.php"); 
        exit();
    }
}

// Planta del usuario actual (para Administrador es su propio id).
$adminIdPlanta = $_SESSION['admin_id'] ?? null;

require_once __DIR__ . '/../models/ProductoModel.php';

class ProductoController {
    private $model;
    private $adminIdPlanta;

    public function __construct($adminIdPlanta) {
        $this->model = new ProductoModel();
        $this->adminIdPlanta = $adminIdPlanta;
    }

    public function procesarAccion() {
        $accion = $_GET['accion'] ?? 'listar';
        switch ($accion) {
            case 'listar': 
                $this->listar(); 
                break;
            case 'crear': 
            case 'editar': 
            case 'eliminar': 
                // Restricción: Solo el Administrador puede gestionar productos e insumos.
                // El Empleado puede VER el listado (para consultar precios/recetas), pero no tocarlo.
                if (($_SESSION['usuario_rol'] ?? '') !== 'Administrador') {
                    header("Location: ProductoController.php?accion=listar");
                    exit();
                }
                if ($accion === 'crear') $this->crear();
                elseif ($accion === 'editar') $this->editar();
                elseif ($accion === 'eliminar') $this->eliminar();
                break;
            default: 
                $this->listar(); 
                break;
        }
    }

    public function listar() {
        // Solo el catálogo de la planta del usuario actual.
        $productos = $this->model->obtenerTodos($this->adminIdPlanta);
        require_once __DIR__ . '/../views/productos.php';
    }

    public function crear() {
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?: ($_SERVER['REQUEST_METHOD'] ?? '');

        if ($requestMethod === 'POST') {
            $datosProducto = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'plusvalia' => $_POST['plusvalia'] ?? 0,
                'precioVenta' => $_POST['precioVenta'] ?? 0
            ];
            
            $insumosDirectos = [];
            if (!empty($_POST['insumos'])) {
                foreach ($_POST['insumos'] as $ins) {
                    $insumosDirectos[] = [
                        'nombre' => $ins['nombre'] ?? '',
                        'cantidad' => $ins['cantidad'] ?? 0,
                        'costo' => $ins['costo'] ?? 0,
                        'unidad' => $ins['unidad'] ?? 'Unidades',
                        'cantidad_por_empaque' => $ins['cantidad_por_empaque'] ?? 1.00,
                        'unidad_contenido' => $ins['unidad_contenido'] ?? 'unidades'
                    ];
                }
            }

            // Se graba con la planta del Administrador que lo está creando.
            $this->model->crearConInsumosDirectos($datosProducto, $insumosDirectos, $this->adminIdPlanta);
            header("Location: ProductoController.php?accion=listar&msg=" . urlencode("Producto creado correctamente"));
            exit();
        }

        $action = "/ProQuaris/controllers/ProductoController.php?accion=crear";
        require_once __DIR__ . '/../views/producto_form.php';
    }

    public function editar() {
        $id = $_GET['id'] ?? 0;
        $requestMethod = filter_input(INPUT_SERVER, 'REQUEST_METHOD') ?: ($_SERVER['REQUEST_METHOD'] ?? '');

        if ($requestMethod === 'POST') {
            $datosProducto = [
                'nombre' => $_POST['nombre'] ?? '',
                'descripcion' => $_POST['descripcion'] ?? '',
                'plusvalia' => $_POST['plusvalia'] ?? 0,
                'precioVenta' => $_POST['precioVenta'] ?? 0
            ];
            
            $insumosDirectos = [];
            if (!empty($_POST['insumos'])) {
                foreach ($_POST['insumos'] as $ins) {
                    $insumosDirectos[] = [
                        'nombre' => $ins['nombre'] ?? '',
                        'cantidad' => $ins['cantidad'] ?? 0,
                        'costo' => $ins['costo'] ?? 0,
                        'unidad' => $ins['unidad'] ?? 'Unidades',
                        'cantidad_por_empaque' => $ins['cantidad_por_empaque'] ?? 1.00,
                        'unidad_contenido' => $ins['unidad_contenido'] ?? 'unidades'
                    ];
                }
            }

            $this->model->actualizar($id, $datosProducto, $insumosDirectos);
            header("Location: ProductoController.php?accion=listar&msg=" . urlencode("Producto actualizado correctamente"));
            exit();
        }

        $producto = $this->model->obtenerPorId($id);
        $insumos = $this->model->obtenerInsumosPorProducto($id);
        $action = "/ProQuaris/controllers/ProductoController.php?accion=editar&id=" . $id;
        
        require_once __DIR__ . '/../views/producto_form.php';
    }

    public function eliminar() {
        $id = $_GET['id'] ?? 0;
        $this->model->eliminar($id);
        header("Location: ProductoController.php?accion=listar&msg=" . urlencode("Producto eliminado correctamente"));
        exit();
    }
}

$controller = new ProductoController($adminIdPlanta);
$controller->procesarAccion();
?>