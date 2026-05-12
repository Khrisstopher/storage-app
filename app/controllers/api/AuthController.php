<?php 
require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/AuthService.php';

class AuthController extends Controller {

    private AuthService $authService;

    public function __construct($pdo) {
        $this->authService = new AuthService($pdo);
    }

    public function register() {
        try {
            $data = $this->getRequestData();

            $result = $this->authService->register($data);

            $this->response(true, 'Usuario registrado correctamente', $result);

        } catch (Exception $e) {
            $this->logError($e, "REGISTER");
        }
    }

    public function login() {
        try {
            $data = $this->getRequestData();

            $user = $this->authService->login($data);

            // Seguridad: regenerar sesión
            session_regenerate_id(true);

            $_SESSION['user_id'] = $user['id'];
            $_SESSION['role_id'] = $user['role_id'];
            $_SESSION['user_name'] = $user['name'];

            $this->response(true, 'Login exitoso', $user);

        } catch (Exception $e) {
            $this->logError($e, "LOGIN");
        }
    }
    
    public function logout() {
        try {
            $_SESSION = [];

            // Si se desea destruir la cookie de sesión por completo
            if (ini_get("session.use_cookies")) {
                $params = session_get_cookie_params();
                setcookie(session_name(), '', time() - 42000,
                    $params["path"], $params["domain"],
                    $params["secure"], $params["httponly"]
                );
            }

            session_destroy();

            $this->response(true, 'Sesión cerrada correctamente');
        } catch (Exception $e) {
            $this->logError($e, "LOGOUT");
        }
    }
}