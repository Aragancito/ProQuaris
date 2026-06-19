<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once "../config/conexion.php";

// ==========================================
// MODELO DE CALIDAD (CAPA DE DATOS)
// ==========================================
// ABSTRACCIÓN: Esta clase abstrae todas las operaciones de base de datos
// relacionadas con inspecciones de calidad. El controlador solo llama
// a registrarInspeccion() sin conocer los detalles SQL.
class CalidadModel {
    
    // ==========================================
    // ENCAPSULAMIENTO
    // ==========================================
    // El atributo $db es privado, protegiendo la conexión a la base de datos.
    private $db;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    // ABSTRACCIÓN: La conexión se obtiene mediante un método estático
    // que oculta la configuración PDO.
    public function __construct() {
        $this->db = Conexion::conectar();
    }

    // ==========================================
    // REGISTRO DE INSPECCIÓN DE CALIDAD
    // ==========================================
    // ABSTRACCIÓN: Este método oculta toda la lógica de inserción SQL.
    // El controlador solo pasa los datos y recibe un booleano.
    public function registrarInspeccion($datos) {
        try {
            // POLIMORFISMO: Los marcadores nombrados (:param) permiten que
            // la consulta se adapte a diferentes estructuras de datos.
            $query = "INSERT INTO Calidad (id_inspeccion, FK_usuario_id, resultado, fecha) 
                      VALUES (:id, :usuario_id, :resultado, :fecha)";
            $stmt = $this->db->prepare($query);
            
            // POLIMORFISMO: bindParam() se adapta a diferentes tipos de datos
            // según la variable que recibe (string, int, date).
            $stmt->bindParam(":id", $datos['id']);
            $stmt->bindParam(":usuario_id", $datos['usuario_id']);
            $stmt->bindParam(":resultado", $datos['resultado']);
            $stmt->bindParam(":fecha", $datos['fecha']);
            
            return $stmt->execute();
        } catch (PDOException $e) {
            // ABSTRACCIÓN: El error se muestra pero los detalles internos
            // de la base de datos están ocultos en el mensaje.
            die("Error en CalidadModel: " . $e->getMessage());
        }
    }
}
?>