<?php
session_start();
if (!isset($_SESSION['usuario_nombre']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../views/login.php");
    exit();
}

require_once '../models/OrdenModel.php';
require_once '../models/ProduccionModel.php';

$model = new OrdenModel();
$prodModel = new ProduccionModel(); 
$accion = $_GET['accion'] ?? 'listar';

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
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'cantidadPlanificada' => $_POST['cantidadPlanificada'],
                'fechaInicio' => $_POST['fechaInicio'],
                'idProducto' => $_POST['idProducto'],
                'estado' => $_POST['estado']
            ];
            $idOrdenGenerada = $model->crear($datos);
            if ($idOrdenGenerada) {
                $prodModel->registrarLote($idOrdenGenerada, $_POST['cantidadPlanificada'], $_POST['estado']);
            }
            header("Location: OrdenController.php?accion=listar");
            exit();
        }
        include '../views/orden_form.php';
        break;

    case 'editar':
        $id = $_GET['id'] ?? 0;
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'cantidadPlanificada' => $_POST['cantidadPlanificada'],
                'fechaInicio' => $_POST['fechaInicio'],
                'idProducto' => $_POST['idProducto'],
                'estado' => $_POST['estado']
            ];
            $model->actualizar($id, $datos);
            $prodModel->actualizarEstadoPorOrden($id, $_POST['estado']);
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