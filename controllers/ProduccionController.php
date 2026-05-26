<?php
require_once "../models/ProduccionModel.php";

class ProduccionController {
    private $model;

    public function __construct() {
        $this->model = new ProduccionModel();
    }

    public function gestionarProduccion() {
        if ($_SERVER["REQUEST_METHOD"] == "POST") {
            $datos = [
                'id' => uniqid(),
                'producto' => $_POST['producto'],
                'cantidad' => $_POST['cantidad'],
                'estado' => 'En Proceso'
            ];
            
            if ($this->model->registrarLote($datos)) {
                echo "<script>alert('Lote registrado'); window.location.href='../views/produccion.php';</script>";
            }
        }
    }
}