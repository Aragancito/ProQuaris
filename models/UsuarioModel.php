<?php
// ==========================================
// CARGA DE DEPENDENCIAS
// ==========================================
require_once '../config/conexion.php';

// ==========================================
// MODELO DE USUARIO (CAPA DE DATOS)
// ==========================================
// ABSTRACCIÓN: Esta clase abstrae todas las operaciones de base de datos
// relacionadas con usuarios. El controlador solo llama métodos como
// registrarUsuario() o buscarPorCorreo() sin conocer los detalles SQL.
class UsuarioModel {
    
    // ==========================================
    // ENCAPSULAMIENTO
    // ==========================================
    // El atributo $db es privado, protegiendo la conexión a la base de datos.
    // Solo se accede a ella desde métodos internos de la clase.
    private $db;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    public function __construct() {
        // ABSTRACCIÓN: La conexión se obtiene mediante un método estático
        // que oculta la configuración PDO.
        $this->db = Conexion::conectar();
        
        // HERENCIA/POLIMORFISMO: El sistema crea automáticamente la tabla
        // si no existe, demostrando polimorfismo en la inicialización.
        $this->crearTabla();
    }

    // ==========================================
    // CREACIÓN AUTOMÁTICA DE LA TABLA
    // ==========================================
    // ENCAPSULAMIENTO: Método privado, solo accesible desde dentro de la clase.
    // ABSTRACCIÓN: Oculta la lógica de creación de tablas SQL,
    // permitiendo que el sistema funcione sin scripts manuales.
    private function crearTabla() {
        $sql = "CREATE TABLE IF NOT EXISTS usuario (
            id VARCHAR(36) PRIMARY KEY,
            nombre VARCHAR(30) NOT NULL,
            apellido VARCHAR(30) NOT NULL,
            correo VARCHAR(50) UNIQUE NOT NULL,
            contraseña VARCHAR(255) NOT NULL,
            rol VARCHAR(20) NOT NULL,
            estado VARCHAR(15) DEFAULT 'Activo'
        )";
        $this->db->exec($sql);
    }

    // ==========================================
    // REGISTRO DE NUEVO USUARIO
    // ==========================================
    // ABSTRACCIÓN: Este método oculta la generación de ID,
    // la consulta SQL y la inserción. El controlador solo pasa los datos.
    public function registrarUsuario($datos) {
        // ABSTRACCIÓN: Generación de ID única con CSPRNG
        // El usuario externo no necesita saber cómo se genera.
        $id = bin2hex(random_bytes(16));
        
        // POLIMORFISMO: La consulta preparada maneja diferentes tipos de datos
        // según los valores que reciba, adaptándose sin cambiar el código.
        $sql = "INSERT INTO usuario (id, nombre, apellido, correo, contraseña, rol, estado) 
                VALUES (?, ?, ?, ?, ?, ?, 'Activo')";
        
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            $id,
            $datos['nombre'],
            $datos['apellido'],
            $datos['correo'],
            $datos['contrasena'], // Debe venir ya hasheada desde el controlador
            $datos['rol']
        ]);
    }

    // ==========================================
    // BÚSQUEDA DE USUARIO POR CORREO
    // ==========================================
    // ABSTRACCIÓN: Oculta la lógica de consulta SQL.
    // POLIMORFISMO: Retorna un array o false según el resultado,
    // permitiendo un manejo flexible en el controlador.
    public function buscarPorCorreo($correo) {
        $sql = "SELECT * FROM usuario WHERE correo = ?";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$correo]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}
?>