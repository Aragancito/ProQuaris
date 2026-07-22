<?php
require_once '../config/conexion.php';

class OrdenModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTodas() {
        $sql = "SELECT * FROM ordenproduccion ORDER BY idOrden DESC";
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function obtenerPorId($id) {
        $sql = "SELECT * FROM ordenproduccion WHERE idOrden = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function crear($datos) {
        $sql = "INSERT INTO ordenproduccion (cantidadPlanificada, fechaInicio, producto, estado) 
                VALUES (?, ?, ?, ?)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['cantidadPlanificada'],
            $datos['fechaInicio'],
            $datos['producto'],
            $datos['estado']
        ]);
    }

    public function actualizar($id, $datos) {
        $sql = "UPDATE ordenproduccion SET 
                cantidadPlanificada = ?,
                fechaInicio = ?,
                producto = ?,
                estado = ?
                WHERE idOrden = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $datos['cantidadPlanificada'],
            $datos['fechaInicio'],
            $datos['producto'],
            $datos['estado'],
            $id
        ]);
    }

    public function eliminar($id) {
        $sql = "DELETE FROM ordenproduccion WHERE idOrden = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([$id]);
    }
}
?>