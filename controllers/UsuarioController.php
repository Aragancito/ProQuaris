<?php
// ==========================================
// CARGA DEL MODELO Y SESIÓN
// ==========================================
require_once '../models/UsuarioModel.php';
session_start();

// ==========================================
// CONTROLADOR DE USUARIOS
// ==========================================
// ABSTRACCIÓN: Esta clase oculta toda la complejidad de autenticación,
// registro y gestión de sesiones, exponiendo solo el método público
// manejarPeticion() como punto de entrada.
class UsuarioController {
    
    // ==========================================
    // ENCAPSULAMIENTO
    // ==========================================
    // El atributo $model es privado, protegiendo el acceso directo.
    // Solo se interactúa con él a través de los métodos de la clase.
    private $model;

    // ==========================================
    // CONSTRUCTOR
    // ==========================================
    // Inicializa el modelo de usuario. La dependencia se crea internamente
    // (no se inyecta desde fuera), lo que mantiene el acoplamiento bajo.
    public function __construct() {
        $this->model = new UsuarioModel();
    }

    // ==========================================
    // ABSTRACCIÓN Y POLIMORFISMO
    // ==========================================
    // Este método actúa como un enrutador (Router). Polimórficamente,
    // decide qué acción ejecutar según el método HTTP (POST/GET) y los parámetros.
    public function manejarPeticion() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            if (isset($_POST['accion']) && $_POST['accion'] === 'registrar') {
                $this->registrar();
            } else {
                $this->login();
            }
        } elseif (isset($_GET['logout'])) {
            $this->logout();
        }
    }

    // ==========================================
    // INICIO DE SESIÓN (LÓGICA DE NEGOCIO)
    // ==========================================
    // ENCAPSULAMIENTO: Método privado, solo accesible desde dentro de la clase.
    private function login() {
        // Captura y sanitización básica de datos de entrada
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        
        // El modelo se encarga de la consulta a la base de datos
        $usuario = $this->model->buscarPorCorreo($correo);

        // Verificación segura de credenciales usando password_verify()
        if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
            // Persistencia de datos en sesión
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . " " . $usuario['apellido'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            // POLIMORFISMO: El flujo cambia según el rol del usuario
            if ($usuario['rol'] === 'Administrador') {
                header("Location: ../views/dashboard.php");
            } else {
                header("Location: ../views/dashboard_empleado.php");
            }
            exit();
        } else {
            // Credenciales inválidas: redirige al login con mensaje de error
            header("Location: ../views/login.php?error=1");
            exit();
        }
    }

    // ==========================================
    // REGISTRO DE USUARIO (LÓGICA DE NEGOCIO)
    // ==========================================
    // ENCAPSULAMIENTO: Método privado.
    private function registrar() {
        // ABSTRACCIÓN: El hash de la contraseña se delega a password_hash()
        // Los detalles de encriptación están ocultos en esta línea.
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'correo' => $_POST['correo'],
            'contrasena' => password_hash($_POST['contrasena'], PASSWORD_DEFAULT),
            'rol' => $_POST['rol']
        ];
        
        // Delegación al modelo para persistencia
        $resultado = $this->model->registrarUsuario($datos);
        
        if ($resultado) {
            header("Location: ../views/login.php?registro=exitoso");
        } else {
            header("Location: ../views/registro.php?error=1");
        }
        exit();
    }

    // ==========================================
    // CIERRE DE SESIÓN
    // ==========================================
    // ENCAPSULAMIENTO: Método privado.
    private function logout() {
        // Destrucción completa de la sesión para evitar acceso no autorizado
        session_destroy();
        header("Location: ../views/index.php");
        exit();
    }
}

// ==========================================
// INSTANCIACIÓN Y EJECUCIÓN
// ==========================================
// ABSTRACCIÓN: El usuario externo solo conoce la clase y su método público.
$controller = new UsuarioController();
$controller->manejarPeticion();
?>