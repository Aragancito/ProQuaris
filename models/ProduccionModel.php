<?php
require_once "../config/conexion.php";

class ProduccionModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    public function registrarLote($datos) {
        try {
            $query = "INSERT INTO Produccion (id_lote, producto, cantidad, estado) 
                      VALUES (:id, :producto, :cantidad, :estado)";
            $stmt = $this->db->prepare($query);
            $stmt->execute($datos); // Pasamos el array directo si los nombres coinciden con las columnas
            return true;
        } catch (PDOException $e) {
            die("Error en ProduccionModel: " . $e->getMessage());
        }
    }
}