<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Services\AuthService;
use App\Core\Session;

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/AuthService.php';
require_once __DIR__ . '/../../core/Session.php';

/**
 * Controlador de autenticación.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class AuthController extends Controller {

    private AuthService $authService;

    public function __construct(\PDO $pdo) {
        $this->authService = new AuthService($pdo);
    }

    public function register() {
        try {
            $data = $this->getRequestData();

            $result = $this->authService->register($data);

            $this->response(true, 'Usuario registrado correctamente', $result);

        } catch (\Throwable $e) {
            $this->logError($e, "REGISTER");
        }
    }

    public function login() {
        try {
            $data = $this->getRequestData();
            $user = $this->authService->login($data);

            Session::regenerate();
            Session::set('user_id', $user['id']);
            Session::set('role_id', $user['role_id']);
            Session::set('user_name', $user['name']);

            $this->response(true, 'Login exitoso', $user);
        } catch (\Throwable $e) {
            $this->logError($e, "LOGIN");
        }
    }
    
    public function logout() {
        try {
            Session::destroy();
            $this->response(true, 'Sesión cerrada correctamente');
        } catch (\Throwable $e) {
            $this->logError($e, "LOGOUT");
        }
    }
}