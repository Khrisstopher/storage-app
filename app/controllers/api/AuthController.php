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
            $this->response(false, $e->getMessage());
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
            $this->response(false, $e->getMessage());
        }
    }
}