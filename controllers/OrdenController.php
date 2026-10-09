<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre']) || !in_array($_SESSION['usuario_rol'], ['Administrador', 'Empleado'])) {
    header("Location: ../views/login.php");
    exit();
}

// BLOQUEO ESTRICTO: Si es empleado, debe tener planta asignada Y estar aprobado
if ($_SESSION['usuario_rol'] === 'Empleado') {
    if (empty($_SESSION['admin_id']) || ($_SESSION['estado'] ?? '') !== 'Activo') {
        header("Location: ../views/usuarios.php"); 
        exit();
    }
}

// admin_id = la planta a la que pertenece este usuario (si es Administrador, es su propio id;
// si es Empleado, es el id del Administrador al que fue aprobado). Se usa para que cada quien
// solo vea y cree órdenes DENTRO de su propia planta.
$adminIdPlanta = $_SESSION['admin_id'] ?? null;

// usuario_id = quién es realmente la persona detrás de la sesión (Administrador o Empleado).
// Se usa para dejar registrado quién CREÓ cada orden, sin importar el rol.
$creadorIdActual = $_SESSION['usuario_id'] ?? null;

require_once '../models/OrdenModel.php';
require_once '../models/ProduccionModel.php';

$model = new OrdenModel();
$prodModel = new ProduccionModel(); 
$accion = $_GET['accion'] ?? 'listar';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

switch ($accion) {
    case 'listar':
        $ordenes = $model->obtenerTodas($adminIdPlanta);
        include '../views/ordenes.php';
        break;

    case 'historico':
        // También acotado a la planta actual: antes mostraba TODAS las órdenes
        // completadas del sistema, sin importar de qué Administrador fueran.
        $historicos = $model->obtenerHistoricoCompleto($adminIdPlanta);
        include '../views/historico_produccion.php';
        break;

    case 'cambiar_estado':
        $id = $_GET['id'] ?? 0;
        $nuevoEstado = $_GET['estado'] ?? 'Activa';
        $ordenActual = $model->obtenerPorId($id);
        if ($ordenActual) {
            $ordenActual['estado'] = $nuevoEstado;
            $model->actualizar($id, $ordenActual);
            $prodModel->actualizarEstadoPorOrden($id, $nuevoEstado);
        }
        header("Location: OrdenController.php?accion=listar");
        exit();
        break;

    case 'crear':
        if ($requestMethod === 'POST') {
            $cantidadPlanificada = intval($_POST['cantidadPlanificada'] ?? 0);
            $fechaInicio = trim($_POST['fechaInicio'] ?? '');
            $idProducto = intval($_POST['idProducto'] ?? 0);
            $estado = $_POST['estado'] ?? 'Activa';

            // --- VALIDACIÓN EN SERVIDOR ---
            // El HTML del formulario ya pone required/min, pero eso es fácil de
            // saltarse (basta con desactivar JS o mandar el POST directo). La
            // validación que de verdad protege los datos es esta, del lado servidor.
            $errores = [];

            if ($cantidadPlanificada <= 0) {
                $errores[] = "La cantidad planificada debe ser un número mayor a 0.";
            }
            if (empty($fechaInicio) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio)) {
                $errores[] = "La fecha de inicio no es válida.";
            }
            if ($idProducto <= 0) {
                $errores[] = "Debe seleccionar un producto del catálogo.";
            } else {
                // No basta con que el producto exista: tiene que ser de ESTA planta.
                // Sin este chequeo, alguien podría forzar por URL/POST el idProducto
                // de OTRA planta y crear una orden con un producto que no es suyo.
                require_once __DIR__ . '/../models/ProductoModel.php';
                $productoModel = new ProductoModel();
                $productoValido = $productoModel->obtenerPorId($idProducto);
                if (!$productoValido || ($productoValido['admin_id'] ?? null) != $adminIdPlanta) {
                    $errores[] = "El producto seleccionado no pertenece a tu planta.";
                }
            }

            if (!empty($errores)) {
                header("Location: OrdenController.php?accion=crear&msg=" . urlencode(implode(' ', $errores)) . "&tipo=error");
                exit();
            }

            $datos = [
                'cantidadPlanificada' => $cantidadPlanificada,
                'fechaInicio' => $fechaInicio,
                'idProducto' => $idProducto,
                'estado' => $estado
            ];
            // Se graba con el admin_id de la planta actual (sin importar si quien la crea
            // es el Administrador o un Empleado ya aprobado en esa planta), y con el
            // creador_id de quién la creó de verdad, para trazabilidad.
            $idOrdenGenerada = $model->crear($datos, $adminIdPlanta, $creadorIdActual);
            if ($idOrdenGenerada) {
                $prodModel->registrarLote($idOrdenGenerada, $cantidadPlanificada, $estado);
            }
            header("Location: OrdenController.php?accion=listar&msg=" . urlencode("Orden creada correctamente"));
            exit();
        }
        include '../views/orden_form.php';
        break;

    case 'editar':
        $id = $_GET['id'] ?? 0;
        if ($requestMethod === 'POST') {
            $cantidadPlanificada = $_POST['cantidadPlanificada'] ?? 0;
            $fechaInicio = $_POST['fechaInicio'] ?? '';
            $idProducto = $_POST['idProducto'] ?? 0;
            $estado = $_POST['estado'] ?? 'Activa';

            $datos = [
                'cantidadPlanificada' => $cantidadPlanificada,
                'fechaInicio' => $fechaInicio,
                'idProducto' => $idProducto,
                'estado' => $estado
            ];
            $model->actualizar($id, $datos);
            $prodModel->actualizarEstadoPorOrden($id, $estado);
            header("Location: OrdenController.php?accion=listar");
            exit();
        }
        $orden = $model->obtenerPorId($id);
        include '../views/orden_form.php';
        break;

    case 'eliminar':
        $id = $_GET['id'] ?? 0;
        $model->eliminar($id);
        header("Location: OrdenController.php?accion=listar&msg=" . urlencode("Orden eliminada correctamente"));
        exit();

    default:
        header("Location: OrdenController.php?accion=listar");
        exit();
}
?>