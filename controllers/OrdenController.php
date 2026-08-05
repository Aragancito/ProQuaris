<?php
session_start();
if (!isset($_SESSION['usuario_nombre']) || $_SESSION['usuario_rol'] !== 'Administrador') {
    header("Location: ../views/login.php");
    exit();
}

require_once '../models/OrdenModel.php';
require_once '../models/ProduccionModel.php'; // 1. Importamos el modelo de producción

$model = new OrdenModel();
$prodModel = new ProduccionModel(); // 2. Instanciamos el modelo de producción
$accion = $_GET['accion'] ?? 'listar';

switch ($accion) {
    case 'listar':
        $ordenes = $model->obtenerTodas();
        include '../views/ordenes.php';
        break;

    case 'crear':
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $datos = [
                'cantidadPlanificada' => $_POST['cantidadPlanificada'],
                'fechaInicio' => $_POST['fechaInicio'],
                'producto' => $_POST['producto'],
                'estado' => $_POST['estado']
            ];
            
            // 3. Creamos la orden y capturamos el ID insertado
            $idOrdenGenerada = $model->crear($datos);

            // 4. Si la orden se creó correctamente y tenemos un ID válido, registramos el lote automático
            if ($idOrdenGenerada) {
                $prodModel->registrarLoteDesdeOrden(
                    $idOrdenGenerada, 
                    $_POST['cantidadPlanificada'], 
                    $_POST['estado']
                );
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
                'producto' => $_POST['producto'],
                'estado' => $_POST['estado']
            ];
            $model->actualizar($id, $datos);
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