<?php

namespace App\Controllers\Api;

use App\Core\Controller;
use App\Services\UserService;

require_once __DIR__ . '/../../core/Controller.php';
require_once __DIR__ . '/../../services/UserService.php';

/**
 * Controlador de usuario para manejar configuraciones relacionadas con los usuarios.
 * @author Khrisstopher
 * @link https://www.linkedin.com/in/khrisstopher/
 */
class UserController extends Controller {

    private UserService $userService;

    public function __construct(\PDO $pdo) {
        $this->userService = new UserService($pdo);
    }

    public function list() {
        try {
            $this->requireAdmin();
            $users = $this->userService->getUsers();
            $this->response(true, 'Usuarios obtenidos correctamente', $users);
        } catch (\Exception $e) {
            $this->logError($e, "GET_USERS");
        }
    }

    public function update() {
        try {
            $this->requireAdmin();
            $this->checkCSRFStrict();
            $data = $this->getRequestData();
            $result = $this->userService->updateUser($data);
            $this->response(true, 'Usuario actualizado correctamente', $result);
        } catch (\Exception $e) {
            $this->logError($e, "UPDATE_USER");
        }
    }

    public function delete() {
        try {
            $this->requireAdmin();
            $this->checkCSRFStrict();
            $data = $this->getRequestData();
            $result = $this->userService->deleteUser($data['id']);
            $this->response(true, 'Usuario eliminado correctamente', $result);
        } catch (\Exception $e) {
            $this->logError($e, "DELETE_USER");
        }
    }
}