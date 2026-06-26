<?php
require_once '../models/UsuarioModel.php';
session_start();

class AuthService {
    private $usuarioModel;

    public function __construct($usuarioModel) {
        $this->usuarioModel = $usuarioModel;
    }

    public function login($correo, $contraseña) {
        $usuario = $this->usuarioModel->buscarPorCorreo($correo);
        if ($usuario && password_verify($contraseña, $usuario['contraseña'])) {
            return $usuario;
        }
        return null;
    }

    public function registrar($datos) {
        $datos['contraseña'] = password_hash($datos['contraseña'], PASSWORD_DEFAULT);
        return $this->usuarioModel->registrarUsuario($datos);
    }

    public function logout() {
        $_SESSION = array();
        if (ini_get("session.use_cookies")) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
        }
        session_destroy();
    }
}

class UsuarioController {
    private $authService;

    public function __construct($authService) {
        $this->authService = $authService;
    }

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

    private function login() {
        $correo = $_POST['correo'] ?? '';
        $contraseña = $_POST['contraseña'] ?? '';
        
        $usuario = $this->authService->login($correo, $contraseña);

        if ($usuario) {
            $_SESSION['usuario_nombre'] = $usuario['nombre'] . " " . $usuario['apellido'];
            $_SESSION['usuario_rol'] = $usuario['rol'];
            
            if ($usuario['rol'] === 'Administrador') {
                header("Location: ../views/dashboard.php");
            } else {
                header("Location: ../views/dashboard_empleado.php");
            }
            exit();
        } else {
            header("Location: ../views/login.php?error=1");
            exit();
        }
    }

    private function registrar() {
        $datos = array(
            'nombre' => $_POST['nombre'] ?? '',
            'apellido' => $_POST['apellido'] ?? '',
            'correo' => $_POST['correo'] ?? '',
            'contraseña' => $_POST['contraseña'] ?? '',
            'rol' => $_POST['rol'] ?? 'Empleado'
        );
        
        $resultado = $this->authService->registrar($datos);
        
        if ($resultado) {
            header("Location: ../views/login.php?registro=exitoso");
        } else {
            header("Location: ../views/registro.php?error=1");
        }
        exit();
    }

    private function logout() {
        $this->authService->logout();
        header("Location: ../views/index.php");
        exit();
    }
}

$authService = new AuthService(new UsuarioModel());
$controller = new UsuarioController($authService);
$controller->manejarPeticion();
?>