<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['usuario_nombre']) || !in_array($_SESSION['usuario_rol'], ['Administrador', 'Operario'])) {
    header("Location: ../views/login.php");
    exit();
}

// BLOQUEO ESTRICTO: Si es operario, debe tener planta Y estar aprobado
if ($_SESSION['usuario_rol'] === 'Operario') {
    if (empty($_SESSION['admin_id']) || ($_SESSION['estado'] ?? '') !== 'Activo') {
        header("Location: ../views/usuarios.php"); 
        exit();
    }
}

require_once '../models/OrdenModel.php';
require_once '../models/ProduccionModel.php';

$model = new OrdenModel();
$prodModel = new ProduccionModel(); 
$accion = $_GET['accion'] ?? 'listar';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? '';

switch ($accion) {
    case 'listar':
        $ordenes = $model->obtenerTodas();
        include '../views/ordenes.php';
        break;

    case 'historico':
        $historicos = $model->obtenerHistoricoCompleto();
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
            $idOrdenGenerada = $model->crear($datos);
            if ($idOrdenGenerada) {
                $prodModel->registrarLote($idOrdenGenerada, $cantidadPlanificada, $estado);
            }
            header("Location: OrdenController.php?accion=listar");
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
        header("Location: OrdenController.php?accion=listar");
        exit();

    default:
        header("Location: OrdenController.php?accion=listar");
        exit();
}
?>