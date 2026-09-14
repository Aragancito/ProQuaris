<?php
require_once __DIR__ . '/../config/conexion.php';

class InventarioModel {
    private $db;

    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // $adminId = planta del usuario actual. Si viene vacío, se listan todos
    // (comportamiento anterior; útil para tareas internas).
    public function obtenerTodos($adminId = null) {
        try {
            $sql = "SELECT * FROM inventario";
            $params = [];
            if (!empty($adminId)) {
                $sql .= " WHERE admin_id = ?";
                $params[] = $adminId;
            }
            $stmt = $this->db->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            return [];
        }
    }

    public function insertar($datos, $adminId = null) {
        try {
            $query = "INSERT INTO inventario (idinventario, insumo, stockActual, ubicacion, costoUnitario, unidadMedida, admin_id) 
                      VALUES (:idinventario, :insumo, :stockActual, :ubicacion, :costoUnitario, :unidadMedida, :adminId)";
            $stmt = $this->db->prepare($query);
            return $stmt->execute([
                ':idinventario' => $datos['idinventario'],
                ':insumo' => $datos['insumo'],
                ':stockActual' => $datos['stockActual'],
                ':ubicacion' => $datos['ubicacion'],
                ':costoUnitario' => $datos['costoUnitario'],
                ':unidadMedida' => $datos['unidadMedida'],
                ':adminId' => $adminId
            ]);
        } catch (PDOException $e) {
            return false;
        }
    }
}
?>