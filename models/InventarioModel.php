<?php
require_once __DIR__ . '/../config/conexion.php';

class InventarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTodos() {
        try {
            $query = "SELECT * FROM inventario";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function insertar($datos) {
        try {
            $query = "INSERT INTO inventario (idinventario, insumo, stockActual, ubicacion, costoUnitario, unidadMedida) 
                      VALUES (:idinventario, :insumo, :stockActual, :ubicacion, :costoUnitario, :unidadMedida)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':idinventario' => $datos['idinventario'],
                ':insumo' => $datos['insumo'],
                ':stockActual' => $datos['stockActual'],
                ':ubicacion' => $datos['ubicacion'],
                ':costoUnitario' => $datos['costoUnitario'],
                ':unidadMedida' => $datos['unidadMedida']
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>