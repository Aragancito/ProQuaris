<?php
require_once "../config/conexion.php";

class ProductoModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function obtenerTodos() {
        try {
            $query = "SELECT * FROM productos";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function crear($datos) {
        try {
            $query = "INSERT INTO productos (nombre, descripcion, precioVenta) VALUES (:nombre, :descripcion, :precioVenta)";
            $stmt = $this->db->prepare($query);
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':descripcion' => $datos['descripcion'],
                ':precioVenta' => $datos['precioVenta']
            ]);
            return $this->db->lastInsertId();
        } catch (PDOException $e) {
            return false;
        }
    }

    public function obtenerInsumos() {
        try {
            // Trae la materia prima de tu tabla inventario actual
            $query = "SELECT * FROM inventario";
            return $this->db->query($query)->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function guardarReceta($idProducto, $idinventario, $cantidadRequerida) {
        try {
            $query = "INSERT INTO recetas (idProducto, idinventario, cantidadRequerida) VALUES (:prod, :ins, :cant)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':prod' => $idProducto,
                ':ins' => $idinventario,
                ':cant' => $cantidadRequerida
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>